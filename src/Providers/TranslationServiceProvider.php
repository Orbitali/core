<?php

namespace Orbitali\Providers;

use Orbitali\Foundations\TranslationLoaderManager;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\TranslationServiceProvider as IlluminateTranslationServiceProvider;

class TranslationServiceProvider extends IlluminateTranslationServiceProvider
{

    /**
     * Register the application services.
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

    }

    /**
     * Register the translation line loader. This method registers a
     * `TranslationLoaderManager` instead of a simple `FileLoader` as the
     * applications `translation.loader` instance.
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new TranslationLoaderManager($app['files'], $app['path.lang']);
        });
    }
}
