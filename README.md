#Installation
run "composer install"
configure .env file
run "php artisan make:migrate"

#CLI commands
repos:list - returns all imported repositories
repos:import - imports new repository and/or restores/adds missing commits
repos:commits - returns all commits of the specified repository
