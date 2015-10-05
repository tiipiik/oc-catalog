<?php namespace Tiipiik\Catalog\Models;

use DB;
use Model;
use Tiipiik\Catalog\Models\Store as StoreModel;
use Tiipiik\Catalog\Models\Product as ProductModel;
use Tiipiik\Catalog\Models\CustomValue as CustomValueModel;

//use SystemException;
// throw new SystemException('Message');

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
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'customvalues' => ['Tiipiik\Catalog\Models\CustomValue', 'table' => 'tiipiik_catalog_csf_csv'],
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


    /**
     * Allows filtering for specifc groups
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $categories List of category ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterGroups($query, $groups)
    {
        return $query->whereHas('groups', function($q) use ($groups) {
            $q->whereIn('id', $groups);
        });
    }

    
    /*
     * Add newly created custom fields to all products
     */
    public function afterSave()
    {
        // Need to ensure that the custom field is only in edition mode
        
        // Retreve the groups
        //  Should be done directly from the relation
        $groups = DB::table('tiipiik_catalog_group_field')->where('custom_field_id', '=', $this->id)->get();
        
        // For each group attached to this custom field
        foreach ($groups as $group)
        {
            // Get all products of this group
            $products = ProductModel::whereGroupId($group->group_id)->get();
            
            $products->each(function($product)
            {
                // Get the group of the product
                $product_group = $product->group_id;
                
                // If product alreay has the custom field, do nothing
                $has_custom_value = CustomValueModel::where('product_id', '=', $product->id)
                    ->where('custom_field_id', '=', $this->id)
                    ->first();

                if (!$has_custom_value)
                {
                    // Create default value for each product
                    $custom_value = CustomValueModel::create([
                        'product_id' => $product->id,
                        'custom_field_id' => $this->id,
                        'value' => $this->default_value,
                    ]);
                    
                    // Create relation between custom value and custom field if not exists
                    $relation = DB::table('tiipiik_catalog_csf_csv')
                        ->whereCustomValueId($custom_value->id)
                        ->whereCustomFieldId($this->id)
                        ->first();
                    
                    if (! $relation)
                    {
                        DB::insert('insert into tiipiik_catalog_csf_csv (custom_value_id, custom_field_id) values ("'.$custom_value->id.'", "'.$this->id.'")');
                    }
                }
            });
            
            // Get all stores of this group
            $stores = StoreModel::whereGroupId($group->group_id)->get();

            $stores->each(function($store)
            {
                // Get the group of the product
                $store_group = $store->group_id;
                
                // If product alreay has the custom field, do nothing
                $has_custom_value = CustomValueModel::whereStoreId($store->id)
                    ->where('custom_field_id', '=', $this->id)
                    ->first();

                if (!$has_custom_value)
                {
                    // Create default value for each product
                    $custom_value = new CustomValueModel();
                    $custom_value->store_id = $store->id;
                    $custom_value->custom_field_id = $this->id;
                    $custom_value->value = $this->default_value;
                    $custom_value->save();
                    
                    // Create relation between custom value and custom field if not exists
                    $relation = DB::table('tiipiik_catalog_csf_csv')
                        ->whereCustomValueId($custom_value->id)
                        ->whereCustomFieldId($this->id)
                        ->first();
                    
                    if (! $relation)
                    {
                        DB::insert('insert into tiipiik_catalog_csf_csv (custom_value_id, custom_field_id) values ("'.$custom_value->id.'", "'.$this->id.'")');
                    }
                }
            });
        }
        
        // Delete fields from non selected groups if exists
        $products = ProductModel::with('customfields')->get();
        
        $products->each(function($product)
        {
            $group = $product->group_id;
            
            foreach ($product->customfields as $custom_field)
            {                
                // Does this custom field belongs to the group related to the product ?
                $relation = DB::table('tiipiik_catalog_group_field')
                    ->where('custom_field_id', $custom_field->custom_field_id)
                    ->where('group_id', $group)
                    ->first();
                
                // There's no relation so we have to remove this custom field and it's custom value
                if (!$relation)
                {
                    // Delete custom value  
                    CustomValueModel::whereProductId($product->id)
                        ->whereCustomFieldId($custom_field->custom_field_id)
                        ->delete();
                }
            }
        });
        
        // Delete fields from non selected groups if exists
        $stores = StoreModel::with('customfields')->get();
        
        $stores->each(function($store)
        {
            $group = $store->group_id;
            
            foreach ($store->customfields as $custom_field)
            {                
                // Does this custom field belongs to the group related to the product ?
                $relation = DB::table('tiipiik_catalog_group_field')
                    ->where('custom_field_id', $custom_field->custom_field_id)
                    ->where('group_id', $group)
                    ->first();
                
                // There's no relation so we have to remove this custom field and it's custom value
                if (!$relation)
                {
                    // Delete custom value  
                    CustomValueModel::whereStoreId($store->id)
                        ->whereCustomFieldId($custom_field->custom_field_id)
                        ->delete();
                }
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
            $custom_values = CustomValueModel::whereProductId($product->id)
                ->whereCustomFieldId($this->id)
                ->get();
            
            $custom_values->each(function($custom_value)
            {
                // Delete relation
                $relation = DB::table('tiipiik_catalog_csf_csv')
                    ->where('custom_value_id', '=', $custom_value->id)
                    ->delete();
                
                // Delete custom value
                CustomValueModel::whereId($custom_value->id)->delete();
            });
        });
        
        // then grab all stores
        $stores = StoreModel::all();
        
        $stores->each(function($store)
        {
            // Find the related custom value
            $custom_values = CustomValueModel::whereStoreId($store->id)
                ->whereCustomFieldId($this->id)
                ->get();
            
            $custom_values->each(function($custom_value)
            {
                // Delete relation
                $relation = DB::table('tiipiik_catalog_csf_csv')
                    ->where('custom_value_id', '=', $custom_value->id)
                    ->delete();
                
                // Delete custom value
                CustomValueModel::whereId($custom_value->id)->delete();
            });
        });
        
    }

}