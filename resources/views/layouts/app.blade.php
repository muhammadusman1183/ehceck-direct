<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'PaycronClone') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-white shadow p-4 mb-6">
        <div class="max-w-7xl mx-auto">
            <a href="/" class="text-xl font-bold">{{ config('app.name') }}</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4">
        @yield('content')
    </main>
</body>
</html>
