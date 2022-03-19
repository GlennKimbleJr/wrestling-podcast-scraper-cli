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

        $this->artisan('download');

        Queue::assertPushed(function (DownloadEpisodeJob $job) use ($nonLocalEpisode) {
            return $job->episode->is($nonLocalEpisode);
        });
    }

    /** @test */
    public function a_local_episode_will_not_be_downloaded()
    {
        $nonLocalEpisode = Episode::factory()->create(['local' => 1]);

        $this->expectException(Exception::class);

        $this->artisan('download');
    }

    /** @test */
    public function multiple_episodes_can_be_downloaded_at_once()
    {
        Episode::factory()->create(['local' => 0]);
        Episode::factory()->create(['local' => 0]);

        $this->artisan('download 2');

        Queue::assertPushed(DownloadEpisodeJob::class, 2);
    }
}
