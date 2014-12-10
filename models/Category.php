<?php namespace Tiipiik\Catalog\Models;

use Model;

/**
 * Category Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\NestedTree;
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_categories';

    /**
     * Validation rules
     */
    public $rules = [
        'name' => 'required|unique:tiipiik_catalog_categories',
        'slug' => 'required',
    ];

    /**
     * @var array Translatable fields
     */
    public $translatable = ['title', 'description'];
    
    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    public $belongsToMany = [
        'products' => ['Tiipiik\Catalog\Models\Product', 'table' => 'tiipiik_catalog_prods_cats', 'order' => 'name'],
    ];

    public $attachOne = [
        'category_image'    =>  ['System\Models\File']
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
     * From Benefreke MenuManager plugin
     * Returns the list of menu items, where the key is the id and the value is the title, indented with '-' for depth
     * @return array
     */
    public function getSelectList()
    {
        $items  = $this->getAll();
        $output = array();
        foreach ($items as $item) {
            $depthIndicator         = $this->getDepthIndicators($item->nest_depth);
            $output["id-$item->id"] = $depthIndicator . ' ' . $item->title;
        }
        return $output;
    }

    /**
     * From Benefreke MenuManager plugin
     * Recursively adds depth indicators, a '-', to a string
     *
     * @param int    $depth
     * @param string $indicators
     *
     * @return string
     */
    protected function getDepthIndicators($depth = 0, $indicators = '')
    {
        if ($depth < 1) {
            return $indicators;
        }
        return $this->getDepthIndicators(--$depth, $indicators . '-');
    }
    
    /*
     *
     */
    public function getCategoriesOptions()
    {
        return $this->orderBy('name')->lists('name', 'id');
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
            'sort' => 'name',
            'search' => '',
        ], $options));

        App::make('paginator')->setCurrentPage($page);
        $obj = $this->newQuery();
        
        return $obj->paginate($perPage);
    }
    
    public function category($param)
    {
        $category = Category::where('slug', '=', $param)->first();
        
        return $category;
    }

}