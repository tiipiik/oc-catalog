<?php namespace Tiipiik\Catalog\Models;

use Model;

/**
 * CustomField Model
 */
class CustomField extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_custom_fields';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name', 'value'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'products' => ['Tiipiik\Catalog\Models\Product']
    ];
    
    public function onSave()
    {
        return 'Saving...';
    }

}