<?php
namespace Tiipiik\Catalog\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class FixBrandNotNullOnProducts extends Migration
{

    public function up()
    {
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->integer('brand_id')->nullable()->change();
        });
    }
    
    public function down()
    {
        //
    }
}
