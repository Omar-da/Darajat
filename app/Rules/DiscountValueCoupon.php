<?php

namespace App\Rules;

use App\Enums\DiscountTypeEnum;
use App\Responses\Response;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DiscountValueCoupon implements ValidationRule
{
    protected $course_price;
    protected $discount_type;

    public function __construct($course_price, $discount_type)
    {
        $this->course_price = $course_price;
        $this->discount_type = $discount_type;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if($this->discount_type === DiscountTypeEnum::PERCENTAGE->value && $value > 100) {
            $fail(__('msg.discount_value_percentage'));
        }

        if($this->discount_type === DiscountTypeEnum::FIXED->value && $value > $this->course_price) {
            $fail(__('msg.discount_value_fixed'));
        }
    }
}
