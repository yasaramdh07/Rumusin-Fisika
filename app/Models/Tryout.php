<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tryout extends Model
{
    protected $fillable = [
        'lesson_id', 'title', 'slug', 'description', 'duration_minutes', 'is_active', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'tryout_question')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TryoutAttempt::class);
    }
}
