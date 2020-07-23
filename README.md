## Repos viewer

#Installation
- run "composer install"
- configure .env file
- run "php artisan make:migrate"

#CLI commands
- php artisan repos:list - returns all imported repositories
- php artisan repos:import - imports new repository and/or restores/adds missing commits
- php artisan repos:commits - returns all commits of the specified repository
