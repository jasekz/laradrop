<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Jasekz\Laradrop\Http\Controllers\LaradropController;
use Jasekz\Laradrop\Services\File as FileService;


/**
 * Copy this test to your Laravel app's "tests" directory and run it from there:
 *
 * `vendor/bin/phpunit tests/LaradropControllerCRUDTest`
 */
class LaradropControllerCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected string $storage, $filename;

    protected UploadedFile $file;

    protected FileService $fileService, $newFile, $newFileFromDb;

    protected LaradropController $controller;

    protected function setUp()
    {
        parent::setUp();

        $this->storage = 'laradrop-tests';
        $this->fileName = 'laradrop-test.png';

        Storage::fake($this->storage);
        config(['laradrop.disk' => $this->storage]);

        $this->file = UploadedFile::fake()->image($this->fileName);
        $this->fileService = new FileService();

        $this->controller = new LaradropController($this->fileService);

        $this->newFileFromDb = new FileService();
        $this->newFile = new FileService();
    }

    /** @test */
    public function throws_error_if_uploaded_file_is_missing()
    {
        $res = $this->controller->store(Request::create('/store', 'POST'));

        $this->assertEquals('err.fileNotProvided', $res->getData()->msg);
    }

    /** @test */
    public function successfully_uploads_file()
    {
        $this->setFiles();

        Storage::disk($this->storage)->assertExists($this->newFile->filename);
        $this->assertEquals($this->newFile->id, $this->newFileFromDb->id);
    }

    /** @test */
    public function edits_filename()
    {
        $this->setFiles();

        $this->assertEquals($this->newFile->filename, $this->newFileFromDb->filename);

        $newFileName = '_prefix_' . $this->newFile->filename;
        $this->controller->update( Request::create('/update', 'POST', ['filename' => $newFileName]), $this->newFile->id );
        $this->newFileFromDb = FileService::find($this->newFile->id);

        $this->assertNotEquals($this->newFile->filename, $this->newFileFromDb->filename);
        $this->assertEquals($this->newFileFromDb->filename, $newFileName);
    }

    /** @test */
    public function deletes_file()
    {
        $this->setFiles();

        $this->assertEquals($this->newFile->filename, $this->newFileFromDb->filename);

        $fileId = $this->newFile->id;
        $this->controller->destroy( $fileId );
        $file = FileService::find($fileId);

        $this->assertNull($file);
    }

    /** @test */
    public function creates_folder_with_filename_provided()
    {
        $res = $this->controller->create( Request::create('/update', 'POST', ['filename' => $this->fileName, 'type' => 'folder']) );
        $fileFromDb = FileService::find($res->getData()->id);

        $this->assertEquals('success', $res->getData()->status);
        $this->assertEquals($res->getData()->id, $fileFromDb->id);
        $this->assertEquals('folder', $fileFromDb->type);
        $this->assertEquals($this->fileName, $fileFromDb->alias);
    }

    /** @test */
    public function creates_folder_with_filename_not_provided()
    {
        $res = $this->controller->create( Request::create('/update', 'POST', ['type' => 'folder']) );
        $fileFromDb = FileService::find($res->getData()->id);

        $this->assertEquals('success', $res->getData()->status);
        $this->assertEquals($res->getData()->id, $fileFromDb->id);
        $this->assertEquals('folder', $fileFromDb->type);
        $this->assertNotEquals($this->fileName, $fileFromDb->alias);
    }

    /** @test */
    public function moves_file()
    {
        $this->setFiles();

        $res = $this->controller->create( Request::create('/create', 'POST', ['filename' => 'Folder 1', 'type' => 'folder']) );
        $draggedInId = $this->newFile->id;
        $droppedInId = $res->getData()->id;
        $res = $this->controller->move( Request::create('/move', 'POST', ['draggedId' => $draggedInId, 'droppedId' => $droppedInId]) );
        $draggedIn = FileService::find($draggedInId);
        $droppedIn = FileService::find($droppedInId);

        $this->assertEquals('success', $res->getData()->status);
    }

    protected function setFiles()
    {
        if( ! $this->newFile->id ) $this->newFile = $this->controller->store( Request::create('/store', 'POST', [], [], ['file' => $this->file]) );
        if( ! $this->newFileFromDb->id ) $this->newFileFromDb = FileService::find($this->newFile->id);
    }

    protected function tearDown()
    {
        parent::tearDown();

        Storage::disk($this->storage)->delete([$this->newFile->filename, '_thumb_' . $this->newFile->filename]);
    }
}
