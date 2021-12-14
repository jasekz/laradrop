<?php

namespace Jasekz\Laradrop\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Jasekz\Laradrop\Events\FileWasUploaded;
use Jasekz\Laradrop\Services\File as FileService;

class LaradropController extends BaseController
{
    public $file;

    /**
     * LaradropController constructor.
     * @param FileService $file
     */
    public function __construct(FileService $file)
    {
        $this->file = $file;
    }

    /**
     * Return html containers
     *
     * @return JsonResponse
     */
    public function getContainers()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'main' => view('laradrop::mainContainer')->render(),
                'preview' => view('laradrop::previewContainer')->render(),
                'file' => view('laradrop::fileContainer')->render(),
            ],
        ]);
    }

    /**
     * Return all files which belong to the parent (pid), or root if no pid provided.
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $files = $this->file->get(Request::input('pid'));

            return response()->json([
                'status' => 'success',
                'data' => $files,
            ]);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Error handler for this controller
     *
     * @param Exception $e
     *
     * @return JsonResponse
     */
    private function handleError(Exception $e)
    {
        return response()->json([
            'msg' => $e->getMessage(),
        ], 401);
    }

    /**
     * Create a folder
     *
     * @return JsonResponse
     */
    public function create()
    {
        try {
            $fileData['alias'] = Request::input('filename') ? Request::input('filename') : date('m.d.Y - G:i:s');
            $fileData['type'] = 'folder';
            if (Request::input('pid') > 0) {
                $fileData['parent_id'] = Request::input('pid');
            }

            $this->file->create($fileData);

            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Upload and store new file.
     *
     * @return JsonResponse
     */
    public function store()
    {
        try {

            if (!Request::hasFile('file')) {
                throw new Exception(trans('err.fileNotProvided'));
            }

            if (!Request::file('file')->isValid()) {
                throw new Exception(trans('err.invalidFile'));
            }

            /*
             * move file to temp location
             */
            $fileExt = Request::file('file')->getClientOriginalExtension();
            $fileName = str_replace('.' . $fileExt, '', Request::file('file')->getClientOriginalName()) . '-' . date('Ymdhis');
            $mimeType = Request::file('file')->getMimeType();
            $tmpStorage = storage_path();
            $movedFileName = $fileName . '.' . $fileExt;
            $fileSize = Request::file('file')->getSize();

            if ($fileSize > ((int)config('laradrop.max_upload_size') * 1000000)) {
                throw new Exception(trans('err.invalidFileSize'));
            }

            Request::file('file')->move($tmpStorage, $movedFileName);

            $disk = Storage::disk(config('laradrop.disk'));

            /*
             * create thumbnail if needed
             */
            $fileData['has_thumbnail'] = 0;
            if ($fileSize <= ((int)config('laradrop.max_thumbnail_size') * 1000000) && in_array($mimeType, ["image/jpg", "image/jpeg", "image/png", "image/gif"])) {

                $thumbDims = config('laradrop.thumb_dimensions');
                $img = Image::make($tmpStorage . '/' . $movedFileName);
                $img->resize($thumbDims['width'], $thumbDims['height']);
                $img->save($tmpStorage . '/_thumb_' . $movedFileName);

                // move thumbnail to final location
                $disk->put('_thumb_' . $movedFileName, fopen($tmpStorage . '/_thumb_' . $movedFileName, 'r+'));
                File::delete($tmpStorage . '/_thumb_' . $movedFileName);
                $fileData['has_thumbnail'] = 1;

            }

            /*
             * move uploaded file to final location
             */
            $disk->put($movedFileName, fopen($tmpStorage . '/' . $movedFileName, 'r+'));
            File::delete($tmpStorage . '/' . $movedFileName);

            /*
             * save in db
             */
            $fileData['filename'] = $movedFileName;
            $fileData['alias'] = Request::file('file')->getClientOriginalName();
            $fileData['public_resource_url'] = config('laradrop.disk_public_url') . '/' . $movedFileName;
            $fileData['type'] = $fileExt;
            if (Request::input('pid') > 0) {
                $fileData['parent_id'] = Request::input('pid');
            }
            $meta = $disk->getDriver()->getAdapter()->getMetaData($movedFileName);
            $meta['disk'] = config('laradrop.disk');
            $fileData['meta'] = json_encode($meta);
            $file = $this->file->create($fileData);

            /*
             * fire 'file uploaded' event
             */
            event(new FileWasUploaded([
                'file' => $file,
                'postData' => Request::all(),
            ]));

            return $file;

        } catch (Exception $e) {

            // delete the file(s)
            if (isset($disk) && $disk) {

                if ($disk->has($movedFileName)) {
                    $disk->delete($movedFileName);
                }

                if ($disk->has('_thumb_' . $movedFileName)) {
                    $disk->delete('_thumb_' . $movedFileName);
                }
            }

            return $this->handleError($e);
        }
    }

    /**
     * Delete the resource
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            $this->file->destroy($id);

            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Move file
     *
     * @return JsonResponse
     */
    public function move()
    {

        try {
            $this->file->move(Request::input('draggedId'), Request::input('droppedId'));

            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Update filename
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update($id)
    {

        try {
            $file = $this->file->find($id);
            $file->filename = Request::input('filename');
            $file->save();

            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
}
