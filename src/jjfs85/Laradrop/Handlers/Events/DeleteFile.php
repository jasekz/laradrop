<?php
namespace Jasekz\Laradrop\Handlers\Events;

use Jasekz\Laradrop\Events\FileWasDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Exception, Storage;

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
            $meta = json_decode($event->data['file']->meta);
            $disk = Storage::disk($meta->disk);
            $disk->delete($event->data['file']->filename);   
            $disk->delete('_thumb_' . $event->data['file']->filename);          
        }
        
        catch (Exception $e) {
            throw $e;
        }
    }
}
