<?php

namespace App\Http\Controllers;

use Str;
use App\Models\Episode;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $episodes = Episode::query()
            ->canStreamLocally()
            ->when($request->has('program'), function ($query) use ($request) {
                $query->whereProgram(
                    Str::of($request->get('program'))->replace('-', ' ')->title()
                );
            })
            ->orderByDesc('published_at')
            ->get();

        abort_if($episodes->isEmpty(), 404);

        return view('index', [
            'programs' => Episode::getProgramsList(),
            'episodes' => $episodes,
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
