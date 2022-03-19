<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Episode;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class Scraper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:83-weeks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download episodes of the 83 Weeks podcast from Megaphone.fm';

    /**
     * The GuzzleHttp client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The number of episodes added while parsing.
     *
     * @var integer
     */
    protected $added = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = 'https://player.megaphone.fm/playlist/WWO5563730202';

        $response = $this->client->get($url);

        if ($response->getStatusCode() !== 200) {
            return 0;
        }

        $contents = json_decode(
            $response->getBody()->getContents()
        );


        foreach ($contents->episodes as $episode) {
            $this->storeEpisode($episode);
        }

        $this->info(
            "Finished with {$this->added} " . Str::plural('episode', $this->added) . '.'
        );

        return 0;
    }

    private function storeEpisode($episode): void
    {
        $localEpisode = Episode::firstOrCreate([
            'source' => 'megaphone.fm',
            'source_id' => $episode->uid,
        ], [
            'program' => '83 Weeks',
            'title' => $episode->title,
            'summary' => $episode->summary,
            'mp3' => $episode->audioUrl,
            'image' => $episode->imageUrl,
            'duration' => $episode->duration,
            'published_at' => Carbon::parse($episode->pubDate),
        ]);

        if ($localEpisode->wasRecentlyCreated) {
            $this->added++;
        }
    }
}
