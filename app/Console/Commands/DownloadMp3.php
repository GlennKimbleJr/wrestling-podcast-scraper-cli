<?php

namespace App\Console\Commands;

use Exception;
use Carbon\Carbon;
use App\Models\Episode;
use Illuminate\Console\Command;
use App\Jobs\DownloadEpisodeJob;
use Illuminate\Support\Facades\App;

class DownloadMp3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download {amount=1} {--sleep=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download a random episode.';

    /**
     * A list of episode id's that failed to download.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $amount = (int) $this->argument('amount');
        $count = 1;

        while ($count <= $amount) {
            // add a blank line in-between iterations.
            if ($count > 1) {
                $this->newLine();
            }

            $episode = $this->getNextEpisode();

            if (! $episode) {
                $this->warn('There are no episodes available to download.');

                return 0;
            }

            $this->printDetails($episode);
            $this->download($episode);
            $this->info("{$count} of {$amount} Complete");

            // The number of seconds to wait in-between download attempts.
            if ($sleepSeconds = (int) $this->option('sleep')) {
                sleep($sleepSeconds);
            }

            $count++;
        }

        return 0;
    }

    /**
     * Attempt to retrieve the next episode to be downloaded.
     *
     * @return Episode|null
     */
    private function getNextEpisode(): ?Episode
    {
        return Episode::query()
            ->whereLocal(0)
            ->whereNotIn('id', $this->errors)
            ->whereNotIn('program', ['The Ross Report'])
            ->inRandomOrder()
            ->first();
    }

    /**
     * Print episode details to the console.
     *
     * @param Episode $episode
     *
     * @return void
     */
    private function printDetails(Episode $episode): void
    {
        $this->info(
            $episode->published_at->format('Y-m-d') . ' - ' . $episode->program
        );

        $this->info($episode->title);
    }

    /**
     * Attempt to download the episode.
     *
     * @param Episode $episode
     *
     * @return void
     */
    private function download(Episode $episode): void
    {
        try {
            DownloadEpisodeJob::dispatch($episode);
        } catch (Exception $e) {
            $this->errors[] = $episode->id;
            $this->error('Error while downloading.');
        }
    }
}
