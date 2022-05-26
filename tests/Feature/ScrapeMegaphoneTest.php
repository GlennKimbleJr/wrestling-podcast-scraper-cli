<?php

namespace Tests\Feature\Commands;

use Http;
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
     * @test
     * @dataProvider programListProvider
     */
    public function a_valid_program_must_be_specified($program, $programTitle)
    {
        Http::fake(
            CreateMegaphoneResponse::init()->addEpisode()->generate()
        );

        $this->artisan("scrape {$program}");

        $this->assertEquals(1, Episode::whereProgram($programTitle)->count());
    }

    /**
     * Data provider for a_valid_program_must_be_specified test.
     *
     * @return \string[][]
     */
    public function programListProvider()
    {
        return [
            ['83-weeks', '83 Weeks'],
            ['my-world', 'My World'],
            ['whw', 'What Happened When'],
            ['grilling-jr', 'Grilling JR'],
            ['something', 'Something to Wrestle'],
            ['arn', 'ARN'],
            ['kurt-angle', 'The Kurt Angle Show'],
            ['flair', 'To Be The Man'],
            ['road-dogg', 'Oh You Didnt Know'],
            ['hardy', 'The Extreme Life of Matt Hardy'],
            ['ddp', 'DDP Snake Pit'],
            ['foley', 'Foley Is Pod'],
            ['regal', 'Gentleman Villain'],
        ];
    }

    /** @test */
    public function an_invalid_program_may_not_be_specified()
    {
        Http::fake();

        $this->artisan('scrape invalid-program')
            ->expectsOutput('Invalid program')
            ->assertExitCode(1);

        Http::assertNothingSent();
    }

    /** @test */
    public function a_200_status_code_must_be_returned()
    {
        Http::fakeSequence()->pushStatus(404);

        $this->artisan('scrape 83-weeks')
            ->expectsOutput('Error retrieving rss feed.')
            ->assertExitCode(1);

        $this->assertEquals(0, Episode::count());
    }

    /** @test */
    public function passing_in_all_in_place_of_a_program_will_scrape_all_programs()
    {
        $response = CreateMegaphoneResponse::init()->addEpisode()->generateForSequence();

        Http::fakeSequence()
            ->push($response, 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200)
            ->push($this->incrementUid($response), 200);

        $this->artisan('scrape all')
            ->expectsOutput('83 Weeks')
            ->expectsOutput('My World')
            ->expectsOutput('What Happened When')
            ->expectsOutput('Grilling JR')
            ->expectsOutput('Something to Wrestle')
            ->expectsOutput('ARN')
            ->expectsOutput('The Kurt Angle Show')
            ->expectsOutput('To Be The Man')
            ->expectsOutput('Oh You Didnt Know')
            ->expectsOutput('The Extreme Life of Matt Hardy')
            ->expectsOutput('DDP Snake Pit')
            ->expectsOutput('Foley Is Pod')
            ->expectsOutput('Gentleman Villain')
            ->assertExitCode(0);

        // Prooves that 1 episode was scrapped for each podcast.
        $this->assertEquals(13, Episode::count());
    }

    /**
     * Increment the UUID in the array so we can reuse it for different podcasts without conflicts.
     *
     * @param $array array
     * @return array
     */
    private function incrementUid(array &$array): array
    {
        $array['episodes'][0]['uid']++;

        return $array;
    }
}
