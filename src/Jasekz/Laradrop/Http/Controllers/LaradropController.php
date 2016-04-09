<?php
namespace Jasekz\Laradrop\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Jasekz\Laradrop\Services\File;
use Jasekz\Laradrop\Events\FileWasUploaded;
use Jasekz\Laradrop\Events\FileWasDeleted;
use Jasekz\Laradrop\Services\StorageProviders\Storable as StorageProvider;
use Illuminate\Support\Facades\Input;
use Request;
use Exception;
use Config;

class LaradropController extends BaseController {

    /**
     * Constructor
     *
     * @param File $file            
     */
    public function __construct(File $file, StorageProvider $storageProvider)
    {
        $this->file = $file;
        $this->storageProvider = $storageProvider;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $files = $this->file->all()->get();
        
        foreach($files as $file) {
            $file->filename = $this->storageProvider->getPublicLocation() . '/_thumb_' . $file->filename;
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $files
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        try {
            if (! Request::hasFile('file')) {
                throw new Exception('File not provided.');
            }
            
            // move file            
            $fileExt = Input::file('file')->getClientOriginalExtension();
            $fileName = str_replace('.' . $fileExt, '', Input::file('file')->getClientOriginalName()) . '-' . date('Ymdhis');
            $mimeType = Request::file('file')->getMimeType();     
                   
            $movedFileDir = Config::get('laradrop.LARADROP_INITIAL_UPLOADS_DIR');
            $movedFileName = $fileName . '.' . $fileExt;    
               
            $fileSize = Input::file('file')->getSize();
            Request::file('file')->move($movedFileDir, $movedFileName);
            
            // fire 'file uploaded' event
            event(new FileWasUploaded([
                'filePath' => $movedFileDir . '/' . $movedFileName,
                'fileName' => $movedFileName,
                'fileSize' => $fileSize,
                'fileExt'  => $fileExt,
                'postData' => Input::all()
            ]));
            
        } 

        catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Delete the resource
     *
     * @param $id 
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $file = $this->file->find($id);
            $this->file->delete($id);            
            
            // fire 'file deleted' event
            event(new FileWasDeleted([
                'file' => $file
            ]));
        } 

        catch (Exception $e) {
            return $this->handleError($e);
        }
        
        return response()->json([
            'status' => 'success'
        ]);
    }
    
    /**
     * Error handler for this controller
     * 
     * @param Exception $e
     */
    private function handleError(Exception $e)
    {
        return response()->json([
            'status' => 'error',
            'data' => $e->getMessage()
        ]);
    }
}
