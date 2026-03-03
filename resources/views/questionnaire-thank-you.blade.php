<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank You — {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md text-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <div class="text-6xl mb-4 text-green-600">✓</div>
                <h1 class="text-2xl font-semibold mb-2">Thank You</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Your questionnaire has been submitted successfully. The requesting organization will review your responses and may contact you if they have any follow-up questions.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
