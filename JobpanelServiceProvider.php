<?php

namespace Jobpanel;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Jobpanel\Repositories\DatabaseJobRepository;
use Jobpanel\Repositories\FailedJobRepository;
use Jobpanel\Repositories\RedisJobRepository;
use Jobpanel\Supervisors\SupervisorManager;

class JobpanelServiceProvider extends ServiceProvider
{

    protected $config;
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigs();
        $this->registerJobpanel();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();

        $this->publishes([
            __DIR__ . '/config/jobpanel.php' => config_path('jobpanel.php'),
        ]);
    }

    /**
     * Register manager.
     *
     * @return void
     */
    protected function registerJobpanel()
    {
        $this->app->singleton('Jobpanel', function ($app) {
            return new JobManager($this->getDriver(), $this->config['queue']);
        });

        $this->app->singleton('Jobpanel.failed', function ($app) {
            return new JobFailedManager($this->getFailedDriver(), $this->config['queue']);
        });

        $this->app->singleton('Jobpanel.supervisor', function ($app) {
            return new SupervisorManager();
        });
    }

    /**
     * Merge configurations.
     */
    protected function mergeConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/jobpanel.php', 'jobpanel');

        $this->config = $this->app->make('config')->get('jobpanel');
    }

    protected function getFailedDriver()
    {
        return new FailedJobRepository($this->config);
    }

    protected function getDriver()
    {
        $driver = $this->config['driver'] ?: 'database';
        switch ($driver) {
            case 'value':
                return new RedisJobRepository($this->config);
                break;

            case 'database':
            default:
                return new DatabaseJobRepository($this->config);
                break;
        }
    }

    protected function registerRoutes()
    {
        Route::group([
            'domain' => $this->config['route']['domain'] ?: null,
            'prefix' => $this->config['route']['prefix'],
            'namespace' => 'Jobpanel\Http\Controllers',
            'middleware' => $this->config['route']['middleware'] ?: 'web',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
        });
    }
}
