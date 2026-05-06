<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'model',
    'base_url',
    'temperature',
    'top_p',
    'repeat_penalty',
    'system_prompt',
    'is_active'
])]
class OllamaConfig extends Model
{
    protected $casts = [
        'temperature'    => 'float',
        'top_p'          => 'float',
        'repeat_penalty' => 'float',
        'is_active'      => 'boolean',
    ];

    /** Ambil config yang sedang aktif */
    public static function active(): self
    {
        return static::where('is_active', true)->latest()->firstOrFail();
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(\App\Models\ChatSession::class);
    }
}
