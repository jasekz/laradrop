<?php

namespace Jasekz\Laradrop\Handlers\Events;

use Exception;
use Illuminate\Queue\InteractsWithQueue;
use Jasekz\Laradrop\Events\FileWasDeleted;
use Storage;

class DeleteFile
{

    /**
     * Handle the event.
     *
     * @param FileWasDeleted $event
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle(FileWasDeleted $event)
    {
        try {
            $meta = json_decode($event->data['file']->meta);
            $disk = Storage::disk($meta->disk);
            $disk->delete($event->data['file']->filename);
            $disk->delete('_thumb_' . $event->data['file']->filename);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
