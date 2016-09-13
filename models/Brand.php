<?php namespace TiipiiK\Catalog\Models;

use Model;

/**
 * Brand Model
 */
class Brand extends Model
{
    use \October\Rain\Database\Traits\Validation;
    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_brands';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name', 'slug', 'description', 'published'];

    public $translatable = ['name', 'description'];
    
    public $rules = [
        'name' => ['required', 'unique:tiipiik_catalog_brands'],
        'slug' => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:tiipiik_catalog_brands'],
    ];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['published_at'];

    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = array(
        'name asc' => 'Name (ascending)',
        'name desc' => 'Name (descending)',
        'created_at asc' => 'Created (ascending)',
        'created_at desc' => 'Created (descending)',
        'updated_at asc' => 'Updated (ascending)',
        'updated_at desc' => 'Updated (descending)',
        //'published_at asc' => 'Published (ascending)',
        //'published_at desc' => 'Published (descending)',
        'random' => 'Random'
    );

    /**
     * @var array Relations
     */
    public $attachOne = [
        'cover_image' => ['System\Models\File', 'order' => 'sort_order', 'delete' => true],
    ];
    
    public $hasMany = [
        'products' => ['Tiipiik\Catalog\Models\Product'],
    ];

     /**
     * Add translation support to this model, if available.
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        if (!class_exists('RainLab\Translate\Behaviors\TranslatableModel')) {
            return;
        }

        self::extend(function ($model) {
            $model->implement[] = 'RainLab.Translate.Behaviors.TranslatableModel';
        });
    }

    /**
     * Lists brands for the front end
     * @param  array $options Display options
     * @return self
     */
    public function scopeListFrontEnd($query, $options)
    {
        extract(array_merge([
            'page' => 1,
            'perPage' => 30,
            'sort' => 'name',
            'search' => '',
        ], $options));

        $obj = $this->newQuery();
        $obj = $obj->wherePublished(1);

        return $obj->paginate($perPage, $page);
    }

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = ['slug' => $this->slug];

        return $this->url = $controller->pageUrl($pageName, $params);
    }
}
