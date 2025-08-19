<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftQuestion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'draft_quiz_id',
        'question_number',
        'content',
        'answer_a',
        'answer_b',
        'answer_c',
        'answer_d',
        'explanation',
        'right_answer',
    ];

    public function draft_quiz(): BelongsTo
    {
        return $this->belongsTo(DraftQuiz::class);
    }
}
