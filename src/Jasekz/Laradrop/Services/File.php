<?php

namespace Jasekz\Laradrop\Services;

use Jasekz\Laradrop\Models\File as FileModel;
use Jasekz\Laradrop\Events\FileWasDeleted;

class File extends FileModel
{

    /**
     * Return all files which belong to the parent (pid), or root if no pid provided.
     *
     * @param $parentId
     * @return array
     * @throws \Exception
     */
    public function get($parentId)
    {
        try {
            $out = [];

            if ($this->count() && $parentId > 0) {
                $files = $this->where('parent_id', '=', $parentId)->get();
            } else if ($this->count()) {
                $files = $this->whereNull('parent_id')->get();
            }

            if (isset($files)) {

                foreach ($files as $file) {

                    if ($file->has_thumbnail && config('laradrop.disk_public_url')) {

                        $publicResourceUrlSegments = explode('/', $file->public_resource_url);
                        $publicResourceUrlSegments[count($publicResourceUrlSegments) - 1] = '_thumb_' . $publicResourceUrlSegments[count($publicResourceUrlSegments) - 1];
                        $file->filename = implode('/', $publicResourceUrlSegments);

                    } else {
                        $file->filename = config('laradrop.default_thumbnail_url');
                    }

                    $file->numChildren = $file->children()->count();

                    if ($file->type == 'folder') {
                        array_unshift($out, $file);
                    } else {
                        $out[] = $file;
                    }
                }
            }

            return $out;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete file(s)
     *
     * @param int $id
     * @return int|void
     * @throws \Exception
     */
    public static function destroy($id)
    {
        try {
            $file = self::find($id);

            if ($file->children()->exists()) {
                foreach ($file->children()->where('type', '!=', 'folder')->get() as $descendant) {

                    event(new FileWasDeleted([ // fire 'file deleted' event for each descendant
                        'file' => $descendant
                    ]));

                    $descendant->delete();
                }
            }

            $file->delete($id);

            if ($file->type != 'folder') {
                event(new FileWasDeleted([ // fire 'file deleted' event for file
                    'file' => $file
                ]));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Move file
     *
     * @param $draggedFileId
     * @param $droppedFileId
     * @throws \Exception
     */
    public function move($draggedFileId, $droppedFileId)
    {
        try {
            $dragged = $this->find($draggedFileId);
            $dropped = $this->find($droppedFileId);

            if ($droppedFileId == 0) {
                $dragged->parent_id = null;
            } else {
                $dragged->parent_id = $dropped->id;
            }
            $dragged->save();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
