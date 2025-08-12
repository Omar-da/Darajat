<?php

namespace Database\Seeders;

use App\Models\JobTitle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobTitles = [
            ['title' => ['en' => 'Student', 'ar' => 'طالب']],
            ['title' => ['en' => 'Teacher', 'ar' => 'معلّم']],
            ['title' => ['en' => 'Professor', 'ar' => 'أستاذ جامعي']],
            ['title' => ['en' => 'Lecturer', 'ar' => 'محاضر']],
            ['title' => ['en' => 'Teaching Assistant', 'ar' => 'مساعد تدريس']],
            ['title' => ['en' => 'Tutor', 'ar' => 'مدرّس خصوصي']],
            ['title' => ['en' => 'Instructor', 'ar' => 'مدرب']],
            ['title' => ['en' => 'Curriculum Developer', 'ar' => 'مطوّر مناهج']],
            ['title' => ['en' => 'Instructional Designer', 'ar' => 'مصمم تعليمي']],
            ['title' => ['en' => 'Education Consultant', 'ar' => 'مستشار تعليمي']],
            ['title' => ['en' => 'School Administrator', 'ar' => 'مدير مدرسة']],
            ['title' => ['en' => 'Principal', 'ar' => 'ناظر مدرسة']],
            ['title' => ['en' => 'Vice Principal', 'ar' => 'وكيل مدرسة']],
            ['title' => ['en' => 'Dean', 'ar' => 'عميد']],
            ['title' => ['en' => 'Academic Advisor', 'ar' => 'مرشد أكاديمي']],
            ['title' => ['en' => 'Guidance Counselor', 'ar' => 'مرشد طلابي']],
            ['title' => ['en' => 'Special Education Teacher', 'ar' => 'معلّم التربية الخاصة']],
            ['title' => ['en' => 'ESL Teacher', 'ar' => 'معلّم لغة إنجليزية كلغة ثانية']],
            ['title' => ['en' => 'STEM Educator', 'ar' => 'معلّم STEM']],
            ['title' => ['en' => 'E-Learning Specialist', 'ar' => 'أخصائي تعليم إلكتروني']],
            ['title' => ['en' => 'EdTech Developer', 'ar' => 'مطوّر تقنيات تعليمية']],
            ['title' => ['en' => 'Online Course Creator', 'ar' => 'مصمم دورات تعليمية عبر الإنترنت']],
            ['title' => ['en' => 'Researcher', 'ar' => 'باحث']],
            ['title' => ['en' => 'PhD Candidate', 'ar' => 'طالب دكتوراه']],
            ['title' => ['en' => 'Postdoctoral Fellow', 'ar' => 'باحث ما بعد الدكتوراه']],
            ['title' => ['en' => 'Librarian', 'ar' => 'أمين مكتبة']],
            ['title' => ['en' => 'Education Technologist', 'ar' => 'أخصائي تقنيات التعليم']],
            ['title' => ['en' => 'School Psychologist', 'ar' => 'أخصائي نفسي مدرسي']],
            ['title' => ['en' => 'Education Policy Analyst', 'ar' => 'محلل سياسات تعليمية']],
            ['title' => ['en' => 'Corporate Trainer', 'ar' => 'مدرب مؤسسي']],
            ['title' => ['en' => 'Workshop Facilitator', 'ar' => 'ميسر ورش عمل']],
            ['title' => ['en' => 'Mentor', 'ar' => 'مرشد']],
            ['title' => ['en' => 'Coach', 'ar' => 'مدرب']],
            ['title' => ['en' => 'Freelance Educator', 'ar' => 'معلّم مستقل']],
            ['title' => ['en' => 'Homeschool Teacher', 'ar' => 'معلّم تعليم منزلي']],
            ['title' => ['en' => 'Teaching Fellow', 'ar' => 'زميل تدريس']],
            ['title' => ['en' => 'Graduate Assistant', 'ar' => 'مساعد دراسات عليا']],
            ['title' => ['en' => 'Adjunct Professor', 'ar' => 'أستاذ مساعد']],
            ['title' => ['en' => 'Department Head', 'ar' => 'رئيس قسم']],
            ['title' => ['en' => 'Chancellor', 'ar' => 'رئيس جامعة']],
        ];

        foreach($jobTitles as $jobTitle)
            JobTitle::create($jobTitle);
    }
}
