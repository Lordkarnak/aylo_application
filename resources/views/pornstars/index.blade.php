<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <!-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        .content {
            padding: 2rem;
            text-align: center;
        }
        .footer {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        a {
            color: #3490dc;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pornhub feed application</h1>
    </div>

    @if (!empty($pornstars))
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Thumbnail</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pornstars as $pornstar)
            <tr>
                <td>{{ $pornstar->name }}</td>
                <td>
                    @if (!empty($pornstar->cached_thumbnail))
                    <img width="70" height="auto" src="data:image/jpeg;base64,{{ $pornstar->cached_thumbnail }}">
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $pornstars->links() }}
    @endif

    <div class="footer">
        <p>&copy; {{ date('Y') }} Spyridopoulos Application. All rights reserved.</p>
    </div>
</body>
</html>