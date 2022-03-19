<?php

namespace Tests\Feature;

use Mockery;
use Carbon\Carbon;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\Models\Episode;
use Tests\Helpers\CreateMegaphoneResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScrapeGrillingJRTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Client
     */
    protected $mockClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $this->mockClient);
    }

    /** @test */
    public function podcast_can_be_scraped()
    {
        $response = CreateMegaphoneResponse::init()
            ->addEpisode()
            ->generate();

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->with('https://player.megaphone.fm/playlist/WWO8396779805')
            ->andReturn($response);

        $this->artisan('scrape grilling-jr');

        $episode = Episode::first();
        $this->assertEquals(1, Episode::count());
        $this->assertEquals('megaphone.fm', $episode->source);
        $this->assertEquals(1, $episode->source_id);
        $this->assertEquals('Grilling JR', $episode->program);
        $this->assertEquals('Test Title', $episode->title);
        $this->assertEquals('Test Summary', $episode->summary);
        $this->assertEquals('download.mp3', $episode->mp3);
        $this->assertEquals('download.jpg', $episode->image);
        $this->assertEquals('1234', $episode->duration);
        $this->assertEquals(now()->format('Y-m-d'), $episode->published_at->format('Y-m-d'));
    }

    /** @test */
    public function early_episodes_program_title_are_the_ross_report()
    {
        $response = CreateMegaphoneResponse::init()->addEpisode([
        	'pubDate' => Carbon::parse('2019-05-01')->toIso8601String(),
        ])->generate();

        $this->mockClient->shouldReceive('get')->once()->andReturn($response);

        $this->artisan('scrape grilling-jr');

        $this->assertEquals('The Ross Report', Episode::first()->program);
    }

    /** @test */
    public function later_episodes_program_title_are_grilling_jr()
    {
        $response = CreateMegaphoneResponse::init()->addEpisode([
        	'pubDate' => Carbon::parse('2019-05-02')->toIso8601String(),
        ])->generate();

        $this->mockClient->shouldReceive('get')->once()->andReturn($response);

        $this->artisan('scrape grilling-jr');

        $this->assertEquals('Grilling JR', Episode::first()->program);
    }
}
