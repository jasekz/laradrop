<?php
namespace Jasekz\Laradrop\Handlers\Events;

use Jasekz\Laradrop\Events\FileWasDeleted;
use Jasekz\Laradrop\Services\StorageProviders\Storable as StorageProvider;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Exception;

class DeleteFile {

    /**
     * Create the event handler.
     *
     * @return void
     */
    public function __construct(StorageProvider $storageProvider) 
    {
        $this->storageProvider = $storageProvider;
    }

    /**
     * Handle the event.
     *
     * @param FileWasDeleted $event            
     * @return void
     */
    public function handle(FileWasDeleted $event)
    {
        try {
            $this->storageProvider->deleteFile($event->data['file']);            
        }
        
        catch (Exception $e) {
            throw $e;
        }
    }
}
