<html>
    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    </head>
    <body class="bg-dark">
        <div class="m-2">
            <a href="{{ route('index') }}" class="btn btn-sm {{ isset($selectedProgram) ? 'btn-dark' : 'btn-primary' }}">Home</a>
            @foreach ($programs as $slug => $program)
                <a href="{{ route('program', [$slug]) }}" class="btn btn-sm {{ (isset($selectedProgram) && $selectedProgram == $slug) ? 'btn-primary' : 'btn-dark' }}">{{ $program }}</a>
            @endforeach
        </div>

        <div class="row m-2 bg-light p-4 rounded-lg">
            <div class="col-12">
                <div class="row">
                    <div class="col-6">
                        Total Episodes: {{ $episodes->count() }}
                    </div>
                    <div class="col-6">
                    </div>
                </div>

                <table class="mt-3 table table-striped table-hover w-auto small">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col" class="w-25">Program</th>
                      <th scope="col" class="w-25">Published</th>
                      <th scope="col">Title</th>
                      <th scope="col"></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($episodes as $episode)
                    <tr>
                        <td>
                            <a href="{{ route('program', [$episode->program_slug]) }}">
                                {{ $episode->program }}
                            </a>
                        </td>
                        <td>{{ $episode->published_at->format('Y-m-d') }}</td>
                        <td>{{ $episode->title }}</td>
                        <td class="text-right">
                          <a
                            href="{{ route('episode', $episode) }}"
                            target="_blank"
                            class="btn btn-dark"
                          >
                            Listen
                          </a>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    </body>
</html>
