<?php

namespace Tests\Feature\Commands;

use Mockery;
use Exception;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\Models\Episode;
use Tests\Helpers\CreateMegaphoneResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScrapeMegaphoneTest extends TestCase
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
    public function a_valid_program_must_be_specified()
    {
        $response = CreateMegaphoneResponse::init()->addEpisode(['uid' => 1])->generate();
        $this->mockClient->shouldReceive('get')->once()->andReturn($response);
        $this->artisan('scrape 83-weeks');
        $this->assertEquals(1, Episode::whereProgram('83 Weeks')->count());

        $response = CreateMegaphoneResponse::init()->addEpisode(['uid' => 2])->generate();
        $this->mockClient->shouldReceive('get')->once()->andReturn($response);
        $this->artisan('scrape my-world');
        $this->assertEquals(1, Episode::whereProgram('My World')->count());

        $response = CreateMegaphoneResponse::init()->addEpisode(['uid' => 3])->generate();
        $this->mockClient->shouldReceive('get')->once()->andReturn($response);
        $this->artisan('scrape whw');
        $this->assertEquals(1, Episode::whereProgram('What Happened When')->count());

        $response = CreateMegaphoneResponse::init()->addEpisode(['uid' => 4])->generate();
        $this->mockClient->shouldReceive('get')->once()->andReturn($response);
        $this->artisan('scrape grilling-jr');
        $this->assertEquals(1, Episode::whereProgram('Grilling JR')->count());

        $response = CreateMegaphoneResponse::init()->addEpisode(['uid' => 5])->generate();
        $this->mockClient->shouldReceive('get')->once()->andReturn($response);
        $this->artisan('scrape something');
        $this->assertEquals(1, Episode::whereProgram('Something to Wrestle')->count());

        $response = CreateMegaphoneResponse::init()->addEpisode(['uid' => 6])->generate();
        $this->mockClient->shouldReceive('get')->once()->andReturn($response);
        $this->artisan('scrape arn');
        $this->assertEquals(1, Episode::whereProgram('ARN')->count());

        $this->mockClient->shouldReceive('get')->never();
        $this->expectException(Exception::class);
        $this->artisan('scrape invalid-program');
    }

    /** @test */
    public function a_200_status_code_must_be_returned()
    {
        $response = CreateMegaphoneResponse::init()
            ->setStatusCode(404)
            ->addEpisode()
            ->generate();

        $this->mockClient
            ->shouldReceive('get')
            ->once()
            ->andReturn($response);

        $this->artisan('scrape 83-weeks');

        $this->assertEquals(0, Episode::count());
    }

    /** @test */
    public function handle_null_summary()
    {
        $response = CreateMegaphoneResponse::init()->addEpisode(['summary' => null])->generate();
        $this->mockClient->shouldReceive('get')->once()->andReturn($response);
        $this->artisan('scrape something');
        $this->assertEquals(1, Episode::whereProgram('Something to Wrestle')->count());
        $this->assertEquals('', Episode::whereProgram('Something to Wrestle')->first()->summary);
    }
}
