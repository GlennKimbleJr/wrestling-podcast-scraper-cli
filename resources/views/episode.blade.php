<html>
    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    </head>
    <body class="bg-dark">
        <div class="row m-2">
            <div class='col-md-6 bg-light p-4 rounded-lg'>
                <h2>{{ $episode->title }}</h2>
                <audio controls oncanplay="audioLoaded('audioPlayer')" id="audioPlayer">
                    <source src="{{ $episode->local_mp3_path }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>

                <p class="mt-4 small">
                    {{ $episode->published_at->format('F d, Y') }} &bullet; {{ gmdate("H:i:s", (int) $episode->duration) }}
                </p>

                <p>{!! $episode->summary ?? 'Description missing' !!}</p>
            </div>
            <div class='col-md-6 p-4 rounded-lg'>
                <img src="{{ $episode->image }}" alt="{{ $episode->program }}" class="img-fluid">
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>
