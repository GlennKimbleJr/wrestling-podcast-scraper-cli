<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('index', [
            'programs' => Episode::getProgramsList(),
            'episodes' => Episode::canStreamLocally()
                ->orderByDesc('published_at')
                ->get(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Episode  $episode
     * @return \Illuminate\Http\Response
     */
    public function show(Episode $episode)
    {
        return view('episode', [
            'episode' => $episode,
        ]);
    }
}
