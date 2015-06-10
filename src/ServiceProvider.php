<?php

namespace Axn\LaravelXform;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['xform', 'formbuilder'];
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/xform.php' => config_path('xform.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDependencies();

        $this->mergeConfigFrom(__DIR__ . '/config/xform.php', 'xform');

        $this->registerFormBuilder();

        $this->app->singleton('xform', function ($app) {
            return new XForm(
                $app['html'],
                $app['formbuilder'],
                $app['config'],
                $app['session']
            );
        });
    }

    protected function registerDependencies()
    {
        $this->app->register('Collective\Html\HtmlServiceProvider');

        $loader = AliasLoader::getInstance();
        $loader->alias('Form', 'Collective\Html\FormFacade');
        $loader->alias('Html', 'Collective\Html\HtmlFacade');
    }

    /**
     * Register the form builder instance.
     *
     * @return void
     */
    protected function registerFormBuilder()
    {
        $this->app->bindShared('formbuilder', function($app)
        {
            $form = new FormBuilder($app['html'], $app['url'], $app['session.store']->getToken());

            return $form->setSessionStore($app['session.store']);
        });
    }
}
