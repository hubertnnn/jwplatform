<?php

namespace HubertNNN\JwPlatform\Integration\Laravel;

use HubertNNN\JwPlatform\Contracts;
use HubertNNN\JwPlatform\JwPlatformService;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class JwPlatformServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config.php', 'jwplatform'
        );

        $this->app->singleton(Contracts\JwPlatformService::class, function ($app) {

            /** @var Application $app */
            /** @var Repository $config */
            $config = $app['config'];

            $apiKey = $config->get('jwplatform.credentials.apiKey');
            $secret = $config->get('jwplatform.credentials.secret');
            $fallback = $config->get('jwplatform.fallbackTemplate');
            $players = $config->get('jwplatform.players');

            return new JwPlatformService($apiKey, $secret, $players, $fallback);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config.php' => config_path('jwplatform.php')
        ], 'config');
    }
}
