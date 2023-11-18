<html>
<head>
    @if($isDevMode)
        <script type="module" src="{{ $devServerConfig['url'] }}/@@vite/client"></script>
        <script type="module" src="{{ $devServerConfig['url'] }}/src/main.js"></script>
    @else
        {{-- Production SPA build --}}
        <link rel="stylesheet" href="/vendor/task-manager-interface/assets/main.css">
        <script type="module" src="/vendor/task-manager-interface/assets/main.js"
                defer></script>
    @endif
</head>
<body>
<div id="app"></div>
</body>
</html>
