<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'order_number',
        'amount',
        'currency',
        'status',
        'payment_intent_id',
        'stripe_customer_id',
        'payment_status',
        'product_id',
        'product_name',
        'quantity',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'metadata' => 'array', // Automatically cast the JSON field to an array
        'amount' => 'integer',
        'quantity' => 'integer',
        'status' => 'enum'
    ];

    /**
     * Get the user that owns the order.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(MoreDetail::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(MoreDetail::class, 'teacher_id');
    }

    /**
     * Get the product that was ordered.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }


}