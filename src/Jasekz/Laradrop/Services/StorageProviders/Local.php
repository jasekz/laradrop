<?php
namespace Jasekz\Laradrop\Services\StorageProviders;

use Jasekz\Laradrop\Services\File as FileService;
use Jasekz\Laradrop\Models\File as FileModel;
use DB;
use File;
use Config;
use Exception;

class Local implements Storable {
    
    public function __construct(File $file, FileService $fileService)
    {
        $this->file = $file;
        $this->fileService = $fileService;
    }

    public function moveFile($source)
    {
        $initialUploadsPath = Config::get('laradrop.LARADROP_INITIAL_UPLOADS_DIR');
        $publicUploadsPath = Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR');
        
        if (! File::move($initialUploadsPath . '/' . $source, $publicUploadsPath . '/' . $source)) {
            throw new Exception("Could not copy file {$source}");
        }
        
        if(file_exists($initialUploadsPath . '/_thumb_' . $source)) {
            if (! File::move($initialUploadsPath . '/_thumb_' . $source, $publicUploadsPath . '/_thumb_' . $source)) {
                throw new Exception("Could not move file _thumb_{$source}");
            }              
        } else {                
            if (! File::copy(__DIR__ . '/../../resources/assets/img/genericThumbs/no_thumb.jpg', $publicUploadsPath . '/_thumb_' . $source)) {
                throw new Exception("Could not copy file no_thumb.jpg");
            }
        }
    }

    public function deleteFile(FileModel $file)
    {
        $initialUploadsPath = Config::get('laradrop.LARADROP_INITIAL_UPLOADS_DIR');
        $publicUploadsPath = Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR');
        
        File::delete(
            $publicUploadsPath . '/' . $file->filename,
            $publicUploadsPath . '/_thumb_' . $file->filename
        );
    }
    
    public function getPublicLocation()
    {
        return Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.PUBLIC_LOCATION');
    }
    
}