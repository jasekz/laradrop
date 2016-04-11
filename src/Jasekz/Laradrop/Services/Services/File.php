<?php
namespace Jasekz\Laradrop\Services;

use Jasekz\Laradrop\Models\File as FileModel;
use Jasekz\Laradrop\Events\FileWasUploaded;
use Jasekz\Laradrop\Events\FileWasDeleted;
use Exception, Config;

class File extends FileModel {

    /**
     * Return all files which belong to the parent (pid), or root if no pid provided.
     * 
     * @param int $parentId
     * @throws Exception
     * @return array
     */
    public function get($parentId)
    {
        try {
            $out = [];
            
            if($this->count() && $parentId > 0) {
                $files = $this->where('id', '=', $parentId)->first()->immediateDescendants()->get();
            } else if($this->count()) {
                $files = $this->orderBy('parent_id')->first()->getSiblingsAndSelf();
            }
            
            if(isset($files)) {
                $storageProvider = \App::make('\Jasekz\Laradrop\Services\StorageProviders\Storable');
                
                foreach($files as $file) {
                    $file->alias = $file->filename;
                    $file->filename = $storageProvider->getPublicLocation() . '/_thumb_' . $file->filename;
                    $file->numChildren = $file->children()->count();
                    
                    if($file->type == 'folder') {
                        array_unshift($out, $file);
                    } else {
                        $out[] = $file;
                    }
                }
            }
            
            return $out;
        }
        
        catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Delete file(s) 
     * 
     * @param int $id
     * @throws Exception
     */
    public static function destroy($id) 
    {
        try {
            $file = self::find($id);
            
            if($file->descendants()->exists()) {
                foreach($file->descendants()->where('type', '=', 'file')->get() as $descendant) {
                    
                    event(new FileWasDeleted([ // fire 'file deleted' event for each descendant
                        'file' => $descendant
                    ]));
                }
            }
            
            $file->delete($id);
                        
            event(new FileWasDeleted([ // fire 'file deleted' event for file
                'file' => $file
            ]));
        }
        
        catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Move file
     * 
     * @param int $draggedFileId
     * @param int $droppedFileId
     */
    public function move($draggedFileId, $droppedFileId) 
    {
        try {
            $dragged = $this->find($draggedFileId);
            $dropped = $this->find($droppedFileId);
            
            if($droppedFileId == 0) {
                $dragged->makeRoot();
            } else {
                $dragged->makeChildOf($dropped);
            }
        }
        
        catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Return intial uploads path.  
     * 
     * @return string
     */
    public function getInitialUploadsPath()
    {
        return Config::get('laradrop.LARADROP_INITIAL_UPLOADS_DIR');
    }
    
    /**
     * Return public uploads path.  
     * 
     * @return string
     */
    public function getPublicUploadsPath()
    {
        return Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR');
    }
    
    /**
     * Return public file location.  
     * 
     * @return string
     */
    public function getPublicLocation()
    {
        return Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.PUBLIC_LOCATION');
    }
}
