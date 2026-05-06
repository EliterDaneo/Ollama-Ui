@extends('layouts.app', ['title' => 'Chat with Ollama AI'])

@section('content')
    <div class="d-flex flex-column vh-100 mx-auto bg-dark border-start border-end border-secondary" style="max-width: 800px;">

        {{-- HEADER --}}
        <header class="d-flex align-items-center justify-content-between px-4 py-3 bg-dark border-bottom border-secondary">
            <div class="d-flex align-items-center gap-3">
                {{-- Bot avatar --}}
                <div class="position-relative d-flex align-items-center justify-content-center flex-shrink-0">
                    <img src="{{ asset('img/logo.png') }}" alt="logo.png" class="img-fluid" style="width:50px; height:50px;">
                    <span class="online-dot position-absolute bottom-0 end-0 bg-success border border-dark rounded-circle"
                        style="width:12px; height:12px;"></span>
                </div>
                <div>
                    <h6 class="mb-0 text-white fw-bold">
                        Ollama AI
                        <small class="text-secondary fw-normal fs-6 ms-1">{{ $config->model }}</small>
                    </h6>
                    <small class="text-success fw-medium">Online</small>
                </div>
            </div>

            {{-- Tombol hapus riwayat --}}
            <button id="btn-clear" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-trash3 me-1"></i>Hapus riwayat
            </button>
        </header>

        {{-- MESSAGES AREA --}}
        <main id="messages" class="flex-grow-1 overflow-y-auto p-4 d-flex flex-column gap-3">

            {{-- Pesan sambutan bot (selalu tampil) --}}
            <div class="d-flex align-items-end gap-2">
                {{-- Bot avatar --}}
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width:28px; height:28px; background: linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <i class="bi bi-robot text-white" style="font-size:13px;"></i>
                </div>
                <div class="bg-secondary text-light border border-secondary px-3 py-2"
                    style="border-radius: 1rem 1rem 1rem 0.25rem; max-width:85%;">
                    Halo! 👋 Saya Ollama AI, ada yang bisa saya bantu hari ini?
                </div>
            </div>

            {{-- Riwayat pesan dari database --}}
            @foreach ($messages as $msg)
                @if ($msg->role === 'user')
                    {{-- Bubble user --}}
                    <div class="d-flex align-items-end justify-content-end gap-2">
                        <div class="text-white px-3 py-2 fs-6"
                            style="background: linear-gradient(135deg,#6366f1,#8b5cf6); max-width:85%;
                                    border-radius: 1rem 1rem 0.25rem 1rem;
                                    box-shadow: 0 4px 12px rgba(99,102,241,.2);">
                            {{ $msg->content }}
                        </div>
                        {{-- Avatar splash singkatan --}}
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-white"
                            style="width:28px; height:28px; font-size:11px;
                                    background-color: {{ $session->avatar_color }};">
                            {{ $session->avatarInitials() }}
                        </div>
                    </div>
                @elseif ($msg->role === 'assistant')
                    {{-- Bubble bot --}}
                    <div class="d-flex align-items-end gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:28px; height:28px; background: linear-gradient(135deg,#6366f1,#8b5cf6);">
                            <i class="bi bi-robot text-white" style="font-size:13px;"></i>
                        </div>
                        <div class="bg-dark-subtle text-light border border-secondary px-3 py-2 fs-6"
                            style="border-radius: 1rem 1rem 1rem 0.25rem; max-width:85%;">
                            {!! nl2br(e($msg->content)) !!}
                        </div>
                    </div>
                @endif
            @endforeach

        </main>

        {{-- INPUT FOOTER --}}
        <footer class="px-3 py-3 bg-dark border-top border-secondary">
            <form id="chat-form">
                @csrf
                <div class="d-flex align-items-center gap-2">

                    <button type="button"
                        class="btn btn-outline-secondary rounded-circle border-secondary d-flex align-items-center justify-content-center p-0 flex-shrink-0"
                        style="width:40px; height:40px;" title="Lampiran">
                        <i class="bi bi-paperclip fs-5"></i>
                    </button>

                    <div class="flex-grow-1 border border-secondary rounded px-3 py-2">
                        <input type="text" id="msg-input" class="form-control" placeholder="Ketik pesan..."
                            autocomplete="off" width="100%">
                    </div>

                    <button type="submit"
                        class="btn-send btn btn-primary border-0 rounded-circle d-flex align-items-center justify-content-center text-white p-0 flex-shrink-0"
                        style="width:40px; height:40px;" title="Kirim">
                        <i class="bi bi-send fs-5"></i>
                    </button>

                </div>
            </form>
        </footer>

    </div>
@endsection

@push('scripts')
    <script>
        const CSRF = '{{ csrf_token() }}';
        const AV_COLOR = '{{ $session->avatar_color }}';
        const AV_INIT = '{{ $session->avatarInitials() }}';

        const messagesEl = document.getElementById('messages');
        const input = document.getElementById('msg-input');

        //Avatar: 'bot' → ikon robot || 'user' → splash singkatan
        function avatar(type) {
            return type === 'bot' ?
                `<div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width:28px;height:28px;background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                   <i class="bi bi-robot text-white" style="font-size:13px;"></i>
               </div>` :
                `<div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-white"
                    style="width:28px;height:28px;font-size:11px;background-color:${AV_COLOR};">
                   ${AV_INIT}
               </div>`;
        }

        //Tambah bubble ke area pesan
        function bubble(type, content) {
            const isBot = type === 'bot';
            const wrap = document.createElement('div');

            wrap.className = isBot ?
                'd-flex align-items-end gap-2' :
                'd-flex align-items-end justify-content-end gap-2';

            wrap.innerHTML = isBot ?
                `${avatar('bot')}
               <div class="bg-secondary text-light border border-secondary px-3 py-2 fs-6"
                    style="border-radius:1rem 1rem 1rem .25rem;max-width:85%;">${content}</div>` :
                `<div class="text-white px-3 py-2 fs-6"
                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);max-width:85%;
                           border-radius:1rem 1rem .25rem 1rem;
                           box-shadow:0 4px 12px rgba(255, 255, 255, 0.2);"></div>
               ${avatar('user')}`;

            // Isi teks: pakai textContent untuk user (XSS-safe), innerHTML untuk bot
            if (!isBot) wrap.querySelector('div').textContent = content;

            messagesEl.appendChild(wrap);
            messagesEl.scrollTop = messagesEl.scrollHeight;
            return wrap;
        }

        //Typing indicator: bubble bot dengan 3 titik animasi
        function typing() {
            return bubble('bot',
                `<span class="typing-dot bg-dark"></span>
             <span class="typing-dot bg-dark"></span>
             <span class="typing-dot bg-dark"></span>`);
        }

        //Submit pesan
        document.getElementById('chat-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;

            bubble('user', text);
            input.value = '';

            const loader = typing();

            try {
                const res = await fetch('/chat/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        message: text
                    }),
                });
                const data = await res.json();
                loader.remove();
                bubble('bot', res.ok ? data.reply.replace(/\n/g, '<br>') : '⚠️ ' + data.error);
            } catch (err) {
                loader.remove();
                bubble('bot', '⚠️ ' + err.message);
            }
        });

        //Hapus riwayat
        document.getElementById('btn-clear').addEventListener('click', async () => {
            if (!confirm('Hapus semua riwayat chat?')) return;
            await fetch('/chat/session', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                }
            });
            location.reload();
        });
    </script>
@endpush
