start "Server" cmd /c "php artisan serve"
start "Vite" cmd /c "npm run dev"
start "Driver" cmd /c "vendor\laravel\dusk\bin\chromedriver-win.exe --port=9515"