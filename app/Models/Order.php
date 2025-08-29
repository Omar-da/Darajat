<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'order_number',
        'amount',
        'currency',
        'status',
        'payment_intent_id',
        'course_id',
        'course_name',
        'purchase_date',
    ];

    protected $casts = [
        'metadata' => 'array', // Automatically cast the JSON field to an array
        'amount' => 'integer',
        'quantity' => 'integer',
        'status' => OrderStatusEnum::class
    ];


    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }


}