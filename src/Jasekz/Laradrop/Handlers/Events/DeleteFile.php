<?php
namespace Jasekz\Laradrop\Handlers\Events;

use Jasekz\Laradrop\Events\FileWasDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Exception;

class DeleteFile {

    /**
     * Handle the event.
     *
     * @param FileWasDeleted $event            
     * @return void
     */
    public function handle(FileWasDeleted $event)
    {
        try {
            app()->make('laradropStorage')->delete($event->data['file']->filename);   
            app()->make('laradropStorage')->delete('_thumb_' . $event->data['file']->filename);          
        }
        
        catch (Exception $e) {
            throw $e;
        }
    }
}
