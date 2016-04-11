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
        $publicUploadsPath = Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR');

        try {
            if (! File::move($source['filePath'] . '/' . $source['fileName'], $publicUploadsPath . '/' . $source['fileName'])) {
                throw new Exception('Could not copy file ' . $source['filePath'] . '/' . $source['fileName']);
            }
            
            if(file_exists($source['filePath'] . '/_thumb_' . $source['fileName'])) {
                if (! File::move($source['filePath'] . '/_thumb_' . $source['fileName'], $publicUploadsPath . '/_thumb_' . $source['fileName'])) {
                    throw new Exception('Could not move file _thumb_' . $source['filePath'] . '/' . $source['fileName']);
                }              
            } else {                
                if (! File::copy(__DIR__ . '/../../resources/assets/img/genericThumbs/no_thumb.jpg', $publicUploadsPath . '/_thumb_' . $source['fileName'])) {
                    throw new Exception("Could not copy file no_thumb.jpg");
                }
            }
        }
        
        catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteFile(FileModel $file)
    {
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
    
    public function getUploadsDir()
    {
        return Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR');
    }
    
}