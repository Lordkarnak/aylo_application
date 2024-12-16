## Laravel cache application

An application that downloads entries from a given url and stores them locally. Includes a simple api to query and present existing entries.
An .env is included to boost deployment and test functionality.
The app can be containerized in docker and run in a fixed environment using php8.4-fpm, nginx, mysql-8.4.3

## Installation
- Make sure to have docker installed locally.
- Clone the application in a directory
- Navigate to that directory and run the following commands in the cmd

```
docker compose up --build --detach
docker compose exec app bash
```

Inside the app terminal you can then run laravel artisan commands, control the environment, install or remove packages as needed.

```
php artisan migrate
npm run build
```

Note that npm is only needed to display the basic frontend.

## Console
Two console commands are available for utilizing the cache:

**- app:get-pornstars** [options]

`--force` Force download of new entries
  
`--debug` Display debug info and error messages
  
`--max` Max entries to store.
  
**- app:refresh-cache** [options]

`--debug` Display error messages, if any

## Schedule
The console command to get entries is set to run twice per day to check if any modifications were made to the original file and retrieve new content.

## URLS
Below are the available urls:

get:
- **/** -> homepage
- **/api/pornstars** -> get entries paginated
- **/api/pornstars/{id}** -> get entry for one creator, where id is a number, without the {}
- **/api/pornstars/{pornstar id}/thumbnails/{thumbnail id}** -> retrieve a thumbnail from cache if exists, without the {}

post:
- **/api/pornstars/{id}/refreshCache** refresh cache for a given creator id via the api
