<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['name' => ['en' => 'English', 'ar' => 'الإنجليزية']],
            ['name' => ['en' => 'Spanish', 'ar' => 'الإسبانية']],
            ['name' => ['en' => 'French', 'ar' => 'الفرنسية']],
            ['name' => ['en' => 'German', 'ar' => 'الألمانية']],
            ['name' => ['en' => 'Mandarin Chinese', 'ar' => 'الصينية الماندرين']],
            ['name' => ['en' => 'Arabic', 'ar' => 'العربية']],
            ['name' => ['en' => 'Hindi', 'ar' => 'الهندية']],
            ['name' => ['en' => 'Portuguese', 'ar' => 'البرتغالية']],
            ['name' => ['en' => 'Russian', 'ar' => 'الروسية']],
            ['name' => ['en' => 'Japanese', 'ar' => 'اليابانية']],
            ['name' => ['en' => 'Korean', 'ar' => 'الكورية']],
            ['name' => ['en' => 'Italian', 'ar' => 'الإيطالية']],
            ['name' => ['en' => 'Dutch', 'ar' => 'الهولندية']],
            ['name' => ['en' => 'Swedish', 'ar' => 'السويدية']],
            ['name' => ['en' => 'Turkish', 'ar' => 'التركية']],
            ['name' => ['en' => 'Polish', 'ar' => 'البولندية']],
            ['name' => ['en' => 'Vietnamese', 'ar' => 'الفيتنامية']],
            ['name' => ['en' => 'Thai', 'ar' => 'التايلاندية']],
            ['name' => ['en' => 'Persian (Farsi)', 'ar' => 'الفارسية']],
            ['name' => ['en' => 'Urdu', 'ar' => 'الأردية']],
            ['name' => ['en' => 'Indonesian', 'ar' => 'الإندونيسية']],
            ['name' => ['en' => 'Malay', 'ar' => 'الماليزية']],
            ['name' => ['en' => 'Hebrew', 'ar' => 'العبرية']],
            ['name' => ['en' => 'Greek', 'ar' => 'اليونانية']],
            ['name' => ['en' => 'Czech', 'ar' => 'التشيكية']],
            ['name' => ['en' => 'Hungarian', 'ar' => 'المجرية']],
            ['name' => ['en' => 'Romanian', 'ar' => 'الرومانية']],
            ['name' => ['en' => 'Danish', 'ar' => 'الدنماركية']],
            ['name' => ['en' => 'Finnish', 'ar' => 'الفنلندية']],
            ['name' => ['en' => 'Norwegian', 'ar' => 'النرويجية']],
            ['name' => ['en' => 'Ukrainian', 'ar' => 'الأوكرانية']],
            ['name' => ['en' => 'Bengali', 'ar' => 'البنغالية']],
            ['name' => ['en' => 'Filipino (Tagalog)', 'ar' => 'الفلبينية (تاغالوغ)']],
            ['name' => ['en' => 'Swahili', 'ar' => 'السواحيلية']],
            ['name' => ['en' => 'Afrikaans', 'ar' => 'الأفريقانية']],
            ['name' => ['en' => 'Bulgarian', 'ar' => 'البلغارية']],
            ['name' => ['en' => 'Croatian', 'ar' => 'الكرواتية']],
            ['name' => ['en' => 'Slovak', 'ar' => 'السلوفاكية']],
            ['name' => ['en' => 'Serbian', 'ar' => 'الصربية']],
            ['name' => ['en' => 'Catalan', 'ar' => 'الكتالونية']],
        ];

        foreach ($languages as $language)
            Language::create($language);
    }
}
