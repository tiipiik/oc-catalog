<?php namespace Tiipiik\Catalog\Models;

use DB;
use App;
use Model;
use Tiipiik\Catalog\Models\CustomField as CustomFieldModel;
use Tiipiik\Catalog\Models\CustomValue as CustomValueModel;

/**
 * Product Model
 */
class Product extends Model
{
    use \October\Rain\Database\Traits\Validation;

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
        'price' => 'required',
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

        App::make('paginator')->setCurrentPage($page);
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
            
        return $obj->paginate($perPage);
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
        // Get all custom fields
        $customFields = CustomFieldModel::all();
        
        $customFields->each(function($customField)
        {
            // Add to product as custom value, with default value
            $customValue = CustomValueModel::create([
                'product_id' =>$this->id,
                'custom_field_id' => $customField->id,
                'value' => $customField->default_value,
            ]);
                        
            // Create relation between custom value and custom field for this product
            DB::insert('insert into tiipiik_catalog_csf_csv (custom_value_id, custom_field_id) values ("'.$customValue->id.'", "'.$customField->id.'")');
        });
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
}