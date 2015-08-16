<?php
namespace Jasekz\Laradrop\Services;

use Jasekz\Laradrop\Models\File as FileModel;
use Exception;
use Config;

class File extends Base {

    protected $model = null;

    private $table = 'laradrop_files';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = new FileModel();
    }

    /**
     * Get file
     *
     * @param int $id
     * @return App\Models\File
     */
    public function find($id)
    {
        return FileModel::find($id);
    }

    /**
     * Delete file from S3 and databse
     *
     * @param int $fileId            
     * @throws Exception
     */
    public function delete($fileId)
    {
        try {
            $file = FileModel::findOrFail($fileId);
            $file->delete();
        } 

        catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Creates thumbnail and uploads both full size image and thumbnail to storage server
     *
     * @param string $destination            
     * @throws Exception
     */
    public function store($data)
    {
        try {            
            return FileModel::create($data);
        } 

        catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    public function getInitialUploadsPath()
    {
        return Config::get('laradrop.LARADROP_INITIAL_UPLOADS_DIR');
    }
    
    public function getPublicUploadsPath()
    {
        return Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR');
    }
    
    public function getPublicLocation()
    {
        return Config::get('laradrop.LARADROP_STORAGE_ENGINES.LOCAL.PUBLIC_LOCATION');
    }
}
