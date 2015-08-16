<?php
namespace Jasekz\Laradrop;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Jasekz\Laradrop\Services\File as FileService;
use Config;
use File;

class LaradropServiceProvider extends ServiceProvider {

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Jasekz\Laradrop\Events\FileWasUploaded' => [
            'Jasekz\Laradrop\Handlers\Events\CreateThumbnail',
            'Jasekz\Laradrop\Handlers\Events\MoveFile',
            'Jasekz\Laradrop\Handlers\Events\SaveFile',
        ],
        'Jasekz\Laradrop\Events\FileWasDeleted' => [
            'Jasekz\Laradrop\Handlers\Events\DeleteFile',
        ],
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
        
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
            __DIR__ . '/resources/assets' => public_path('vendor/jasekz/laradrop')
        ], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('laradrop', function ($app)
        {
            return new Services\LaradropService();
        });
        
        $this->app->bind('Jasekz\Laradrop\Services\StorageProviders\Storable', function ($app)
        {
            /*
             * Here, we will determine which storage provider should be used, based on config settings
             * and return the appropriate object.
             */
            $provider = 'Jasekz\Laradrop\Services\StorageProviders\\' . ucfirst(strtolower(Config::get('laradrop.LARADROP_STORAGE_ENGINE')));
            return new $provider(new File, new FileService);
        });
    }
}
