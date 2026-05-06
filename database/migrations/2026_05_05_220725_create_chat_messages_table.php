<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')
                ->constrained('chat_sessions')
                ->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);     // pengirim pesan
            $table->text('content');                                   // isi pesan
            $table->unsignedInteger('token_count')->nullable();        // estimasi token
            $table->unsignedInteger('response_ms')->nullable();        // lama respons (ms), khusus assistant
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
