<?php

namespace Jasekz\Laradrop\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
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
    public function index(Request $request)
    {
        try {
            $files = $this->file->get($request->get('pid'));

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
    public function create(Request $request)
    {
        try {
            $fileData['alias'] = $request->get('filename') ? $request->get('filename') : date('m.d.Y - G:i:s');
            $fileData['type'] = 'folder';
            if ($request->get('pid') > 0) {
                $fileData['parent_id'] = $request->get('pid');
            }

            $file = $this->file->create($fileData);

            return response()->json([
                'status' => 'success',
                'id' => $file->id
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
    public function store(Request $request)
    {
        try {

            if (!$request->hasFile('file')) {
                throw new Exception(trans('err.fileNotProvided'));
            }

            if (!$request->file('file')->isValid()) {
                throw new Exception(trans('err.invalidFile'));
            }

            /*
             * move file to temp location
             */
            $fileExt = $request->file('file')->getClientOriginalExtension();
            $fileName = str_replace('.' . $fileExt, '', $request->file('file')->getClientOriginalName()) . '-' . date('Ymdhis');
            $mimeType = $request->file('file')->getMimeType();
            $tmpStorage = storage_path();
            $movedFileName = $fileName . '.' . $fileExt;
            $fileSize = $request->file('file')->getSize();

            if ($fileSize > ((int)config('laradrop.max_upload_size') * 1000000)) {
                throw new Exception(trans('err.invalidFileSize'));
            }

            $request->file('file')->move($tmpStorage, $movedFileName);

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
            $fileData['alias'] = $request->file('file')->getClientOriginalName();
            $fileData['public_resource_url'] = config('laradrop.disk_public_url') . '/' . $movedFileName;
            $fileData['type'] = $fileExt;
            if ($request->get('pid') > 0) {
                $fileData['parent_id'] = $request->get('pid');
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
                'postData' => $request->all(),
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
    public function move(Request $request)
    {

        try {
            $this->file->move($request->get('draggedId'), $request->get('droppedId'));

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
    public function update(Request $request, $id)
    {

        try {
            $file = $this->file->find($id);
            $file->filename = $request->get('filename');
            $file->save();

            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
}
