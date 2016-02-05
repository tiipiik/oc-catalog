<?php namespace Tiipiik\Catalog\Models;

use Model;

/**
 * Group Model
 */
class Group extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_groups';

    public $translatable = ['name'];
    
    public $rules = [
        'name' => 'required|unique:tiipiik_catalog_groups',
    ];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    
    public $belongsToMany = [
        'products' => [
            'Tiipiik\Catalog\Models\Product',
            'table' => 'tiipiik_catalog_group_product',
            'order' => 'name'
        ],
        'custom_fields' => [
            'Tiipiik\Catalog\Models\CustomField',
            'table' => 'tiipiik_catalog_group_field',
            'order' => 'name'
        ],
    ];
    
     /**
     * Add translation support to this model, if available.
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        if (!class_exists('RainLab\Translate\Behaviors\TranslatableModel')) {
            return;
        }

        self::extend(function ($model) {
            $model->implement[] = 'RainLab.Translate.Behaviors.TranslatableModel';
        });
    }
}
