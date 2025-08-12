<?php

namespace App\Policies;

use App\Enums\CourseStatusEnum;
use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;


class CoursePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Course $course): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return false;
    }

    public function owner(User $user, Course $course): Response
    {
        return $user->id === $course->teacher_id
            ? Response::allow()
            : Response::deny(__('msg.are_not_owner'), 403);
    }


    public function statusApproved(User $user, Course $course): Response
    {
        return $course->status == 'approved'
            ? Response::allow()
            : Response::deny(__('msg.course_not_approved'), 403);
    }

    public function quizAction(User $user, Course $course): Response
    {
        return ($course->status == 'draft' || $course->status == 'rejected')
            ? Response::allow()
            : Response::deny(__('msg.quiz_action'), 403);
    }
}
