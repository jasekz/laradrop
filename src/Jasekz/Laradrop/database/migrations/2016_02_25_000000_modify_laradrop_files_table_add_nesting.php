<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyLaradropFilesTableAddNesting extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laradrop_files', function ($table) {
            $table->integer('parent_id')->nullable()->index()->after('id');
            $table->integer('lft')->nullable()->index()->after('parent_id');
            $table->integer('rgt')->nullable()->index()->after('lft');
            $table->integer('depth')->nullable()->after('rgt');
            $table->string('type')->nullable()->after('depth');
            $table->text('meta')->nullable()->after('type');
            $table->string('public_resource_url')->after('filename');
            $table->string('alias')->nullable()->after('filename');
            $table->smallInteger('has_thumbnail')->after('type')->default(0);
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('laradrop_files', function ($table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('lft');
            $table->dropColumn('rgt');
            $table->dropColumn('depth');
            $table->dropColumn('type');
            $table->dropColumn('meta');
            $table->dropColumn('public_resource_url');
            $table->dropColumn('alias');
            $table->dropColumn('has_thumbnail');
        });
    }
}
