<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftQuiz extends Model
{
    public $timestamps = false;

    public $fillable = [
        'draft_episode_id',
        'num_of_questions',
    ];

    public function draft_episode(): BelongsTo
    {
        return $this->belongsTo(DraftEpisode::class);
    }

    public function draft_questions(): HasMany
    {
        return $this->hasMany(DraftQuestion::class);
    }

}
