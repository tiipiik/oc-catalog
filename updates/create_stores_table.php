<?php namespace Tiipiik\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateStoresTable extends Migration
{

    public function up()
    {
        Schema::create('tiipiik_catalog_stores', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->boolean('is_activated')->default(0);
            $table->timestamps();
        });
        
        Schema::create('tiipiik_catalog_products_stores', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->unsigned();
            $table->integer('store_id')->unsigned();
            $table->primary(['product_id', 'store_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tiipiik_catalog_stores');
        Schema::dropIfExists('tiipiik_catalog_products_stores');
    }

}
