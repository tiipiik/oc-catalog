<?php namespace Tiipiik\Catalog\Models;

use DB;
use App;
use Model;
use Tiipiik\Catalog\Models\CustomField as CustomFieldModel;
use Tiipiik\Catalog\Models\CustomValue as CustomValueModel;
use Tiipiik\Catalog\Models\Group;

use SystemException;

/**
 * Store Model
 */
class Store extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    private static $store_group;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_stores';

    /**
     * Validation rules
     */
    public $rules = [
        'name' => 'required|unique:tiipiik_catalog_stores',
        'slug' => 'required|unique:tiipiik_catalog_stores',
        'group' => 'required',
    ];

    /**
     * @var array Translatable fields
     */
    public $translatable = ['name', 'description'];

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
    public $belongsTo = [
        'group' => ['Tiipiik\Catalog\Models\Group'],
    ];
    
    public $hasMany = [
        'customfields' => ['Tiipiik\Catalog\Models\CustomValue', 'order' => 'custom_field_id'],
    ];
    
    public $belongsToMany = [
        'products' => ['Tiipiik\Catalog\Models\Product',
        'table' => 'tiipiik_catalog_products_stores',
        'order' => 'title'],
    ];
    
    public $attachOne = [
        'cover_image' => ['System\Models\File', 'delete' => true],
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
        if (!class_exists('RainLab\Translate\Behaviors\TranslatableModel')) {
            return;
        }

        // Extend the constructor of the model
        self::extend(function ($model) {

            // Implement the translatable behavior
            $model->implement[] = 'RainLab.Translate.Behaviors.TranslatableModel';

        });
    }
    
    /**
     * Lists stores for the front end
     * @param  array $options Display options
     * @return self
     */
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
        ], $options));

        $obj = $this->newQuery();
        $obj = $obj->whereIsActivated(1);

        return $obj->paginate($perPage, $page);
    }
    
    /*
     * Add existing custom fields to newly created stores
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
        $store = self::find($this->id);
        self::$store_group = $store->group_id;
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
        $customValues = CustomValueModel::where('store_id', '=', $this->id)->get();
        
        $customValues->each(function ($value) {
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
        if ($context == 'update' && self::$store_group != $this->group_id) {
            CustomValueModel::whereStoreId($this->id)->delete();
        }
        
        // Get custom fields from group
        $custom_field_ids = DB::table('tiipiik_catalog_group_field')
            ->where('group_id', '=', $this->group_id)
            ->get();
        
        if ($custom_field_ids) {
            foreach ($custom_field_ids as $custom_field_id) {
                $field_exists = CustomValueModel::whereStoreId($this->id)
                    ->whereCustomFieldId($custom_field_id->custom_field_id)
                    ->first();
                
                if (!$field_exists) {
                    $custom_fields = CustomFieldModel::whereId($custom_field_id->custom_field_id)->get();
                    
                    $custom_fields->each(function ($custom_field) {
                        // Add to product as custom value, with default value
                        $custom_value = new CustomValueModel();
                        $custom_value->store_id = $this->id;
                        $custom_value->custom_field_id = $custom_field->id;
                        $custom_value->value = $custom_field->default_value;
                        $custom_value->save();
                                    
                        // Create relation between custom value and custom field for this product
                        DB::insert(
                            'insert into tiipiik_catalog_csf_csv (custom_value_id, custom_field_id)
                            values ("'.$custom_value->id.'", "'.$custom_field->id.'")'
                        );
                    });
                }
            }
        }
    }
}
