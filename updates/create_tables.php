<?php namespace Tiipiik\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTables extends Migration
{

    public function up()
    {
        Schema::create('tiipiik_catalog_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->index()->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->integer('nest_left');
            $table->integer('nest_right');
            $table->integer('nest_depth')->nullable();
            $table->timestamps();
        });
        
        Schema::create('tiipiik_catalog_products', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->integer('items_available')->nullable();
            $table->integer('price')->nullable();
            $table->integer('discount_price')->nullable();
            $table->timestamps();
        });
        
        Schema::create('tiipiik_catalog_custom_fields', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->text('value');
            $table->timestamps();
        });
        
        // Relation between categories and products
        Schema::create('tiipiik_catalog_prods_cats', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['product_id', 'category_id']);
        });
        
        // Relation between products and custom fields
        Schema::create('tiipiik_catalog_prods_fields', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->unsigned();
            $table->integer('field_id')->unsigned();
            $table->string('selected_value');
            $table->primary(['product_id', 'field_id']);
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('tiipiik_catalog_categories');
        Schema::dropIfExists('tiipiik_catalog_products');
        Schema::dropIfExists('tiipiik_catalog_custom_fields');
        Schema::dropIfexists('tiipiik_catalog_prods_cats');
        Schema::dropIfExists('tiipiik_catalog_prods_fields');
    }

}
