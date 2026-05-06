<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ollama_configs', function (Blueprint $table) {
            $table->id();
            $table->string('model')->default('llama3.2:3b');           // model yang dipakai
            $table->string('base_url')->default('http://localhost:11434');
            $table->float('temperature')->default(0.7);
            $table->float('top_p')->default(0.9);
            $table->float('repeat_penalty')->default(1.1);
            $table->text('system_prompt');
            $table->boolean('is_active')->default(true);               // config aktif
            $table->timestamps();
        });

        // Seed default config
        DB::table('ollama_configs')->insert([
            'model'         => 'llama3.2:3b',
            'base_url'      => 'http://localhost:11434',
            'temperature'   => 0.7,
            'top_p'         => 0.9,
            'repeat_penalty' => 1.1,
            'system_prompt' => 'Kamu adalah ChatBotUi, asisten AI yang cerdas, ramah, dan membantu. Jawab dalam bahasa yang sama dengan pertanyaan pengguna (Indonesia atau Inggris). Berikan jawaban yang jelas, terstruktur, dan mudah dipahami.',
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ollama_configs');
    }
};
