<?php
namespace Jasekz\Laradrop\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'laradrop_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filename',
    ];

}
