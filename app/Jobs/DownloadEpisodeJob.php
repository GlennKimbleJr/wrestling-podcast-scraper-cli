<?php

namespace App\Jobs;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Episode;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DownloadEpisodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $episode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Episode $episode)
    {
        $this->episode = $episode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->episode->local) {
            return;
        }

        $filename = $this->getFileName();
        $path = public_path("mp3s/{$this->episode->program}");
        $this->setupDirectory($path);
        $this->downloadFile($path);
        $this->episode->update([
            'local' => true,
        ]);
    }

    private function setupDirectory($path)
    {
        if(! file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function getFileName()
    {
        return $this->episode->published_at->format('Y-m-d') .  '-' . $this->episode->source_id . '.mp3';
    }

    private function downloadFile($path)
    {
        $stream = Utils::streamFor(
            fopen($path . '/' . $this->getFileName(), 'w')
        );

        $client = new Client;
        $client->request('GET', $this->episode->mp3, ['sink' => $stream]);
    }
}
