<?php

namespace Tests\Feature;

use Mockery;
use Carbon\Carbon;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\Models\Episode;
use Tests\Helpers\CreateMegaphoneResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScrapeSomethingToWrestleTest extends TestCase
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
            ->with('https://player.megaphone.fm/playlist/WWO3531002211')
            ->andReturn($response);

        $this->artisan('scrape something');

        $episode = Episode::first();
        $this->assertEquals(1, Episode::count());
        $this->assertEquals('megaphone.fm', $episode->source);
        $this->assertEquals(1, $episode->source_id);
        $this->assertEquals('Something to Wrestle', $episode->program);
        $this->assertEquals('Test Title', $episode->title);
        $this->assertEquals('Test Summary', $episode->summary);
        $this->assertEquals('download.mp3', $episode->mp3);
        $this->assertEquals('download.jpg', $episode->image);
        $this->assertEquals('1234', $episode->duration);
        $this->assertEquals(now()->format('Y-m-d'), $episode->published_at->format('Y-m-d'));
    }
}
