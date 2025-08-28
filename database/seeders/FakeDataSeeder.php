<?php

namespace Database\Seeders;

use App\Enums\CourseStatusEnum;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Episode;
use App\Models\MoreDetail;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //teacher
        $user1 = User::create([
            'id' => 2,
            'first_name' => 'Tahsin',
            'last_name' => 'abo sherko',
            'profile_image_url' => 'profile.jpg',
            'email' => 'tahsin@gmail.com',
            'password' => '147258369',
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        $MoreDetail1 = MoreDetail::create([
            'id' => 1,
            'user_id' => 2,
            'job_title_id' => 2,
            'country_id' => 3,
            'education' => 'none',
        ]);

        $user1->badges()->attach([1, 2, 5]);
        $MoreDetail1->skills()->attach([2, 3, 4]);
        $MoreDetail1->languages()->attach(2, ['level' => 'mother_tongue']);
        $MoreDetail1->languages()->attach(5, ['level' => 'beginner']);

        //student
        $user2 = User::create([
            'id' => 3,
            'first_name' => 'Omar',
            'last_name' => 'Od',
            'profile_image_url' => 'personal_img.jpg',
            'email' => 'Omar@gmail.com',
            'password' => '147258369',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $MoreDetail2 = MoreDetail::create([
            'id' => 2,
            'user_id' => 3,
            'job_title_id' => 2,
            'country_id' => 10,
            'education' => 'none',
        ]);


        $user2->badges()->attach([1, 2, 5]);
        $MoreDetail2->skills()->attach([2, 3, 4]);
        $MoreDetail2->languages()->attach(3, ['level' => 'mother_tongue']);
        $MoreDetail2->languages()->attach(2, ['level' => 'beginner']);

        for ($i = 0; $i < 5; $i++) {
            $courses = [
                [
                    'title' => 'Laravel for beginner',
                    'description' => 'Laravel course that explains the basics of back-end concepts',
                    'image_url' => 'course1.png',
                    'topic_id' => 2,
                    'teacher_id' => 2,
                    'admin_id' => 1,
                    'difficulty_level' => 'beginner',
                    'total_time' => 23,
                    'price' => 100,
                    'rate' => 4,
                    'language_id' => 1,
                    'num_of_episodes' => 20,
                    'num_of_students_enrolled' => 1,
                    'publishing_request_date' => '2004-08-23',
                    'response_date' => '2005-08-23',
                    'status' => CourseStatusEnum::APPROVED,
                    'has_certificate' => true,
                    'total_quizzes' => 3
                ],
                [
                    'title' => 'Laravel for intermediate',
                    'description' => 'Laravel course that explains the intermediate concepts of back-end',
                    'image_url' => 'course2.png',
                    'topic_id' => 2,
                    'teacher_id' => 2,
                    'admin_id' => 1,
                    'difficulty_level' => 'intermediate',
                    'total_time' => 19,
                    'price' => 10,
                    'rate' => 0,
                    'language_id' => 1,
                    'num_of_episodes' => 10,
                    'num_of_students_enrolled' => 0,
                    'publishing_request_date' => '2004-08-23',
                    'response_date' => '2005-08-23',
                    'status' => CourseStatusEnum::REJECTED,
                    'has_certificate' => false,
                    'total_quizzes' => 1
                ],
                [
                    'title' => 'Laravel for expert',
                    'description' => 'Laravel course that explains advanced concepts of back-end',
                    'image_url' => 'course3.png',
                    'topic_id' => 2,
                    'teacher_id' => 2,
                    'admin_id' => 1,
                    'difficulty_level' => 'expert',
                    'total_time' => 40,
                    'price' => 32,
                    'rate' => 0,
                    'language_id' => 1,
                    'num_of_episodes' => 34,
                    'num_of_students_enrolled' => 1,
                    'publishing_request_date' => '2004-08-23',
                    'response_date' => null, // Note: This was missing in the original query
                    'status' => CourseStatusEnum::DRAFT,
                    'has_certificate' => false,
                    'total_quizzes' => 1
                ]
            ];

            foreach ($courses as $courseData)
                Course::create($courseData);
        }

        $user = User::withTrashed()->find(2);
        foreach (Course::all() as $course)
            $user->followed_courses()->attach($course, ['progress' => 2, 'perc_progress' => 66.6, 'num_of_completed_quizzes' => 1, 'rate' => 3]);

        for ($i = 0; $i < 3; $i++) {
            // episode 1
            Episode::create([
                'course_id' => $i + 1,
                'title' => 'Basics of Laravel and MVC',
                'episode_number' => 1,
                'duration' => 151,
            ]);

            // episode 2
            Episode::create([
                'course_id' => $i + 1,
                'title' => 'Routes and Controllers',
                'episode_number' => 2,
                'duration' => 151,
            ]);

            // episode 3
            Episode::create([
                'course_id' => $i + 1,
                'title' => 'Blade Engine',
                'episode_number' => 3,
                'duration' => 43,
            ]);
        }

        // comments
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 10; $j++)
                Comment::create([
                    'episode_id' => $i + 1,
                    'user_id' => 2,
                    'content' => 'this is the first comment, hello world',
                    'likes' => 203,
                ]);
        }

        // reply 1
        for ($i = 0; $i < 10; $i++) {
            Reply::create([
                'comment_id' => $i + 1,
                'user_id' => 2,
                'content' => 'this is the first reply, hello world',
                'likes' => 5
            ]);

            // reply 2
            Reply::create([
                'comment_id' => $i + 1,
                'user_id' => 2,
                'content' => 'this is the second reply, hello world',
                'likes' => 84
            ]);
        }

        // quiz 1
        $quiz1 = Quiz:: create([
            'episode_id' => 1,
            'num_of_questions' => 10,
        ]);

        // quiz 2
        $quiz2 = Quiz::create([
            'episode_id' => 2,
            'num_of_questions' => 10,
        ]);

        // quiz 3
        Quiz::create([
            'episode_id' => 3,
            'num_of_questions' => 10,
        ]);


        // questions in quiz 1
        for ($i = 0; $i < 3; $i++)
            for ($j = 0; $j < 10; $j++)
                Question::create([
                    'quiz_id' => $i + 1,
                    'question_number' => $j + 1,
                    'content' => 'what is your name?',
                    'answer_a' => 'Omar',
                    'answer_b' => 'Hamza',
                    'answer_c' => 'Ahmad',
                    'answer_d' => 'Ali',
                    'right_answer' => 'a'
                ]);

        $user->quizzes()->attach($quiz1, ['success' => false, 'mark' => 1, 'percentage_mark' => 33]);
        $user->quizzes()->attach($quiz2, ['success' => true, 'mark' => 2, 'percentage_mark' => 100]);
    }
}
