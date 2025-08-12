<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'title' => ['en' => 'Mathematics and Sciences', 'ar' => 'الرياضيات والعلوم'],
                'image_url' => 'sience_and_math.jpg'
            ],
            [
                'id' => 2,
                'title' => ['en' => 'Computer Science and Technology', 'ar' => 'علوم الحاسب والتكنولوجيا'],
                'image_url' => 'computer_science_and_technology.jpg'
            ],
            [
                'id' => 3,
                'title' => ['en' => 'Humanities and Social Sciences', 'ar' => 'العلوم الإنسانية والاجتماعية'],
                'image_url' => 'humanities_and_social_sciences.png'
            ],
            [
                'id' => 4,
                'title' => ['en' => 'Business and Professional Development', 'ar' => 'الأعمال والتطوير المهني'],
                'image_url' => 'business_and_professional_development.png'
            ],
            [
                'id' => 5,
                'title' => ['en' => 'Creative Arts and Design', 'ar' => 'الفنون الإبداعية والتصميم'],
                'image_url' => 'creative_arts_and_design.jpg'
            ],
            [
                'id' => 6,
                'title' => ['en' => 'Languages and Linguistics', 'ar' => 'اللغويات واللغات'],
                'image_url' => 'languages_and_linguistics.png'
            ],
            [
                'id' => 7,
                'title' => ['en' => 'Test Preparation and Academic Skills', 'ar' => 'تحضير الاختبارات والمهارات الأكاديمية'],
                'image_url' => 'test_preparation_and_academic_skills.png'
            ],
            [
                'id' => 8,
                'title' => ['en' => 'Life Skills and Personal Development', 'ar' => 'مهارات الحياة والتطوير الشخصي'],
                'image_url' => 'life_skills_and_personal_development.png'
            ]
        ];

        foreach ($categories as $category)
            Category::create($category);
    }
}
