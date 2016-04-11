<?php
namespace Jasekz\Laradrop\Handlers\Events;

use Jasekz\Laradrop\Events\FileWasUploaded;
use Jasekz\Laradrop\Services\File as FileService;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Exception;

class CreateThumbnail {
    
    private $acceptableExtensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
    ];
    
    private $maxFileSize = 10; // Megabytes

    /**
     * Create the event handler.
     *
     * @return void
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
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
            if (in_array($event->data['fileExt'], $this->acceptableExtensions) 
                && $event->data['fileSize'] < $this->maxFileSize * 1000000
                && file_exists($this->fileService->getInitialUploadsPath() . '/' . $event->data['fileName'])) {

                $img = Image::make($this->fileService->getInitialUploadsPath() . '/' . $event->data['fileName']);
                $img->resize(150, 150);
                $img->save($this->fileService->getInitialUploadsPath() . '/_thumb_' . $event->data['fileName']);
                $img->destroy();
            }
        } 

        catch (Exception $e) {
            throw $e;
        }
    }
}
