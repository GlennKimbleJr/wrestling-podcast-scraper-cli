<?php

namespace App\Console\Commands;

use Str;
use Http;
use Storage;
use Exception;
use App\Models\Episode;
use Illuminate\Console\Command;
use Spatie\TemporaryDirectory\TemporaryDirectory;

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
            $this->sleep($count, $amount);

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
            $temporaryDirectory = (new TemporaryDirectory)->create();
            $temporaryMp3Path = $temporaryDirectory->path('wrestling/temp.mp3');

            Http::sink($temporaryMp3Path)->get($episode->mp3);

            Storage::put(
                (string) Str::of($episode->local_mp3_path)->replaceFirst('/storage/', '/public/'),
                file_get_contents($temporaryMp3Path)
            );

            $temporaryDirectory->delete();

            $episode->update(['local' => true]);
        } catch (Exception $e) {
            $this->errors[] = $episode->id;
            $this->error('Error while downloading.');
        }
    }

    /**
     * Wait in-betwen download attempts.
     *
     * @param int $iterationCount
     * @param int $interationAmount
     *
     * @return void
     */
    private function sleep(int $iterationCount, int $interationAmount): void
    {
        $numberOfSecondsToWait = (int) $this->option('sleep');

        if (! $numberOfSecondsToWait) {
            return;
        }

        // We don't need to wait after the final iteration.
        if ($iterationCount == $interationAmount) {
            return;
        }

        sleep($numberOfSecondsToWait);
    }
}
