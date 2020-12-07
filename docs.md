# Code Documentation

This documentation should help you understand some of our proprietary code.

## Structure

Within the `api` folder you will find a Laravel 6 installation.

Within the empty `vue` folder a VueJS app should be created.

`api.dockerfile` is used to create the PHP container for the API.

## Docker

We are providing a docker-compose.yml file with the required services for this project.
This includes a MariaDB server, an instance of phpmyadmin, a container for the php code
and a Redis server for caching.

We are using a reverse proxy to allow for multiple services on port 80. Please make sure
to add the following hostnames to your hosts file.

 - 127.0.0.1 api.test
 - 127.0.0.1 phpmyadmin.test

## Caching

We have developed a custom caching solution to more easily scale our caching and
provide an easy to use interface for our developers.

Our caching mentality is to have everything in its most recent version in 
cache at any time. To achieve this we are renewing our cache on an event driven
basis. For example an eloquent model `saved` event is perfect for this purpose.

You will find a folder for all the caching code within `/api/app/Cache`. The most
interesting folders for you in this folder are the `Models` and `Fetchers` folders.

Cache data is always stored as json encoded array within Redis.

### Cache Models

We have designed an eloquent like caching model structure which allows for relations.
These models are all stored within the `/api/app/Cache/Models` folder.

Each Model offers the following methods:
 - `Model::find($id)` Fetch from cache
 - `Model::findOrFail($id)` Fetch from cache and if not found throw exceptions
 - `Model::renew($id)` Renew cached data
 - `Model::delete($id)` Delete the data from cache
 - `Model::with($relation)->find($id)` Load the model and specified relations
 
Any of `find`, `findOrFail`, `renew` and `delete` accept `int`, `array` or multiple `int` 
parameters as arguments. Using arrays or multiple parameters will cause the fetcher
to eager load all the data and thus speed up execution.

Cache data for Models is fetched by a Fetcher class. Each Model specifies which fetcher to
use as `protected $fetcher` class property.

### Cache Fetchers

Fetchers are used to get cachable data from any datasource. In our case we will be using
eloquent as the datasource.

A fetcher will get an `array` of `$identifiers` passed into the `fetch()` method. It is the
fetchers job to find the resources for these identifiers and return an array with
the keys being the identifiers requested and values being the value to be cached.

### Testing

In order to use the redis cache for testing, it is flushed everytime a test is executed.