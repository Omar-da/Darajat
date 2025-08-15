<?php

namespace Database\Seeders;

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
            'email' => 'tahsin@gmail.com',
            'password' => '147258369',
            'role' => 'teacher',
        ]);

        $MoreDetail1 = MoreDetail::create([
            'id' => 1,
            'user_id' =>  2,
            'job_title_id' => 2,
            'country_id' => 3,
            'education' => 'none',
        ]);

        $user1->badges()->attach([1,2,5]);
        $MoreDetail1->skills()->attach([2,3,4]);
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
        ]);

        $MoreDetail2 = MoreDetail::create([
            'id' => 2,
            'user_id' =>  3,
            'job_title_id' => 2,
            'country_id' => 10,
            'education' => 'none',
        ]);


        $user2->badges()->attach([1,2,5]);
        $MoreDetail2->skills()->attach([2,3,4]);
        $MoreDetail2->languages()->attach(3, ['level' => 'mother_tongue']);
        $MoreDetail2->languages()->attach(2, ['level' => 'beginner']);

        for($i=0; $i<5; $i++)
        {
            DB::insert("INSERT INTO 'courses' 
            ('title'               , 'description'                                                 , 'image_url'  , 'topic_id', 'teacher_id', 'admin_id', 'difficulty_level', 'total_time', 'price', 'rate', 'language_id', 'num_of_episodes', 'num_of_students_enrolled', 'publishing_request_date', 'response_date', 'status'  , 'has_certificate', 'total_quizzes') VALUES
            ('Laravel for beginner', 'Laravel course that explains the basics of back-end concepts', 'course1.png', '2'       , '2'         , '1'       , 'beginner'       , '23'        , '0'    , '4'   , '1'          , '20'             , '1'                       , '2004-08-23'             , '2005-08-23'   , 'approved', 'true'           , '1');
            ");
            DB::insert("INSERT INTO 'courses' 
            ('title'                   , 'description'                                                               , 'image_url'  , 'topic_id', 'teacher_id', 'admin_id', 'difficulty_level', 'total_time', 'price', 'rate', 'language_id', 'num_of_episodes', 'num_of_students_enrolled', 'publishing_request_date', 'response_date', 'status'  , 'has_certificate', 'total_quizzes') VALUES
            ('Laravel for intermediate', 'Laravel course that explains the intermediate concepts of back-end', 'course2.png', '2'       , '2'         , '1'       , 'intermediate'    , '19'        , '10'   , '0'   , '1'          , '10'             , '0'                       , '2004-08-23'             , '2005-08-23'   , 'rejected', 'false'          , '1');
            ");
            DB::insert("INSERT INTO 'courses' 
            ('title'               , 'description'                                               , 'image_url'  , 'topic_id', 'teacher_id', 'admin_id', 'difficulty_level', 'total_time', 'price', 'rate', 'language_id', 'num_of_episodes', 'num_of_students_enrolled', 'publishing_request_date', 'status'  , 'has_certificate', 'total_quizzes') VALUES
            ('Laravel for expert'  , 'Laravel course that explains advanced concepts of back-end', 'course3.png', '2'       , '2'         , '1'       , 'expert'          , '40'        , '32'   , '0'   , '1'          , '34'             , '1'                       , '2004-08-23'             , 'pending' , 'false'          , '1');
            ");
        }
    
        $user = User::withTrashed()->find(2);
        foreach(Course::all() as $course)
            $user->followed_courses()->attach($course, ['progress' => 2, 'perc_progress' => 66.6, 'num_of_completed_quizzes' => 1, 'rate' => 3]);

        for($i=0; $i<3; $i++)
        {
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
        for($i=0; $i<3; $i++)
        {
            for($j=0; $j<10; $j++)
            Comment::create([       
                'episode_id' => $i + 1,
                'user_id' => 2,
                'content' => 'this is the first comment, hello world',
                'likes' => 203,
            ]);
        }

        // reply 1
        for($i=0; $i<10; $i++)
        {
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
            'num_of_questions' => 3,
        ]);

        // quiz 2
        $quiz2 = Quiz::create([
            'episode_id' => 2,
            'num_of_questions' => 2,
        ]);

        // quiz 3
        Quiz::create([
            'episode_id' => 3,
            'num_of_questions' => 2,
        ]);


        // questions in quiz 1
        for($i=0; $i<3; $i++)
        for($j=0; $j<10; $j++)
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
