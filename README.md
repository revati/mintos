# SETUP

- `docker compose up`
- `docker compose exec app php bin/console doctrine:database:create`
- `docker compose exec app php bin/console doctrine:migrations:migrat`
- `docker compose exec app php bin/console doctrine:fixtures:load`

## Test

- `docker compose exec app php bin/phpunit`