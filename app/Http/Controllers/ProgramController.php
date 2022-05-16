<?php

namespace App\Http\Controllers;

use Str;
use App\Models\Episode;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  string  $program
     * @return \Illuminate\Http\Response
     */
    public function show(string $program)
    {
        return view('index', [
            'selectedProgram' => $program,
            'programs' => Episode::getProgramsList(),
            'episodes' => Episode::canStreamLocally()
                ->whereProgram(
                    Str::of($program)->replace('-', ' ')->title()
                )
                ->orderByDesc('published_at')
                ->get(),
        ]);
    }
}
