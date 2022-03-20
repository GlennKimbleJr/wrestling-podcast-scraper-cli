<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewEpisodesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_page_loads()
    {
        $expectedEpisode1 = Episode::factory()->create(['local' => 1, 'program' => 'ARN']);
        $expectedEpisode2 = Episode::factory()->create(['local' => 1, 'program' => 'My World']);
        $this->get(route('index'))
            ->assertStatus(200)
            ->assertViewHas('episodes', function ($episodes)
                use ($expectedEpisode1, $expectedEpisode2) {
                    return $episodes->contains($expectedEpisode1)
                        && $episodes->contains($expectedEpisode2);
                })
            ->assertViewHas('programs', function ($programs) {
                $programs = collect($programs);

                return $programs->contains('ARN')
                    && $programs->contains('My World');
            });
    }

    /** @test */
    public function filter_by_program()
    {
        $expectedEpisode1 = Episode::factory()->create(['program' => 'What Happened When']);
        $expectedEpisode2 = Episode::factory()->create(['program' => 'What Happened When']);
        $unexpectedEpisode = Episode::factory()->create(['program' => 'The Ross Report']);

        $this->get(route('program', ['what-happened-when']))
            ->assertStatus(200)
            ->assertViewHas('episodes', function ($episodes)
                use ($expectedEpisode1, $expectedEpisode2, $unexpectedEpisode) {
                    return $episodes->contains($expectedEpisode1)
                        && $episodes->contains($expectedEpisode2)
                        && ! $episodes->contains($unexpectedEpisode);
                });
    }
}
