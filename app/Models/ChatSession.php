<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['session_token', 'guest_name', 'avatar_color', 'ollama_config_id', 'last_active_at'])]
class ChatSession extends Model
{
    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    public function ollamaConfig()
    {
        return $this->belongsTo(OllamaConfig::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public static function fromRequest(): self
    {
        $token  = request()->cookie('chat_token') ?? Str::random(40);
        $config = OllamaConfig::active();

        $session = static::firstOrCreate(
            ['session_token' => $token],
            [
                'guest_name'       => 'Guest',
                'avatar_color'     => static::tokenToColor($token),
                'ollama_config_id' => $config->id,
                'last_active_at'   => now(),
            ]
        );

        $session->update(['last_active_at' => now()]);

        // Simpan token di cookie (1 tahun)
        cookie()->queue('chat_token', $token, 60 * 24 * 365);

        return $session;
    }

    /** Singkatan dari guest_name untuk avatar splash, maks 2 karakter */
    public function avatarInitials(): string
    {
        $words = explode(' ', trim($this->guest_name));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($words[0], 0, 2));
    }

    // Generate warna hex yang konsisten dari token 
    private static function tokenToColor(string $token): string
    {
        $colors = [
            '#6366f1',
            '#8b5cf6',
            '#ec4899',
            '#f59e0b',
            '#10b981',
            '#3b82f6',
            '#ef4444',
            '#14b8a6',
        ];

        return $colors[abs(crc32($token)) % count($colors)];
    }
}
