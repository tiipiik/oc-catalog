<?php namespace Tiipiik\Catalog\Models;

use Model;

/**
 * CustomValue Model
 */
class CustomValue extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_custom_values';

    /**
     * @var array Translatable fields
     */
    public $translatable = ['display_name', 'default_value'];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['product_id', 'custom_field_id', 'value'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'Tiipiik\Catalog\Models\Product'
    ];
    
    public $belongsToMany = [
        'custom_field' => [
            'Tiipiik\Catalog\Models\CustomField',
            'table' => 'tiipiik_catalog_csf_csv',
            //'order' => 'display_name',
            'foreignKey' => 'custom_field_id'
        ],
    ];
    
    public $hasOne = [
        'Tiipiik\Catalog\Models\Product'
    ];
    
     /**
     * Add translation support to this model, if available.
     * @return void
     */
    public static function boot()
    {
        // Call default functionality (required)
        parent::boot();

        // Check the translate plugin is installed
        if (!class_exists('RainLab\Translate\Behaviors\TranslatableModel'))
            return;

        // Extend the constructor of the model
        self::extend(function($model){
            // Implement the translatable behavior
            $model->implement[] = 'RainLab.Translate.Behaviors.TranslatableModel';
        });
    }

}