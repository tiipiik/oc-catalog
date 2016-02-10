<?php namespace TiipiiK\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class FixProductPublished extends Migration
{
    public function up()
    {
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->boolean('is_published')->nullable()->change();
        });
    }

    public function down()
    {
        //
    }
}
