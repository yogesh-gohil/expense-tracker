<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>BudgetBeacon</title>

        <script>
            (() => {
                const key = 'budgetbeacon-theme';
                const savedTheme = localStorage.getItem(key);
                const isDark = savedTheme
                    ? savedTheme === 'dark'
                    : window.matchMedia('(prefers-color-scheme: dark)').matches;

                document.documentElement.classList.toggle('dark', isDark);
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.ts'])

    </head>
    <body id="app" class="h-full font-sans antialiased bg-background text-foreground">
    </body>
</html>
