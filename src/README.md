## Repos viewer

#Installation
- docker-compose up -d
- docker-compose run --rm composer install
- docker-compose run --rm artisan key:generate
- docker-compose run --rm artisan migrate

#CLI commands
- docker-compose run --rm artisan projects:list - returns all imported repositories
- docker-compose run --rm artisan projects:import - imports new repository and/or restores/adds missing commits
- docker-compose run --rm artisan projects:commits - returns all commits of the specified repository
