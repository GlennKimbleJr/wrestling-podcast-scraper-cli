<?php

namespace Tests\Feature;

use Http;
use Carbon\Carbon;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\Models\Episode;
use Tests\Helpers\CreateMegaphoneResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScrapeOhYouDidntKnowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function podcast_can_be_scraped()
    {
        Http::fake(
            CreateMegaphoneResponse::init()
                ->setUrl('https://player.megaphone.fm/playlist/PHL8312998046')
                ->addEpisode()
                ->generate()
        );

        $this->artisan('scrape road-dogg');

        $episode = Episode::first();
        $this->assertEquals(1, Episode::count());
        $this->assertEquals('megaphone.fm', $episode->source);
        $this->assertEquals(1, $episode->source_id);
        $this->assertEquals('Oh You Didnt Know', $episode->program);
        $this->assertEquals('Test Title', $episode->title);
        $this->assertEquals('Test Summary', $episode->summary);
        $this->assertEquals('download.mp3', $episode->mp3);
        $this->assertEquals('download.jpg', $episode->image);
        $this->assertEquals('1234', $episode->duration);
        $this->assertEquals(now()->format('Y-m-d'), $episode->published_at->format('Y-m-d'));
    }
}
