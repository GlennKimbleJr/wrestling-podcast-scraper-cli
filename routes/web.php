<?php

use App\Models\Episode;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index', [
        'programs' => Episode::getProgramsList(),
        'episodes' => Episode::canStreamLocally()
            ->orderByDesc('published_at')
            ->get(),
    ]);
})->name('index');

Route::get('/programs/{program}', function (Request $request, string $program) {
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
})->name('program');

Route::get('/episodes/{episode}', function(Request $request, Episode $episode) {
    return view('episode', [
        'episode' => $episode,
    ]);
})->name('episode');
