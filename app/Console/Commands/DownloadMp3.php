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
    protected $signature = 'download {amount=1}';

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
        $amount = (int) $this->argument('amount') ?? 1;
        if ($amount == 0) {
            $amount = 1;
        }

        $count = 1;
        while ($count <= $amount) {
            if ($count > 1) {
                $this->info('');
            }

            $episode = Episode::query()
                ->whereLocal(0)
                ->whereNotIn('id', $this->errors)
                ->whereNotIn('program', ['The Ross Report'])
                ->inRandomOrder()
                ->first();

            if (! $episode) {
                $this->warn('There are no episodes available to download.');

                return 1;
            }

            $this->info(
                $episode->published_at->format('Y-m-d') . ' - ' . $episode->program
            );

            $this->info($episode->title);

            try {
                DownloadEpisodeJob::dispatch($episode);
            } catch (Exception $e) {
                $this->errors[] = $episode->id;
                $this->error('Error while downloading.');
            }

            $this->info("{$count} of {$amount} Complete");
            $count++;

            if (! App::runningUnitTests()) {
                sleep(10);
            }
        }

        return 0;
    }
}
