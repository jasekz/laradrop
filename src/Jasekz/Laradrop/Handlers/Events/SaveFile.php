<?php
namespace Jasekz\Laradrop\Handlers\Events;

use Jasekz\Laradrop\Events\FileWasUploaded;
use Jasekz\Laradrop\Services\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Exception;

class SaveFile {

    /**
     * Create the event handler.
     *
     * @return void
     */
    public function __construct(File $file)
    {
        $this->file = $file;
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
            return $this->file->store([
                'filename' => $event->data['fileName'],
            ]);
        } 

        catch (Exception $e) {
            throw $e;
        }
    }
}
