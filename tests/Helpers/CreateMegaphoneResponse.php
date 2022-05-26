<?php

namespace Tests\Helpers;

use Http;

class CreateMegaphoneResponse
{
    private $statusCode = 200;
    private $url = '*';
    private $episodes = [];

    public static function init()
    {
        return new self;
    }

    public function generate()
    {
        $response = [
            $this->url => Http::response([
                    'episodes' => $this->episodes,
                ],
                $this->statusCode,
            ),
        ];

        /**
         * The default $url property is a catch-all "*". So if we are overriding that to
         * a specific url then we want to add a new catch all to any other url
         * specified to return a 404 response.
         */
        if ($this->url !== '*') {
            $response['*'] = Http::response('Not Found', 404);
        }

        return $response;
    }

    public function generateForSequence()
    {
        return ['episodes' => $this->episodes];
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;

        return $this;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function addEpisode(array $episodeDetails = []): self
    {
        $this->episodes[] = array_merge([
            'title' => 'Test Title',
            'summary' => 'Test Summary',
            'uid' => count($this->episodes) + 1,
            'audioUrl' => 'download.mp3',
            'imageUrl' => 'download.jpg',
            'duration' => '1234',
            'pubDate' => now()->toIso8601String(),
        ], $episodeDetails);

        return $this;
    }
}
