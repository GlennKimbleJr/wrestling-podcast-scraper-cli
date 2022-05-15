<?php

namespace Tests\Feature;

use Exception;
use App\Jobs\DownloadEpisodeJob;
use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DownloadMp3Test extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    /** @test */
    public function a_non_local_episode_will_be_downloaded()
    {
        $nonLocalEpisode = Episode::factory()->create(['local' => 0]);

        $this->artisan('download --sleep=0');

        Queue::assertPushed(function (DownloadEpisodeJob $job) use ($nonLocalEpisode) {
            return $job->episode->is($nonLocalEpisode);
        });
    }

    /** @test */
    public function a_local_episode_will_not_be_downloaded()
    {
        $nonLocalEpisode = Episode::factory()->create(['local' => 1]);

        $this->artisan('download --sleep=0')
            ->expectsOutput('There are no episodes available to download.')
            ->assertExitCode(0);
    }

    /** @test */
    public function multiple_episodes_can_be_downloaded_at_once()
    {
        Episode::factory()->create(['local' => 0]);
        Episode::factory()->create(['local' => 0]);

        $this->artisan('download 2 --sleep=0');

        Queue::assertPushed(DownloadEpisodeJob::class, 2);
    }

    /** @test */
    public function the_ross_report_will_not_be_downloaded()
    {
        Episode::factory()->create(['program' => 'The Ross Report', 'local' => 0]);

        $this->artisan('download --sleep=0')
            ->expectsOutput('There are no episodes available to download.')
            ->assertExitCode(0);
    }

    /** @test */
    public function the_application_will_wait_10_seconds_inbetween_download_attempts_by_default()
    {
        Episode::factory()->create(['local' => 0]);

        $start = microtime(true);
        $this->artisan('download');
        $time = microtime(true) - $start;

        $this->assertEquals(10, floor($time));
    }

    /** @test */
    public function you_can_specify_how_long_to_wait_inbetween_download_attempts()
    {
        Episode::factory()->create(['local' => 0]);

        $start = microtime(true);
        $this->artisan('download --sleep=2');
        $time = microtime(true) - $start;

        $this->assertEquals(2, floor($time));
    }
}
