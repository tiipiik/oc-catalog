<?php namespace Tiipiik\Catalog;

use October\Rain\Database\Updates\Seeder;
use Tiipiik\Catalog\Models\Group;

class SeedGroupsTables extends Seeder
{

    public function run()
    {
        // Create groups
        Group::create(['name' => 'Motorbikes']);
        Group::create(['name' => 'Accessories']);
    }

}