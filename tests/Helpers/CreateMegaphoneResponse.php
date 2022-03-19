<?php

namespace Tests\Helpers;

use GuzzleHttp\Psr7\Response;


class CreateMegaphoneResponse
{
    private $statusCode = 200;
    private $nextUrl = null;
    private $episodes = [];

    public static function init()
    {
        return new self;
    }

    public function generate()
    {
        return new Response($this->statusCode, [], json_encode([
            'episodes' => $this->episodes,
        ]));
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;

        return $this;
    }

    public function setNextUrl(?string $url): self
    {
        $this->nextUrl = $url;

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
