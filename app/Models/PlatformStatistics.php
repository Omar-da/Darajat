<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformStatistics extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'num_of_courses',
        'num_of_students',
        'num_of_teachers',
        'num_of_countries',
        'num_of_topics',
        'num_of_views',
        'commission',
        'total_profit'
    ];


    public static function getStats(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'num_of_courses' => 0,
            'num_of_students' => 0,
            'num_of_teachers' => 0,
            'num_of_countries' => 0,
            'num_of_topics' => 0,
            'num_of_views' => 0,
            'commission' => 0,
            'total_profit' => 0,
        ]);
    }

    public static function incrementStat($stat)
    {
        self::getStats()->increment($stat);
    }
}
