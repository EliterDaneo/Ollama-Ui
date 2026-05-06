<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\OllamaConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatUiController extends Controller
{
    public function index()
    {
        $session  = ChatSession::fromRequest();
        $config   = $session->ollamaConfig;
        $messages = $session->messages()->where('role', '!=', 'system')->get();

        return view('chat', compact('session', 'config', 'messages'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['message' => 'required|string|max:4000']);

        $session = ChatSession::fromRequest();
        $config  = $session->ollamaConfig;

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'role'            => 'user',
            'content'         => $request->message,
        ]);

        $history = $session->messages()
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $startMs  = now()->valueOf();
        $ollamaRes = $this->callOllama($config, $history);

        if (! $ollamaRes['ok']) {
            return response()->json(['error' => $ollamaRes['error']], 502);
        }

        $responseMs = now()->valueOf() - $startMs;
        $botReply   = $ollamaRes['content'];

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'role'            => 'assistant',
            'content'         => $botReply,
            'response_ms'     => $responseMs,
        ]);

        return response()->json([
            'reply'       => $botReply,
            'response_ms' => $responseMs,
        ]);
    }

    public function destroy(): JsonResponse
    {
        ChatSession::where('session_token', request()->cookie('chat_token'))
            ->first()
            ?->delete();

        cookie()->queue(cookie()->forget('chat_token'));

        return response()->json(['ok' => true]);
    }

    private function callOllama(OllamaConfig $config, array $history): array
    {
        try {
            $ch = curl_init("{$config->base_url}/api/chat");
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => json_encode([
                    'model'    => $config->model,
                    'messages' => $history,
                    'options'  => [
                        'temperature'    => $config->temperature,
                        'top_p'          => $config->top_p,
                        'repeat_penalty' => $config->repeat_penalty,
                    ],
                    'stream' => false,
                ]),
                CURLOPT_TIMEOUT => 120,
            ]);

            $body  = curl_exec($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            if ($errno) {
                return ['ok' => false, 'error' => 'Koneksi ke Ollama gagal.'];
            }

            $data = json_decode($body, true);

            return ['ok' => true, 'content' => $data['message']['content'] ?? ''];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
