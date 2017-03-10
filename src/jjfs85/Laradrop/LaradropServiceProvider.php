<?php
namespace jjfs85\Laradrop;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use jjfs85\Laradrop\Services\File as FileService;
use Config;
use File;
use Storage;

class LaradropServiceProvider extends ServiceProvider {

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'jjfs85\Laradrop\Events\FileWasDeleted' => [
            'jjfs85\Laradrop\Handlers\Events\DeleteFile',
        ],
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(DispatcherContract $events = null)
    {
        // different constructor signature for 5.3+
        if(\App::version() >= '5.3.0') {            
            parent::boot();
        }        
        else {            
            parent::boot($events);
        }
        
        if (! $this->app->routesAreCached()) {
            require __DIR__ . '/Http/routes.php';
        }
        
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('laradrop.php')
        ]);
        
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'migrations');
        
        $this->publishes([
            __DIR__ . '/resources/assets' => public_path('vendor/jjfs85/laradrop')
        ], 'public');
        
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'laradrop');
        
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'laradrop');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {        
    }
}
