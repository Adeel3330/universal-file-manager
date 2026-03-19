<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="ufm-api-base" content="{{ url(config('ufm.route_prefix', 'file-manager') . '/api') }}">
    <title>Universal File Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <div id="ufm-app"></div>

    @if($stack === 'vue')
    {{--
        IMPORTANT: Register this file in your vite.config.js:
        input: ['resources/js/app.js', 'resources/js/components/ufm/ufm.js']
    --}}
    {{-- @vite(['resources/js/components/ufm/ufm.js']) --}}
    <script>
        // If using Vite, uncomment the Vite directive above
        // and remove this script block.
        console.info(
            'Universal File Manager (Vue): The app is ready to mount to #ufm-app.\n' +
            'Uncomment the Vite directive above to load resources/js/components/ufm/ufm.js\n' +
            'API Base URL is available via: document.querySelector(\'meta[name="ufm-api-base"]\').content'
        );
    </script>
    @elseif($stack === 'react')
    {{--
        IMPORTANT: Register this file in your vite.config.js:
        input: ['resources/js/app.js', 'resources/js/components/ufm/ufm.jsx']
    --}}
    {{-- @vite(['resources/js/components/ufm/ufm.jsx']) --}}
    <script>
        console.info(
            'Universal File Manager (React): The app is ready to mount to #ufm-app.\n' +
            'Uncomment the Vite directive above to load resources/js/components/ufm/ufm.jsx\n' +
            'API Base URL is available via: document.querySelector(\'meta[name="ufm-api-base"]\').content'
        );
    </script>
    @endif
</body>

</html>