<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TryoutAttempt extends Model
{
    protected $fillable = [
        'user_id', 'tryout_id', 'started_at', 'finished_at', 'score',
        'correct_count', 'total_questions', 'duration_seconds', 'is_late',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'score' => 'integer',
            'correct_count' => 'integer',
            'total_questions' => 'integer',
            'duration_seconds' => 'integer',
            'is_late' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TryoutAnswer::class, 'attempt_id');
    }
}
