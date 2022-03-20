<?php

namespace Database\Factories;

use App\Models\Episode;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class EpisodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Episode::class;

    /**
     * List of potential program options.
     * 
     * @var array
     */
    private $programs = [
        '83 Weeks',
        'My World',
        'What Happened When',
        'Grilling JR',
        'Something to Wrestle',
        'ARN',
        'The Kurt Angle Show',
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'source' => 'megaphone.fm',
            'source_id' => Str::random(8),
            'program' => Arr::random($this->programs),
            'title' => 'Test Title',
            'summary' => 'Test Summary',
            'image' => 'download.jpg',
            'mp3' => 'download.mp3',
            'duration' => '1234',
            'published_at' => now()->subDays(rand(1, 45))->startOfDay(),
            'local' => 1,
        ];
    }
}
