<?php
namespace Jasekz\Laradrop\Handlers\Events;

use Jasekz\Laradrop\Events\FileWasUploaded;
use Jasekz\Laradrop\Services\StorageProviders\Storable as StorageProvider;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Exception;

class MoveFile {

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
     * @param FileWasUploaded $event            
     * @return void
     */
    public function handle(FileWasUploaded $event)
    {
        try {
            $this->storageProvider->moveFile($event->data);            
        }
        
        catch (Exception $e) {
            throw $e;
        }
    }
}
