<?php

namespace Tests\Feature;

use Http;
use Carbon\Carbon;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\Models\Episode;
use Tests\Helpers\CreateMegaphoneResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScrapeToBeTheManTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function podcast_can_be_scraped()
    {
        Http::fake(
            CreateMegaphoneResponse::init()
                ->setUrl('https://player.megaphone.fm/playlist/PHL6641370953')
                ->addEpisode()
                ->generate()
        );

        $this->artisan('scrape flair');

        $episode = Episode::first();
        $this->assertEquals(1, Episode::count());
        $this->assertEquals('megaphone.fm', $episode->source);
        $this->assertEquals(1, $episode->source_id);
        $this->assertEquals('To Be The Man', $episode->program);
        $this->assertEquals('Test Title', $episode->title);
        $this->assertEquals('Test Summary', $episode->summary);
        $this->assertEquals('download.mp3', $episode->mp3);
        $this->assertEquals('download.jpg', $episode->image);
        $this->assertEquals('1234', $episode->duration);
        $this->assertEquals(now()->format('Y-m-d'), $episode->published_at->format('Y-m-d'));
    }

    /** @test */
    public function early_episodes_program_title_are_wooooo_nation_uncensored()
    {
        Http::fake(
            CreateMegaphoneResponse::init()
                ->addEpisode([
                    'pubDate' => Carbon::parse('2022-04-20')->toIso8601String(),
                ])
                ->generate()
        );

        $this->artisan('scrape flair');

        $this->assertEquals('WOOOOO Nation Uncensored', Episode::first()->program);
    }

    /** @test */
    public function later_episodes_program_title_are_to_be_the_man()
    {
        Http::fake(
            CreateMegaphoneResponse::init()
                ->addEpisode([
                    'pubDate' => Carbon::parse('2022-04-21')->toIso8601String(),
                ])
                ->generate()
        );

        $this->artisan('scrape flair');

        $this->assertEquals('To Be The Man', Episode::first()->program);
    }
}
