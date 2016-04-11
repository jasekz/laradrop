<?php 
namespace Jasekz\Laradrop\Services\StorageProviders;

use Jasekz\Laradrop\Models\File as FileModel;

interface Storable {
    
    public function moveFile($source);
    
    public function deleteFile(FileModel $file);
    
    public function getPublicLocation();
    
    public function getUploadsDir();
    
}