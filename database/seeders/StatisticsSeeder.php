<?php

namespace Database\Seeders;

use App\Models\Statistic;
use Illuminate\Database\Seeder;

class StatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statistics = [
            ['title' => ['en' => 'Current Enthusiasm', 'ar' => 'الحماس الحالي']],
            ['title' => ['en' => 'Max Enthusiasm', 'ar' => 'أقصى حماس']],
            ['title' => ['en' => 'Total Completed Courses', 'ar' => 'إجمالي الدورات المكتملة']],
            ['title' => ['en' => 'Max Of Total Completed Courses In One Topic', 'ar' => 'الحد الأقصى للدورات المكتملة في موضوع واحد']],
            ['title' => ['en' => 'Published Courses', 'ar' => 'الدورات المنشورة']],
            ['title' => ['en' => 'Granted Likes', 'ar' => 'الإعجابات الممنوحة']],
            ['title' => ['en' => 'Acquired Likes', 'ar' => 'الإعجابات المكتسبة']],
            ['title' => ['en' => 'Acquired Views', 'ar' => 'المشاهدات المكتسبة']],
            ['title' => ['en' => 'Num Of Certificates', 'ar' => 'عدد الشهادات']],
            ['title' => ['en' => 'Num Of Badges', 'ar' => 'عدد الشارات']],
            ['title' => ['en' => 'Num Of Bronze Badges', 'ar' => 'عدد الشارات البرونزية']],
            ['title' => ['en' => 'Num Of Silver Badges', 'ar' => 'عدد الشارات الفضية']],
            ['title' => ['en' => 'Num Of Gold Badges', 'ar' => 'عدد الشارات الذهبية']],
        ];

        foreach($statistics as $statistic)
            Statistic::create($statistic);
    }
}
