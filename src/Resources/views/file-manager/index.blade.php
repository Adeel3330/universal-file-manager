<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Universal File Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
    <script>
        // Handle Session/Page Expiration (419)
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({
                fail
            }) => {
                fail(({
                    status,
                    preventDefault
                }) => {
                    if (status === 419) {
                        alert('Your session has expired. The page will now refresh.');
                        window.location.reload();
                        preventDefault();
                    }
                });
            });
        });
    </script>
</head>

<body class="bg-gray-50">
    <livewire:ufm-file-manager />

    @livewireScripts
</body>

</html>