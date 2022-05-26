<?php

namespace Tests\Feature;

use Str;
use Http;
use Storage;
use Exception;
use App\Jobs\DownloadEpisodeJob;
use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadMp3Test extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        Http::fake(Http::response('Fake response for the mp3.', 200));
    }

    /**
     * @test
     * @group slow
     */
    public function the_application_will_wait_10_seconds_inbetween_download_attempts_by_default()
    {
        Episode::factory()->create(['local' => 0]);
        Episode::factory()->create(['local' => 0]);

        $start = microtime(true);
        $this->artisan('download 2');
        $time = microtime(true) - $start;

        $this->assertEquals(10, floor($time));
    }

    /**
     * @test
     * @group slow
     */
    public function you_can_specify_how_long_to_wait_inbetween_download_attempts()
    {
        Episode::factory()->create(['local' => 0]);
        Episode::factory()->create(['local' => 0]);

        $start = microtime(true);
        $this->artisan('download 2 --sleep=2');
        $time = microtime(true) - $start;

        $this->assertEquals(2, floor($time));
    }

    /** @test */
    public function a_non_local_episode_will_be_downloaded()
    {
        $nonLocalEpisode = Episode::factory()->create(['local' => 0]);

        $this->artisan('download --sleep=0');

        Storage::assertExists(
            (string) Str::of($nonLocalEpisode->local_mp3_path)->replaceFirst('/storage/', '/public/'),
        );
    }

    /** @test */
    public function a_local_episode_will_not_be_downloaded()
    {
        $nonLocalEpisode = Episode::factory()->create(['local' => 1]);

        $this->artisan('download --sleep=0')
            ->expectsOutput('There are no episodes available to download.')
            ->assertExitCode(0);

        Storage::assertMissing(
            (string) Str::of($nonLocalEpisode->local_mp3_path)->replaceFirst('/storage/', '/public/'),
        );
    }

    /** @test */
    public function multiple_episodes_can_be_downloaded_at_once()
    {
        $episode1 = Episode::factory()->create(['local' => 0]);
        $episode2 = Episode::factory()->create(['local' => 0]);

        $this->artisan('download 2 --sleep=0');

        Storage::assertExists(
            (string) Str::of($episode1->local_mp3_path)->replaceFirst('/storage/', '/public/'),
        );

        Storage::assertExists(
            (string) Str::of($episode2->local_mp3_path)->replaceFirst('/storage/', '/public/'),
        );
    }

    /** @test */
    public function the_ross_report_will_not_be_downloaded()
    {
        $episode = Episode::factory()->create(['program' => 'The Ross Report', 'local' => 0]);

        $this->artisan('download --sleep=0')
            ->expectsOutput('There are no episodes available to download.')
            ->assertExitCode(0);

        Storage::assertMissing(
            (string) Str::of($episode->local_mp3_path)->replaceFirst('/storage/', '/public/'),
        );
    }
}
