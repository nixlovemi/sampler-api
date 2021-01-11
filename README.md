## Sampler Project

How to install this project:
- clone this repo
- run "composer install"
- rename the home file ".env-sample" to ".env" (or copy it)
- open the file .env and fill in the database information (at the top of the file)
- run "php artisan key:generate"
- run "php artisan migrate:fresh --seed"
- run "php artisan serve"
- access "http://127.0.0.1:8000" to check if its running
- you can import the insomnia-routes.json file to insomnia to see the endpoints, or check the documentation here: xxxx