<?php namespace Tiipiik\Catalog\Models;

use DB;
use App;
use Model;
use Tiipiik\Catalog\Models\CustomField as CustomFieldModel;
use Tiipiik\Catalog\Models\CustomValue as CustomValueModel;
use Tiipiik\Catalog\Models\Group;
use SystemException;

class Product extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    private static $product_group;
    private static $brand;

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
        'price' => 'required|regex:/^(0+)?\d{0,10}(\.\d{0,2})?$/',
        'discount_price' => 'regex:/^(0+)?\d{0,10}(\.\d{0,2})?$/',
    ];
    
    public $customMessages = [
        'price.regex' => 'tiipiik.catalog::lang.validation.price_regex',
        'discount_price.regex' => 'tiipiik.catalog::lang.validation.discount_price_regex',
    ];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    //protected $dates = ['published_at'];

    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = array(
        'title asc' => 'Title (ascending)',
        'title desc' => 'Title (descending)',
        'created_at asc' => 'Created (ascending)',
        'created_at desc' => 'Created (descending)',
        'updated_at asc' => 'Updated (ascending)',
        'updated_at desc' => 'Updated (descending)',
        //'published_at asc' => 'Published (ascending)',
        //'published_at desc' => 'Published (descending)',
        'random' => 'Random'
    );

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
        'brand' => ['Tiipiik\Catalog\Models\Brand'],
    ];
    
    public $hasMany = [
        'customfields' => ['Tiipiik\Catalog\Models\CustomValue', 'order' => 'custom_field_id'],
    ];
    
    public $belongsToMany = [
        'categories' => [
            'Tiipiik\Catalog\Models\Category',
            'table' => 'tiipiik_catalog_prods_cats',
            'order' => 'name'
        ],
        'stores' => [
            'Tiipiik\Catalog\Models\Store',
            'table' => 'tiipiik_catalog_products_stores',
            'order' => 'name',
        ],
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
     * Lists products for the front end
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
            'categories' => null,
            'brand' => null,
        ], $options));

        $searchableFields = ['title', 'slug', 'description'];

        $obj = $this->newQuery();
        $obj = $obj->whereIsPublished(1);

        /*
         * Sorting
         */
        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $_sort) {

            if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {
                $parts = explode(' ', $_sort);
                if (count($parts) < 2) {
                    array_push($parts, 'desc');
                }
                list($sortField, $sortDirection) = $parts;
                if ($sortField == 'random') {
                    $sortField = DB::raw('RAND()');
                }
                $obj->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Categories
         */
        if ($categories !== null) {
            if (!is_array($categories)) {
                $categories = [$categories];
            }
            $obj = $obj->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        /*
         * Brand
         */
        if ($brand !== null) {
            if (!is_array($brand)) {
                $brand = [$brand];
            }
            $obj = $obj->whereHas('brand', function ($q) use ($brand) {
                $q->whereIn('id', $brand);
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
        return $query->whereHas('categories', function ($q) use ($categories) {
            $q->whereIn('id', $categories);
        });
    }

    /**
     * Allows filtering for specifc groups
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $groups List of group ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterGroups($query, $groups)
    {
        return $query->whereHas('group', function ($q) use ($groups) {
            $q->whereIn('id', $groups);
        });
    }

    /**
     * Allows filtering for specifc brands
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $brands List of brand ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterBrands($query, $brands)
    {
        return $query->whereHas('brand', function ($q) use ($brands) {
            $q->whereIn('id', $brands);
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
        if ($context == 'update' && self::$product_group != $this->group_id) {
            CustomValueModel::whereProductId($this->id)->delete();
        }
                    
        // Get custom fields from group
        $custom_field_ids = DB::table('tiipiik_catalog_group_field')
            ->where('group_id', '=', $this->group_id)
            ->get();
        
        if ($custom_field_ids) {
            foreach ($custom_field_ids as $custom_field_id) {
                $field_exists = CustomValueModel::whereProductId($this->id)
                    ->whereCustomFieldId($custom_field_id->custom_field_id)
                    ->first();
                
                if (!$field_exists) {
                    $custom_fields = CustomFieldModel::whereId($custom_field_id->custom_field_id)->get();
                    
                    $custom_fields->each(function ($custom_field) {
                        // Add to product as custom value, with default value
                        $custom_value = CustomValueModel::create([
                            'product_id' => $this->id,
                            'custom_field_id' => $custom_field->id,
                            'value' => $custom_field->default_value,
                        ]);
                                    
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

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id' => $this->id,
            'slug' => $this->slug,
        ];

        if (array_key_exists('categories', $this->getRelations())) {
            $params['category'] = $this->categories->count() ? $this->categories->first()->slug : null;
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }
}
