<?php

namespace App\Cache\Providers;

use App\Cache\CacheHandlers\ClockworkLoggerRedisCacheHandler;
use App\Cache\CacheHandlers\RedisCacheHandler;
use App\Cache\Contracts\CacheHandlerContract;
use App\Cache\Events\CacheMissing;
use App\Cache\Listeners\SaveFetchedDataInCache;
use Event;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CacheHandlerContract::class, RedisCacheHandler::class);
        $this->app->bind(RedisCacheHandler::class, function() {
            return new RedisCacheHandler(cache()->getStore('redis')->getRedis());
        });

        Event::listen(CacheMissing::class, SaveFetchedDataInCache::class);
    }
}
