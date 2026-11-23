<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tryout extends Model
{
    public const MIN_ACTIVE_QUESTIONS = 5;

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

    public function scopeAvailable(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        if ($this->starts_at !== null && $this->starts_at->greaterThan($now)) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->lessThan($now)) {
            return false;
        }

        return true;
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
