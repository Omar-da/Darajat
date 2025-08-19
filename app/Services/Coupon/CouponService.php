<?php

namespace App\Services\Coupon;

use App\Enums\CourseStatusEnum;
use App\Enums\DiscountTypeEnum;
use App\Http\Resources\Coupon\CouponResource;
use App\Http\Resources\Coupon\CouponWithDetailsResource;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class CouponService
{
    public function index($course_id): array
    {
        $course = Course::query()->find($course_id);

        $coupons = $course->coupons;

        return ['data' => CouponResource::collection($coupons), 'message' => __('msg.coupons_retrieved'), 'code' => 200];
    }

    public function store($request, $course_id): array
    {
        $course = Course::query()->find($course_id);

        Gate::authorize('statusApproved', $course);

        $coupon = Coupon::query()->create([
            'course_id' => $course_id,
            'code' => strtoupper($request['code']),
            'discount_type' => $request['discount_type'],
            'discount_value' => $request['discount_value'],
            'expires_at' => $request['expires_at'] ?? null,
            'max_uses' => $request['max_uses'] ?? null
        ]);

        return ['data' => new CouponWithDetailsResource($coupon), 'message' => __('msg.coupon_created'), 'code' => 201];
    }

    public function update($request, $id): array
    {
        $coupon = Coupon::query()->find($id);

        $coupon->update([
            'code' => strtoupper($request['code']),
            'discount_type' => $request['discount_type'],
            'discount_value' => $request['discount_value'],
            'expires_at' => $request['expires_at'] ?? null,
            'max_uses' => $request['max_uses'] ?? null
        ]);

        return ['data' => new CouponWithDetailsResource($coupon), 'message' => __('msg.coupon_updated'), 'code' => 200];
    }

    public function show($id): array
    {
        $coupon = Coupon::query()->find($id);

        return ['data' => new CouponWithDetailsResource($coupon), 'message' => __('msg.coupon_retrieved'), 'code' => 200];
    }

    public function destroy($id): array
    {
        $coupon = Coupon::query()->find($id);

        $coupon->delete();

        return ['message' => __('msg.coupon_deleted'), 'code' => 200];
    }

    public function applyCoupon($id, $request): array
    {
        // Fetch authenticated user.
        $user = auth('api')->user();

        // Make query for get a specific course.
        $course = Course::query()->find($id);

        // Check the course is existing.
        if (is_null($course)) {
            return ['message' => __('msg.course_not_found'), 'code' => 404];
        }

        if ($course->status !== CourseStatusEnum::APPROVED || $user->id == $course->teacher_id) {
            return ['message' => __('msg.unauthorized'), 'code' => 403];
        }

        if ($user->followed_courses()->where('course_id', $course->id)->exists()) {
            return ['message' => __('msg.already_subscribed'), 'code' => 409];
        }

        // Make query for get a specific coupon from the course.
        $coupon = $course->coupons()->where('code', $request['code'])->first();

        if (is_null($coupon)) {
            return ['message' => __('msg.is_not_correct'), 'code' => 409];
        }
        // Check the coupon is not expired and fully using.
        if (!$coupon->isValid()) {
            return ['message' => __('msg.coupon_exp_fully'), 'code' => 400];
        }

        $user->followed_courses()->attach($course->id);
        $user->appliedCoupons()->attach($coupon->id);

        $coupon->increment('use_count');

        // Calculate a price the course after discount with two status discount.
        $price_after_discount = $coupon->discount_type === DiscountTypeEnum::FIXED ?
            $course->price - $coupon->discount_value :
            $course->price - $course->price * $coupon->discount_value / 100;

        // Return array with keys (data, message, code).
        return ['data' =>
            [
                'original_price' => $course->price,
                'price_after_discount' => $price_after_discount,
            ],
            'message' => __('msg.coupon_applied'),
            'code' => 200
        ];

    }
}
