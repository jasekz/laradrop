<?php
namespace Jasekz\Laradrop\Services\StorageProviders;

use Jasekz\Laradrop\Services\File as FileService;
use Jasekz\Laradrop\Models\File as FileModel;
use DB;
use File;
use Config;
use Exception;

class S3 implements Storable {
    
    public function __construct(File $file, FileService $fileService)
    {
        $this->file = $file;
        $this->fileService = $fileService;
    }

    public function moveFile($source)
    {
        $initialUploadsPath = Config::get('laradrop.LARADROP_INITIAL_UPLOADS_DIR');
        $publicUploadsPath = Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR');
        

    }

    public function deleteFile(FileModel $file)
    {
        $initialUploadsPath = Config::get('laradrop.LARADROP_INITIAL_UPLOADS_DIR');
        $publicUploadsPath = Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR');
        

    }
    
    public function getPublicLocation()
    {
        return Config::get('laradrop.LARADROP_STORAGE_ENGINES.S3.PUBLIC_LOCATION');
    }
    
}