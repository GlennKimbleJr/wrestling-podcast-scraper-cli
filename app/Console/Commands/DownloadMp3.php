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
     * @var array
     */
    protected $errors = [];


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = 1;
        $amount = (int) $this->argument('amount') ?? 1;
        if ($amount == 0) {
            $amount = 1;
        }

        while ($count <= $amount) {
            if ($count > 1) {
                $this->info('');
            }

            $this->download($count, $amount);

            $count++;

            if (! App::runningUnitTests()) {
                sleep(10);
            }
        }

        return 0;
    }

    private function download($count, $amount)
    {
        try {
            $episode = Episode::query()
                ->whereLocal(0)
                ->whereNotIn('id', $this->errors)
                ->inRandomOrder()
                ->firstOrFail();

            $this->info(
                $episode->published_at->format('Y-m-d') . ' - ' . $episode->program
            );

            $this->info($episode->title);

            DownloadEpisodeJob::dispatch($episode);

            $this->warn("{$count} of {$amount} Complete");
        } catch (Exception $e) {
            $this->errors[] = $episode->id;
            $this->error('Error while downloading.');
        }
    }
}
