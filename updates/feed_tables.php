<?php namespace Tiipiik\Catalog;

use DB;
use October\Rain\Database\Updates\Seeder;
use Tiipiik\Catalog\Models\Category;
use Tiipiik\Catalog\Models\Product;
use Tiipiik\Catalog\Models\CustomField;

class SeedTables extends Seeder
{

    public function run()
    {
        Category::create(['name' => 'Toys', 'slug' => 'toys']);
        Category::create(['name' => 'Motorbike', 'slug' => 'motorbike']);
        
        Product::create([
            'title' => 'Kawasaki 1400 ZZR',
            'slug' => 'kawasaki-1400-zzr',
            'price' => '10000',
        ]);
        
        CustomField::create([
            'name' => 'color',
            'value' => '{"green", "black"}'
        ]);
        
        // Product categories
        DB::insert('insert into tiipiik_catalog_prods_cats (category_id, product_id) values (?, ?)', ['2', '1']);
        
        // Product custom_fields
        DB::insert('insert into tiipiik_catalog_prods_fields (product_id, field_id) values (?, ?)', ['1', '1']);
        
        // Product custom fields values
        DB::insert('insert into tiipiik_catalog_prods_fields (field_id, selected_value) values (?, ?)', ['1', 'green']);
        
    }

}
