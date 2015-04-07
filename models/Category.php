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
    public $translatable = ['name', 'description'];
    
    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];
    
    public $belongsToMany = [
        'products' => ['Tiipiik\Catalog\Models\Product', 'table' => 'tiipiik_catalog_prods_cats', 'order' => 'title'],
    ];

    public $attachOne = [
        'cover' => ['System\Models\File']
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
     *
     */
    public function getCategoriesOptions()
    {
        return $this->orderBy('name')->lists('name', 'id');
    }
    
    /*
     * Return the number of product for given category
     */
    public function getProductCountAttribute()
    {
        return $this->products()->whereIsPublished(1)->count();
    }

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }
    
    public static function categoryDetails($param)
    {
        if (!$category = self::whereSlug($param['category'])->first())
            return null;
        
        return $category;
    }

}