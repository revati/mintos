# SETUP

- `docker compose up`
- `docker compose exec app php bin/console doctrine:database:create`
- `docker compose exec app php bin/console doctrine:migrations:migrate`
- `docker compose exec app php bin/console doctrine:fixtures:load`

## CRON logging

- `docker compose exec app tail -f /var/log/cron/cron.log`

## Test

- `docker compose exec app php bin/phpunit`

# API endpoints

Import postmann collection: `./minots-app-roberts.postman_collection.json`

- GET `/api/users` - get users
- GET `/api/accounts?filter[user_id]=xxx`
- GET `/api/transactions?filter[account_id]=xxx&limit=0&offset=10`
- POST `/api/transactions/initialize`
  > {
  >   "description": "Description",
  >   "amount": 100,
  >   "currency": "EUR",
  >   "debit_account_id": "xxx",
  >   "credit_account_id": "xxx"
  > }