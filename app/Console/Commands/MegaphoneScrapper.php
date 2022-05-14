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
     * A list of valid programs we can scrape with their ids and titles.
     *
     * @var array
     */
    protected $programs = [
        '83-weeks' => [
            'id' => 'WWO5563730202',
            'title' => '83 Weeks',
        ],
        'my-world' => [
            'id' => 'WWO5330741307',
            'title' => 'My World',
        ],
        'whw' => [
            'id' => 'WWO2089228444',
            'title' => 'What Happened When',
        ],
        'grilling-jr' => [
            'id' => 'WWO8396779805',
            'title' => 'Grilling JR',
        ],
        'something' => [
            'id' => 'WWO3531002211',
            'title' => 'Something to Wrestle',
        ],
        'arn' => [
            'id' => 'WWO1389089569',
            'title' => 'ARN',
        ],
        'kurt-angle' => [
            'id' => 'WWO7281860247',
            'title' => 'The Kurt Angle Show',
        ],
    ];

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
        if (! $this->isValidProgram()) {
            $this->error('Invalid program');

            return 1;
        }

        $response = $this->client->get($this->getUrl());

        if ($response->getStatusCode() !== 200) {
            $this->error('Error retrieving rss feed.');

            return 1;
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

    /**
     * Validates that the provided program argument is safe to use.
     *
     * @return bool
     */
    private function isValidProgram(): bool
    {
        return (bool) Arr::get($this->programs, $this->argument('program'));
    }

    /**
     * Return the appropriate podcast url for the provided program argument.
     *
     * @return string
     */
    private function getUrl(): string
    {
        return 'https://player.megaphone.fm/playlist/'
            . Arr::get($this->programs, "{$this->argument('program')}.id");
    }

    /**
     * Save the given episode to the database if it doesn't already exist.
     */
    private function storeEpisode($episode): void
    {
        $publishedAt = Carbon::parse($episode->pubDate);

        $localEpisode = Episode::firstOrCreate([
            'source' => 'megaphone.fm',
            'source_id' => $episode->uid,
        ], [
            'program' => $this->getProgramTitle($publishedAt),
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

    /**
     * Get the appropriate program title based on the program argument provided.
     *
     * @var Carbon $publishedAt
     *
     * @return string
     */
    private function getProgramTitle(Carbon $publishedAt): string
    {
        $program = $this->argument('program');

        if ($program == 'grilling-jr' && $publishedAt->lte($this->getRossReportCutoffDate())) {
            return 'The Ross Report';
        }

        return (string) Arr::get($this->programs, "{$program}.title");
    }

    /**
     * Get the cutoff date for when The Ross Report becomes Grilling Jr.
     *
     * @return Carbon
     */
    private function getRossReportCutoffDate(): Carbon
    {
        return Carbon::parse('2019-05-01')->endOfDay();
    }
}
