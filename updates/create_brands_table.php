<?php namespace TiipiiK\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBrandsTable extends Migration
{
    public function up()
    {
        Schema::create('tiipiik_catalog_brands', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->unique()->nullable();
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        // Relation between brand and products
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->integer('brand_id')->after('group_id')->unsigned();
        });
        
        Schema::create('tiipiik_catalog_products_brands', function ($table) {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->unsigned();
            $table->integer('brand_id')->unsigned();
            $table->primary(['product_id', 'brand_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tiipiik_catalog_brands');
        Schema::dropIfExists('tiipiik_catalog_products_brands');
        
        if (Schema::hasColumn('tiipiik_catalog_products', 'brand_id')) {
            Schema::table('tiipiik_catalog_products', function ($table) {
                $table->dropColumn('brand_id');
            });
        }
    }
}
