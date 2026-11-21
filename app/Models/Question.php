<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    protected $fillable = [
        'lesson_id', 'body', 'option_a', 'option_b', 'option_c', 'option_d',
        'correct_option', 'explanation', 'difficulty',
    ];

    // Jaring pengaman: kunci dan pembahasan tidak ikut ter-serialize ke klien secara tidak sengaja.
    protected $hidden = ['correct_option', 'explanation'];

    protected function casts(): array
    {
        return ['difficulty' => 'integer'];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function tryouts(): BelongsToMany
    {
        return $this->belongsToMany(Tryout::class, 'tryout_question')
            ->withPivot('position')
            ->withTimestamps();
    }
}
