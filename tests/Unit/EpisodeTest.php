<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EpisodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function slugified_program_property()
    {
        Episode::factory()->create(['program' => '83 Weeks']);

        $episode = Episode::first();

        $this->assertEquals('83-weeks', $episode->program_slug);
    }

    /** @test */
    public function list_of_programs()
    {
        Episode::factory()->create(['program' => '83 Weeks']);
        Episode::factory()->create(['program' => 'My World']);
        Episode::factory()->create(['program' => 'What Happened When']);
        Episode::factory()->create(['program' => 'Grilling JR']);
        Episode::factory()->create(['program' => 'The Ross Report']);
        Episode::factory()->create(['program' => 'Something to Wrestle']);
        Episode::factory()->create(['program' => 'ARN']);
        Episode::factory()->create(['program' => 'The Kurt Angle Show']);

        // create a few duplicates just to proove I end up with a unique list
        Episode::factory()->create(['program' => '83 Weeks']);
        Episode::factory()->create(['program' => '83 Weeks']);
        Episode::factory()->create(['program' => 'What Happened When']);
        Episode::factory()->create(['program' => 'What Happened When']);
        Episode::factory()->create(['program' => 'ARN']);
        Episode::factory()->create(['program' => 'ARN']);

        $programs = Episode::getProgramsList();

        $this->assertEquals([
            '83-weeks' => '83 Weeks',
            'my-world' => 'My World',
            'what-happened-when' => 'What Happened When',
            'grilling-jr' => 'Grilling JR',
            'the-ross-report' => 'The Ross Report',
            'something-to-wrestle' => 'Something to Wrestle',
            'arn' => 'ARN',
            'the-kurt-angle-show' => 'The Kurt Angle Show',
        ], $programs);
    }

    /** @test */
    public function local_mp3_path_property()
    {
        $episode = Episode::factory()->create([
            'source_id' => 'k123',
            'program' => '83 Weeks',
            'published_at' => Carbon::parse('December 26, 2012'),
        ]);

        $expectedPath = "mp3s/83 Weeks/2012-12-26-k123.mp3";

        $this->assertEquals($expectedPath, $episode->local_mp3_path);
    }

    /** @test */
    public function list_of_episodes_that_can_stream_locally()
    {
        $local = Episode::factory()->create(['local' => 1, 'mp3' => 'test.mp3']);
        $notLocal = Episode::factory()->create(['local' => 0, 'mp3' => null]);
        $notLocal2 = Episode::factory()->create(['local' => 1, 'mp3' => null]);
        $notLocal3 = Episode::factory()->create(['local' => 0, 'mp3' => 'test.mp3']);
        
        $localEpisodes = Episode::canStreamLocally()->get();

        $this->assertTrue($localEpisodes->contains($local));
        $this->assertFalse($localEpisodes->contains($notLocal));
        $this->assertFalse($localEpisodes->contains($notLocal2));
        $this->assertFalse($localEpisodes->contains($notLocal3));
    }
}
