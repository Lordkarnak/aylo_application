## Laravel cache application

An application that downloads entries from a given url and stores them locally. Includes a simple api to query and present existing entries.
An .env is included to boost deployment and test functionality.
The app can be containerized in docker and run in a fixed environment using php8.4-fpm, nginx, mysql-8.4.3

## Installation
<ul>
    <li>Make sure to have docker installed locally.</li>
    <li>Clone the application in a directory</li>
    <li>Navigate to that directory and run the following commands in the cmd</li>
</ul>

<code>
docker compose up --build --detach
docker compose exec app bash
</code>

Inside the app terminal you can then run laravel artisan commands, control the environment, install or remove packages as needed.
<code>
php artisan migrate
npm run build
</code>

Note that npm is only needed to display the basic frontend.

## Console
Two console commands are available for utilizing the cache:

<b>- app:get-pornstars</b> [options]
  --force Force download of new entries
  --debug Display debug info and error messages
  --max Max entries to store.
  
<b>- app:refresh-cache</b> [options]

## Schedule
The console command to get entries is set to run twice per day to check if any modifications were made to the original file and retrieve new content.

## URLS
Below are the available urls:

get:
<b>/</b> -> homepage
<b>/api/pornstars</b> -> get entries paginated
<b>/api/pornstars/{id}</b> -> get entry for one creator, where id is a number, without the {}
<b>/api/pornstars/{pornstar id}/thumbnails/{thumbnail id}</b> -> retrieve a thumbnail from cache if exists, without the {}

post:
<b>/api/pornstars/{id}/refreshCache</b> refresh cache for a given creator id via the api
