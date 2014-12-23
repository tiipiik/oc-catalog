<?php namespace Tiipiik\Catalog\Models;

use DB;
use App;
use Model;
use Tiipiik\Catalog\Models\CustomField as CustomFieldModel;

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
        'customfields' => ['Tiipiik\Catalog\Models\CustomValue', 'order' => 'csfield_id']
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
    public function listFrontEnd($options)
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

        App::make('paginator')->setCurrentPage($page);
        $obj = $this->newQuery();
        $obj->where('category_id', '=', $category);
            
        return $obj->paginate($perPage);
    }
    
    /*
     * Add existing custom fields to newly created product
     */
    public function afterCreate()
    {
        // Get all custom fields
        $customFields = CustomFieldModel::all();
        
        // Add to product
        $this->customfields = [
            '' => '',
        ];
    }
    
    /*
     * Delete all relations before deleting product
     */
    public function beforeDelete()
    {
        
    }
}