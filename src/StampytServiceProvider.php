<?php

namespace Citadelle\Stampyt;


use Citadelle\Stampyt\app\Console\Commands\Import;
use Citadelle\Stampyt\app\Console\Commands\Test;
use Illuminate\Support\ServiceProvider;

class StampytServiceProvider extends ServiceProvider
{

    protected $commands = [
        Import::class,
        Test::class,
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addCustomConfigurationValues();
    }

    public function addCustomConfigurationValues()
    {
        // add filesystems.disks for the log viewer
        config([
            'logging.channels.stampyt' => [
                'driver' => 'single',
                'path' => storage_path('logs/stampyt.log'),
                'level' => 'debug',
            ]
        ]);

    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/stampyt.php', 'stampyt'
        );

        // register the artisan commands
        $this->commands($this->commands);
    }
}
