<?php namespace Tiipiik\Catalog\Models;

use DB;
use Model;
use Tiipiik\Catalog\Models\Product as ProductModel;
use Tiipiik\Catalog\Models\CustomValue as CustomValueModel;

/**
 * CustomField Model
 */
class CustomField extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_custom_fields';

    /**
     * @var array Translatable fields
     */
    public $translatable = ['display_name', 'default_value'];

    /**
     * Validation rules
     */
    public $rules = [
        'template_code' => 'required|unique:tiipiik_catalog_custom_fields',
        'display_name' =>'required',
    ];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['*'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'customvalues' => [
            'Tiipiik\Catalog\Models\CustomValue',
            'table' => 'tiipiik_catalog_csf_csv',
        ],
    ];
    
    public $belongsToMany = [
        'groups' => ['Tiipiik\Catalog\Models\Group', 'table' => 'tiipiik_catalog_group_field', 'order' => 'name'],
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
    
    /*
     * Add newly created custom fields to all products
     */
    public function afterSave()
    {
        // Need to ensure that the custom field is only in edition mode
        
        $products = ProductModel::all();
        
        $products->each(function($product)
        {
            // If product alreay has the custom fiel, do nothing
            $hasCustomValue = CustomValueModel::where('product_id', '=', $product->id)
                ->where('custom_field_id', '=', $this->id)
                ->first();
            
            if (!$hasCustomValue)
            {
                // Create default value for each product
                $customValue = CustomValueModel::create([
                    'product_id' => $product->id,
                    'custom_field_id' => $this->id,
                    'value' => $this->default_value,
                ]);
                        
                // Create relation between custom value and custom field
                DB::insert('insert into tiipiik_catalog_csf_csv (custom_value_id, custom_field_id) values ("'.$customValue->id.'", "'.$this->id.'")');
            }
        });
    }
    
    
    /*
     * Remove custom field to all products
     */
    public function beforeDelete()
    {
        // Grab all products
        $products = ProductModel::all();
        
        $products->each(function($product)
        {
            // Find the related custom value
            $customValue = CustomValueModel::where('product_id', '=', $product->id)
                ->where('custom_field_id', '=', $this->id)
                ->first();
                
            // Delete relation
            $relation = DB::table('tiipiik_catalog_csf_csv')
                ->where('custom_value_id', '=', $customValue->id)
                ->delete();
            
            // Delete custom value
            CustomValueModel::find($customValue->id)->delete();
        });
        
    }

}