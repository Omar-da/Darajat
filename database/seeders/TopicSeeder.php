<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            // Category 1: Mathematics and Sciences
            ['title' => ['en' => 'Mathematics', 'ar' => 'الرياضيات'], 'category_id' => 1],
            ['title' => ['en' => 'Science', 'ar' => 'العلوم'], 'category_id' => 1],
            ['title' => ['en' => 'History', 'ar' => 'التاريخ'], 'category_id' => 1],
            ['title' => ['en' => 'Literature', 'ar' => 'الأدب'], 'category_id' => 1],
            ['title' => ['en' => 'Engineering', 'ar' => 'الهندسة'], 'category_id' => 1],
            ['title' => ['en' => 'Algebra', 'ar' => 'الجبر'], 'category_id' => 1],
            ['title' => ['en' => 'Geometry', 'ar' => 'الهندسة الرياضية'], 'category_id' => 1],
            ['title' => ['en' => 'Calculus', 'ar' => 'حساب التفاضل والتكامل'], 'category_id' => 1],
            ['title' => ['en' => 'Statistics', 'ar' => 'الإحصاء'], 'category_id' => 1],
            ['title' => ['en' => 'Biology', 'ar' => 'علم الأحياء'], 'category_id' => 1],
            ['title' => ['en' => 'Chemistry', 'ar' => 'الكيمياء'], 'category_id' => 1],
            ['title' => ['en' => 'Physics', 'ar' => 'الفيزياء'], 'category_id' => 1],
            ['title' => ['en' => 'Earth Science', 'ar' => 'علوم الأرض'], 'category_id' => 1],
            ['title' => ['en' => 'Astronomy', 'ar' => 'علم الفلك'], 'category_id' => 1],

            // Category 2: Computer Science and Technology
            ['title' => ['en' => 'Computer Science', 'ar' => 'علوم الحاسب'], 'category_id' => 2],
            ['title' => ['en' => 'Programming Fundamentals', 'ar' => 'أساسيات البرمجة'], 'category_id' => 2],
            ['title' => ['en' => 'Web Development', 'ar' => 'تطوير الويب'], 'category_id' => 2],
            ['title' => ['en' => 'Mobile App Development', 'ar' => 'تطوير تطبيقات الجوال'], 'category_id' => 2],
            ['title' => ['en' => 'Problem Solving', 'ar' => 'حل المشكلات'], 'category_id' => 2],
            ['title' => ['en' => 'Data Science', 'ar' => 'علم البيانات'], 'category_id' => 2],
            ['title' => ['en' => 'Machine Learning', 'ar' => 'التعلم الآلي'], 'category_id' => 2],
            ['title' => ['en' => 'Artificial Intelligence', 'ar' => 'الذكاء الاصطناعي'], 'category_id' => 2],
            ['title' => ['en' => 'Cybersecurity', 'ar' => 'الأمن السيبراني'], 'category_id' => 2],
            ['title' => ['en' => 'Cloud Computing', 'ar' => 'الحوسبة السحابية'], 'category_id' => 2],
            ['title' => ['en' => 'Blockchain', 'ar' => 'بلوك تشين'], 'category_id' => 2],
            ['title' => ['en' => 'Game Development', 'ar' => 'تطوير الألعاب'], 'category_id' => 2],
            ['title' => ['en' => 'UI/UX Design', 'ar' => 'تصميم واجهة المستخدم'], 'category_id' => 2],
            ['title' => ['en' => 'DevOps', 'ar' => 'ديف أوبس'], 'category_id' => 2],

            // Category 3: Humanities and Social Sciences
            ['title' => ['en' => 'World History', 'ar' => 'التاريخ العالمي'], 'category_id' => 3],
            ['title' => ['en' => 'U.S. History', 'ar' => 'تاريخ الولايات المتحدة'], 'category_id' => 3],
            ['title' => ['en' => 'European History', 'ar' => 'التاريخ الأوروبي'], 'category_id' => 3],
            ['title' => ['en' => 'Political Science', 'ar' => 'العلوم السياسية'], 'category_id' => 3],
            ['title' => ['en' => 'Psychology', 'ar' => 'علم النفس'], 'category_id' => 3],
            ['title' => ['en' => 'Sociology', 'ar' => 'علم الاجتماع'], 'category_id' => 3],
            ['title' => ['en' => 'Philosophy', 'ar' => 'الفلسفة'], 'category_id' => 3],
            ['title' => ['en' => 'Anthropology', 'ar' => 'الأنثروبولوجيا'], 'category_id' => 3],
            ['title' => ['en' => 'Economics', 'ar' => 'الاقتصاد'], 'category_id' => 3],
            ['title' => ['en' => 'Geography', 'ar' => 'الجغرافيا'], 'category_id' => 3],

            // Category 4: Business and Professional Development
            ['title' => ['en' => 'Entrepreneurship', 'ar' => 'ريادة الأعمال'], 'category_id' => 4],
            ['title' => ['en' => 'Marketing', 'ar' => 'التسويق'], 'category_id' => 4],
            ['title' => ['en' => 'Digital Marketing', 'ar' => 'التسويق الرقمي'], 'category_id' => 4],
            ['title' => ['en' => 'Financial Literacy', 'ar' => 'الثقافة المالية'], 'category_id' => 4],
            ['title' => ['en' => 'Project Management', 'ar' => 'إدارة المشاريع'], 'category_id' => 4],
            ['title' => ['en' => 'Business Communication', 'ar' => 'الاتصال التجاري'], 'category_id' => 4],
            ['title' => ['en' => 'Human Resources', 'ar' => 'الموارد البشرية'], 'category_id' => 4],
            ['title' => ['en' => 'Accounting', 'ar' => 'المحاسبة'], 'category_id' => 4],
            ['title' => ['en' => 'E-commerce', 'ar' => 'التجارة الإلكترونية'], 'category_id' => 4],

            // Category 5: Creative Arts and Design
            ['title' => ['en' => 'Graphic Design', 'ar' => 'التصميم الجرافيكي'], 'category_id' => 5],
            ['title' => ['en' => 'Digital Art', 'ar' => 'الفن الرقمي'], 'category_id' => 5],
            ['title' => ['en' => 'Photography', 'ar' => 'التصوير الفوتوغرافي'], 'category_id' => 5],
            ['title' => ['en' => 'Creative Writing', 'ar' => 'الكتابة الإبداعية'], 'category_id' => 5],
            ['title' => ['en' => 'Music Theory', 'ar' => 'نظرية الموسيقى'], 'category_id' => 5],
            ['title' => ['en' => 'Film Production', 'ar' => 'إنتاج الأفلام'], 'category_id' => 5],
            ['title' => ['en' => 'Animation', 'ar' => 'الرسوم المتحركة'], 'category_id' => 5],
            ['title' => ['en' => 'Interior Design', 'ar' => 'التصميم الداخلي'], 'category_id' => 5],
            ['title' => ['en' => 'Fashion Design', 'ar' => 'تصميم الأزياء'], 'category_id' => 5],
            ['title' => ['en' => 'Architecture', 'ar' => 'العمارة'], 'category_id' => 5],
            ['title' => ['en' => '3D Modeling', 'ar' => 'النمذجة ثلاثية الأبعاد'], 'category_id' => 5],
            ['title' => ['en' => 'Game Design', 'ar' => 'تصميم الألعاب'], 'category_id' => 5],

            // Category 6: Languages and Linguistics
            ['title' => ['en' => 'English Language', 'ar' => 'اللغة الإنجليزية'], 'category_id' => 6],
            ['title' => ['en' => 'Spanish Language', 'ar' => 'اللغة الإسبانية'], 'category_id' => 6],
            ['title' => ['en' => 'French Language', 'ar' => 'اللغة الفرنسية'], 'category_id' => 6],
            ['title' => ['en' => 'German Language', 'ar' => 'اللغة الألمانية'], 'category_id' => 6],
            ['title' => ['en' => 'Chinese Language', 'ar' => 'اللغة الصينية'], 'category_id' => 6],
            ['title' => ['en' => 'Japanese Language', 'ar' => 'اللغة اليابانية'], 'category_id' => 6],
            ['title' => ['en' => 'Arabic Language', 'ar' => 'اللغة العربية'], 'category_id' => 6],
            ['title' => ['en' => 'Sign Language', 'ar' => 'لغة الإشارة'], 'category_id' => 6],
            ['title' => ['en' => 'Linguistics', 'ar' => 'اللغويات'], 'category_id' => 6],
            ['title' => ['en' => 'Translation Studies', 'ar' => 'دراسات الترجمة'], 'category_id' => 6],

            // Category 7: Test Preparation and Academic Skills
            ['title' => ['en' => 'SAT Preparation', 'ar' => 'تحضير اختبار SAT'], 'category_id' => 7],
            ['title' => ['en' => 'ACT Preparation', 'ar' => 'تحضير اختبار ACT'], 'category_id' => 7],
            ['title' => ['en' => 'GRE Preparation', 'ar' => 'تحضير اختبار GRE'], 'category_id' => 7],
            ['title' => ['en' => 'GMAT Preparation', 'ar' => 'تحضير اختبار GMAT'], 'category_id' => 7],
            ['title' => ['en' => 'IELTS Preparation', 'ar' => 'تحضير اختبار IELTS'], 'category_id' => 7],
            ['title' => ['en' => 'TOEFL Preparation', 'ar' => 'تحضير اختبار TOEFL'], 'category_id' => 7],
            ['title' => ['en' => 'MCAT Preparation', 'ar' => 'تحضير اختبار MCAT'], 'category_id' => 7],
            ['title' => ['en' => 'LSAT Preparation', 'ar' => 'تحضير اختبار LSAT'], 'category_id' => 7],
            ['title' => ['en' => 'Certification Exams', 'ar' => 'اختبارات الشهادات'], 'category_id' => 7],
            ['title' => ['en' => 'College Admissions', 'ar' => 'القبول الجامعي'], 'category_id' => 7],

            // Category 8: Life Skills and Personal Development
            ['title' => ['en' => 'Time Management', 'ar' => 'إدارة الوقت'], 'category_id' => 8],
            ['title' => ['en' => 'Public Speaking', 'ar' => 'التحدث أمام الجمهور'], 'category_id' => 8],
            ['title' => ['en' => 'Research Methods', 'ar' => 'مناهج البحث'], 'category_id' => 8],
            ['title' => ['en' => 'Data Analysis', 'ar' => 'تحليل البيانات'], 'category_id' => 8],
            ['title' => ['en' => 'Design Thinking', 'ar' => 'التفكير التصميمي'], 'category_id' => 8],
            ['title' => ['en' => 'Leadership', 'ar' => 'القيادة'], 'category_id' => 8],
            ['title' => ['en' => 'Collaboration', 'ar' => 'التعاون'], 'category_id' => 8],
            ['title' => ['en' => 'Writing', 'ar' => 'الكتابة'], 'category_id' => 8],
            ['title' => ['en' => 'Mindfulness', 'ar' => 'اليقظة الذهنية'], 'category_id' => 8],
            ['title' => ['en' => 'Nutrition', 'ar' => 'التغذية'], 'category_id' => 8],
            ['title' => ['en' => 'Fitness', 'ar' => 'اللياقة البدنية'], 'category_id' => 8],
            ['title' => ['en' => 'Personal Finance', 'ar' => 'المالية الشخصية'], 'category_id' => 8],
            ['title' => ['en' => 'Parenting', 'ar' => 'تربية الأطفال'], 'category_id' => 8],
            ['title' => ['en' => 'Relationships', 'ar' => 'العلاقات'], 'category_id' => 8],
            ['title' => ['en' => 'Career Development', 'ar' => 'التطور الوظيفي'], 'category_id' => 8],
            ['title' => ['en' => 'Study Skills', 'ar' => 'مهارات الدراسة'], 'category_id' => 8],
            ['title' => ['en' => 'Critical Thinking', 'ar' => 'التفكير النقدي'], 'category_id' => 8]
        ];

        foreach($topics as $topic)
            Topic::create($topic);
    }
}
