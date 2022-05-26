<?php

namespace App\Models;

use Storage;
use Illuminate\Support\Str;
use Database\Factories\EpisodeFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Episode extends Model
{
    use HasFactory;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'program_slug',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'published_at',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return EpisodeFactory::new();
    }

    public function scopeCanStreamLocally($query)
    {
        return $query->whereLocal(1)->whereNotNull('mp3');
    }

    public function scopeGetProgramsList($query)
    {
        return $query
            ->distinct('program')
            ->orderBy('program')
            ->get()
            ->pluck('program', 'program_slug')
            ->toArray();
    }

    public function getLocalMp3PathAttribute()
    {
        return Storage::url(
            "/mp3s/{$this->program}/"
            . $this->published_at->format('Y-m-d')
            . "-{$this->source_id}.mp3"
        );
    }

    public function getProgramSlugAttribute()
    {
        return (string) Str::of($this->program)->slug('-');
    }
}
