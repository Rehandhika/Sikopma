<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>React Test</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/react/main.jsx'])
</head>
<body class="bg-background text-foreground p-8">
    <h1 class="text-2xl font-bold mb-4">React Test Page</h1>
    <p class="mb-4">If you see this, the Blade template is working.</p>
    
    <script type="application/json" id="public-initial-data">{"banners":[],"categories":[],"products":{"current_page":1,"data":[],"last_page":1}}</script>
    <div id="react-public" data-page="home"></div>
    
    <script>
        console.log('Inline script executed');
        window.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            var root = document.getElementById('react-public');
            console.log('Root element:', root);
            console.log('Root innerHTML before React:', root.innerHTML);
            
            // Check after 2 seconds
            setTimeout(function() {
                console.log('Root innerHTML after 2s:', root.innerHTML);
                if (root.innerHTML === '') {
                    document.body.innerHTML += '<div style="background:yellow;padding:20px;margin-top:20px;"><strong>React did not render!</strong> Check console for errors.</div>';
                }
            }, 2000);
        });
    </script>
</body>
</html>
