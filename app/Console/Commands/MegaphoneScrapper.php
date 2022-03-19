<?php

namespace App\Console\Commands;

use Exception;
use Carbon\Carbon;
use App\Models\Episode;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class MegaphoneScrapper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape {program}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download episodes of various podcasts from megaphone.fm';

    /**
     * The number of episodes added while parsing.
     *
     * @var integer
     */
    protected $added = 0;

    /**
     * The GuzzleHttp client.
     *
     * @var Client
     */
    protected $client;


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
        if (! $this->getProgram()) {
            throw new Exception('Invalid program');
        }

        $response = $this->client->get($this->getUrl());

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

    private function getProgram(Carbon $publishedAt = null): ?string
    {
        $program = $this->argument('program');

        if ($program == 'grilling-jr'
            && optional($publishedAt)->lte(Carbon::parse('2019-05-01')->endOfDay())
        ) {
            return 'The Ross Report';
        }

        return Arr::get([
            '83-weeks'      => '83 Weeks',
            'my-world'      => 'My World',
            'whw'           => 'What Happened When',
            'grilling-jr'   => 'Grilling JR',
            'something'     => 'Something to Wrestle',
            'arn'           => 'ARN',
            'kurt-angle'    => 'The Kurt Angle Show',
        ], $program, null);
    }

    private function getUrl()
    {
        return 'https://player.megaphone.fm/playlist/' . Arr::get([
            '83-weeks'      => 'WWO5563730202',
            'my-world'      => 'WWO5330741307',
            'whw'           => 'WWO2089228444',
            'grilling-jr'   => 'WWO8396779805',
            'something'     => 'WWO3531002211',
            'arn'           => 'WWO1389089569',
            'kurt-angle'    => 'WWO7281860247',
        ], $this->argument('program'));
    }

    private function storeEpisode($episode): void
    {
        $publishedAt = Carbon::parse($episode->pubDate);

        $localEpisode = Episode::firstOrCreate([
            'source' => 'megaphone.fm',
            'source_id' => $episode->uid,
        ], [
            'program' => $this->getProgram($publishedAt),
            'title' => $episode->title,
            'summary' => $episode->summary,
            'mp3' => $episode->audioUrl,
            'image' => $episode->imageUrl,
            'duration' => $episode->duration,
            'published_at' => $publishedAt,
        ]);

        if ($localEpisode->wasRecentlyCreated) {
            $this->added++;
        }
    }
}
