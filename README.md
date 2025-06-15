# SETUP

- `docker compose up`
- `docker compose exec app php bin/console doctrine:database:create`
- `docker compose exec app php bin/console doctrine:migrations:migrate`
- `docker compose exec app php bin/console doctrine:fixtures:load`

## .env

it is commited. Just for this sample app example, i know it should be in .gitignore.

## CRON logging

- `docker compose exec app tail -f /var/log/cron/cron.log`

## Test

- `docker compose exec app php bin/phpunit`

### Flaky tests - sometimes it shows notice:
```
  1x: Since symfony/var-exporter 7.3: The "Symfony\Component\VarExporter\LazyGhostTrait" trait is deprecated, use native lazy objects instead.
    1x in AccessTest::setUp from App\Tests\Domain
```

### Coverage

I did not run coverage report. It most likely is not 80%, but most important core logic is tested. Api controllers are not (which would give noticable % boost).

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

## Code structure

- app\Domain holds all bounded contexts.
  Each has entry file with publicly available functions exposed.
  Internally 

## Caveat

I feel plenty of places i did not like best practices for symfony would sugest. I have not used it before for anly larger project and for simpler ones, it was like 8 years ago. Same goes gor PHP in general. Things that translate over from other languages an dconpcets in general i feel are decent.

DI with its config in yaml seems odd for me, and it most likely is misconfigured. But it works this example.


# Transfering money

Currency rate is taken from db. There is cron job (i could not get symfony scheduler to work) that fetches newest currency rates from service. It marks rates to be valid for 5min. an be changed in env.

Transfer is umbrella over multiple trandfer entries, that each can have different account credi/debit with different counterparty. Easy to add extra fee entries, if neccesary etc.