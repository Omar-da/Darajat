<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            // ----- Programming Languages -----
            ['title' => ['en' => 'HTML', 'ar' => 'HTML']],
            ['title' => ['en' => 'CSS', 'ar' => 'CSS']],
            ['title' => ['en' => 'JavaScript', 'ar' => 'JavaScript']],
            ['title' => ['en' => 'TypeScript', 'ar' => 'TypeScript']],
            ['title' => ['en' => 'Python', 'ar' => 'Python']],
            ['title' => ['en' => 'Java', 'ar' => 'Java']],
            ['title' => ['en' => 'C++', 'ar' => 'C++']],
            ['title' => ['en' => 'C#', 'ar' => 'C#']],
            ['title' => ['en' => 'PHP', 'ar' => 'PHP']],
            ['title' => ['en' => 'Ruby', 'ar' => 'Ruby']],
            ['title' => ['en' => 'Swift', 'ar' => 'Swift']],
            ['title' => ['en' => 'Kotlin', 'ar' => 'Kotlin']],
            ['title' => ['en' => 'Go', 'ar' => 'Go']],
            ['title' => ['en' => 'Rust', 'ar' => 'Rust']],

            // ----- Databases -----
            ['title' => ['en' => 'SQL', 'ar' => 'SQL']],
            ['title' => ['en' => 'NoSQL', 'ar' => 'NoSQL']],
            ['title' => ['en' => 'MySQL', 'ar' => 'MySQL']],
            ['title' => ['en' => 'PostgreSQL', 'ar' => 'PostgreSQL']],
            ['title' => ['en' => 'MongoDB', 'ar' => 'MongoDB']],

            // ----- Frontend Frameworks/Libraries -----
            ['title' => ['en' => 'React', 'ar' => 'React']],
            ['title' => ['en' => 'Angular', 'ar' => 'Angular']],
            ['title' => ['en' => 'Vue.js', 'ar' => 'Vue.js']],
            ['title' => ['en' => 'Svelte', 'ar' => 'Svelte']],

            // ----- Backend Frameworks -----
            ['title' => ['en' => 'Node.js', 'ar' => 'Node.js']],
            ['title' => ['en' => 'Django', 'ar' => 'Django']],
            ['title' => ['en' => 'Flask', 'ar' => 'Flask']],
            ['title' => ['en' => 'Laravel', 'ar' => 'Laravel']],
            ['title' => ['en' => 'Spring Boot', 'ar' => 'Spring Boot']],

            // ----- DevOps & Cloud -----
            ['title' => ['en' => 'Git', 'ar' => 'Git']],
            ['title' => ['en' => 'Docker', 'ar' => 'Docker']],
            ['title' => ['en' => 'Kubernetes', 'ar' => 'Kubernetes']],
            ['title' => ['en' => 'AWS', 'ar' => 'AWS']],
            ['title' => ['en' => 'Azure', 'ar' => 'Azure']],
            ['title' => ['en' => 'Google Cloud', 'ar' => 'Google Cloud']],

            // ----- APIs & Web Services -----
            ['title' => ['en' => 'RESTful APIs', 'ar' => 'RESTful APIs']],
            ['title' => ['en' => 'GraphQL', 'ar' => 'GraphQL']],

            // ----- Data Science & AI/ML -----
            ['title' => ['en' => 'Machine Learning', 'ar' => 'Machine Learning']],
            ['title' => ['en' => 'Data Science', 'ar' => 'Data Science']],
            ['title' => ['en' => 'TensorFlow', 'ar' => 'TensorFlow']],
            ['title' => ['en' => 'PyTorch', 'ar' => 'PyTorch']],
            ['title' => ['en' => 'Pandas', 'ar' => 'Pandas']],
            ['title' => ['en' => 'NumPy', 'ar' => 'NumPy']],

            // ----- Cybersecurity & Blockchain -----
            ['title' => ['en' => 'Cybersecurity', 'ar' => 'Cybersecurity']],
            ['title' => ['en' => 'Ethical Hacking', 'ar' => 'Ethical Hacking']],
            ['title' => ['en' => 'Blockchain', 'ar' => 'Blockchain']],
            ['title' => ['en' => 'Solidity', 'ar' => 'Solidity']],

            // ----- Game Development -----
            ['title' => ['en' => 'Unity', 'ar' => 'Unity']],
            ['title' => ['en' => 'Unreal Engine', 'ar' => 'Unreal Engine']],

            // ----- Soft Skills -----
            ['title' => ['en' => 'Communication', 'ar' => 'التواصل']],
            ['title' => ['en' => 'Teamwork', 'ar' => 'العمل الجماعي']],
            ['title' => ['en' => 'Leadership', 'ar' => 'القيادة']],
            ['title' => ['en' => 'Problem Solving', 'ar' => 'حل المشكلات']],
            ['title' => ['en' => 'Critical Thinking', 'ar' => 'التفكير النقدي']],
            ['title' => ['en' => 'Time Management', 'ar' => 'إدارة الوقت']],
            ['title' => ['en' => 'Creativity', 'ar' => 'الإبداع']],
            ['title' => ['en' => 'Adaptability', 'ar' => 'القدرة على التكيف']],
            ['title' => ['en' => 'Emotional Intelligence', 'ar' => 'الذكاء العاطفي']],
            ['title' => ['en' => 'Public Speaking', 'ar' => 'التحدث أمام الجمهور']],
            ['title' => ['en' => 'Writing', 'ar' => 'الكتابة']],
            ['title' => ['en' => 'Presentation', 'ar' => 'العرض التقديمي']],
            ['title' => ['en' => 'Negotiation', 'ar' => 'التفاوض']],
            ['title' => ['en' => 'Project Management', 'ar' => 'إدارة المشاريع']],
            ['title' => ['en' => 'Research', 'ar' => 'البحث']],
            ['title' => ['en' => 'Analytical Skills', 'ar' => 'المهارات التحليلية']],
            ['title' => ['en' => 'Attention to Detail', 'ar' => 'الانتباه للتفاصيل']],
            ['title' => ['en' => 'Collaboration', 'ar' => 'التعاون']],

            // ----- Education & E-Learning -----
            ['title' => ['en' => 'Curriculum Development', 'ar' => 'تطوير المناهج']],
            ['title' => ['en' => 'E-Learning', 'ar' => 'التعليم الإلكتروني']],
            ['title' => ['en' => 'Instructional Design', 'ar' => 'التصميم التعليمي']],
            ['title' => ['en' => 'Pedagogy', 'ar' => 'علم التربية']],
            ['title' => ['en' => 'Classroom Management', 'ar' => 'إدارة الصف']],
            ['title' => ['en' => 'Student Engagement', 'ar' => 'إشراك الطلاب']],
            ['title' => ['en' => 'Assessment Design', 'ar' => 'تصميم التقييم']],
            ['title' => ['en' => 'Educational Technology', 'ar' => 'تكنولوجيا التعليم']],
            ['title' => ['en' => 'Moodle', 'ar' => 'Moodle']],
            ['title' => ['en' => 'Blackboard', 'ar' => 'Blackboard']],
            ['title' => ['en' => 'Canvas LMS', 'ar' => 'Canvas LMS']],
            ['title' => ['en' => 'Teaching', 'ar' => 'التدريس']],
            ['title' => ['en' => 'Tutoring', 'ar' => 'التدريس الخصوصي']],
            ['title' => ['en' => 'STEM Education', 'ar' => 'تعليم STEM']],
            ['title' => ['en' => 'Literacy Development', 'ar' => 'تنمية الإلمام بالقراءة والكتابة']],
            ['title' => ['en' => 'Special Education', 'ar' => 'التعليم الخاص']]
        ];

        foreach ($skills as $skill)
            Skill::create($skill);
    }
}
