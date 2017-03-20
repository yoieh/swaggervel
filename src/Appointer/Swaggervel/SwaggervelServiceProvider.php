<?php

namespace Appointer\Swaggervel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SwaggervelServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {

        // this publishes the default configuration to the user's application config directory
        $this->publishes([
            __DIR__.'/../../config/swaggervel.php' => config_path('swaggervel.php'),
        ]);

        // this publishes the assets required to run swaggervel-ui
        $this->publishes([
            __DIR__.'/../../../public' => public_path('vendor/swaggervel'),
        ], 'public');

        $this->loadViewsFrom(__DIR__.'/../../views', 'swaggervel');

        // this gives the user the opportunity to override the swaggervel index blade file
        $this->publishes([
            __DIR__.'/../../views' => base_path('resources/views/vendor/swaggervel'),
        ], 'views');

        if (!$this->app->routesAreCached()) {
            Route::group(['namespace' => 'Appointer\Swaggervel\Http\Controllers'], function ($router) {
                require __DIR__ . '/Http/routes.php';
            });
        }
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/swaggervel.php', 'swaggervel'
        );
    }
}
