<?php namespace Tiipiik\Catalog\Models;

use DB;
use App;
use Model;
use Tiipiik\Catalog\Models\CustomField as CustomFieldModel;
use Tiipiik\Catalog\Models\CustomValue as CustomValueModel;
use Tiipiik\Catalog\Models\Group;

//use System\Classes\SystemException;
// throw new SystemException('Message');


class Product extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    private static $product_group;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_products';

    /**
     * Validation rules
     */
    public $rules = [
        'title' => 'required|unique:tiipiik_catalog_products',
        'slug' => 'required',
        'price' => 'required|integer',
        'discount_price' => 'integer',
    ];

    /**
     * @var array Translatable fields
     */
    public $translatable = ['title', 'description'];

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
    public $attachMany = [
        'featured_images' => ['System\Models\File'],
    ];
    
    public $belongsTo = [
        'group' => ['Tiipiik\Catalog\Models\Group'],
    ];
    
    public $hasMany = [
        'customfields' => ['Tiipiik\Catalog\Models\CustomValue', 'order' => 'custom_field_id'],
    ];
    
    public $belongsToMany = [
        'categories' => ['Tiipiik\Catalog\Models\Category', 'table' => 'tiipiik_catalog_prods_cats', 'order' => 'name'],
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
     * Lists rooms for the front end
     * @param  array $options Display options
     * @return self
     */ 
    //public function listFrontEnd($options)
    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'page' => 1,
            'perPage' => 30,
            'sort' => 'title',
            'search' => '',
            'categories' => null
        ], $options));

        $obj = $this->newQuery();

        /*
         * Categories
         */
        if ($categories !== null) {
            if (!is_array($categories)) $categories = [$categories];
            $obj = $obj->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }
            
        return $obj->paginate($perPage, $page);
    }

    /**
     * Allows filtering for specifc categories
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $categories List of category ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterCategories($query, $categories)
    {
        return $query->whereHas('categories', function($q) use ($categories) {
            $q->whereIn('id', $categories);
        });
    }
    
    /*
     * Add existing custom fields to newly created product
     */
    public function afterCreate()
    {
        self::updateCustomFieldsAndValues('create');
    }
    
    /*
     * Get group before update to handle group change
     */
    public function beforeUpdate()
    {
        $product = self::find($this->id);
        self::$product_group = $product->group_id;    
    }
    
    
    public function afterUpdate()
    {   
        self::updateCustomFieldsAndValues('update');
    }
    
    /*
     * Delete all relations before deleting product
     */
    public function beforeDelete()
    {
        // Find the related custom value
        $customValues = CustomValueModel::where('product_id', '=', $this->id)->get();
        
        $customValues->each(function($value)
        {
            // Delete relation
            $relation = DB::table('tiipiik_catalog_csf_csv')
                ->where('custom_value_id', '=', $value->id)
                ->delete();
                
            // Delete custom value
            CustomValueModel::find($value->id)->delete();
        });
    }
    
    
    public function updateCustomFieldsAndValues($context)
    {
        // If updating, delete fields and values only if group has changed
        if ($context == 'update' && self::$product_group != $this->group_id)
        {
            CustomValueModel::whereProductId($this->id)->delete();
        }
                    
        // Get custom fields from group
        $custom_field_ids = DB::table('tiipiik_catalog_group_field')->where('group_id', '=', $this->group_id)->get();
        
        if ($custom_field_ids)
        {
            foreach ($custom_field_ids as $custom_field_id)
            {
                $field_exists = CustomValueModel::whereProductId($this->id)
                    ->whereCustomFieldId($custom_field_id->custom_field_id)
                    ->first();
                
                if (!$field_exists)
                {
                    $custom_fields = CustomFieldModel::whereId($custom_field_id->custom_field_id)->get();
                    
                    $custom_fields->each(function($custom_field)
                    {
                        // Add to product as custom value, with default value
                        $custom_value = CustomValueModel::create([
                            'product_id' =>$this->id,
                            'custom_field_id' => $custom_field->id,
                            'value' => $custom_field->default_value,
                        ]);
                                    
                        // Create relation between custom value and custom field for this product
                        DB::insert('insert into tiipiik_catalog_csf_csv (custom_value_id, custom_field_id) values ("'.$custom_value->id.'", "'.$custom_field->id.'")');
                    });
                }
            }
        }
    }
}