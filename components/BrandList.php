<?php namespace Tiipiik\Catalog\Components;

use Request;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Brand;

class BrandList extends ComponentBase
{
    public $brands;
    public $brandPage;
    public $brandSlug;
    public $noBrandMessage;

    public function componentDetails()
    {
        return [
            'name'        => 'tiipiik.catalog::lang.component.brand_list.name',
            'description' => 'tiipiik.catalog::lang.component.brand_list.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'brandPage' => [
                'title'       => 'tiipiik.catalog::lang.component.brand_list.param.brand_page_title',
                'description' => 'tiipiik.catalog::lang.component.brand_list.param.brand_page_desc',
                'type'        => 'dropdown',
                'default'     => 'stores/:slug',
            ],
            'brandSlug' => [
                'title'       => 'tiipiik.catalog::lang.component.brand_list.param.brand_slug_title',
                'description' => 'tiipiik.catalog::lang.component.brand_list.param.brand_slug_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'noBrandMessage' => [
                'title'        => 'tiipiik.catalog::lang.component.brand_list.param.no_brand_message_title',
                'description'  => 'tiipiik.catalog::lang.component.brand_list.param.no_brand_message_desc',
                'type'         => 'string',
                'default'      => 'tiipiik.catalog::lang.component.brand_list.param.no_brand_message_default',
            ],
            'brandsPerPage' => [
                'title'             => 'tiipiik.catalog::lang.component.brand_list.param.brands_per_page_title',
                'description'       => 'tiipiik.catalog::lang.component.brand_list.param.brands_per_page_desc',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => '',
                'default'           => '9',
                'group'             => 'Pagination',
            ],
            'pageParam' => [
                'title'       => 'tiipiik.catalog::lang.component.brand_list.param.page_param_title',
                'description' => 'tiipiik.catalog::lang.component.brand_list.param.page_param_desc',
                'type'        => 'string',
                'default'     => '{{ :page }}',
                'group'       => 'Pagination',
            ],
        ];
    }

    public function getBrandPageOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        // Use strict method only to avoid conflicts whith other plugins
        $this->brandPage = $this->property('brandPage');
        
        $currentPage = post('page');
        $brands = $this->brands = $this->listBrands();
        $this->brands = $this->listBrands();
        
        /*
         * Pagination
         */
        if ($brands) {
            $queryArr = [];
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            if ($currentPage > ($lastPage = $brands->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }

            $this->page['paginationUrl'] = $paginationUrl;
        }
        
        $this->noBrandsMessage = $this->page['noBrandsMessage'] = $this->property('noBrandMessage');
        $this->brandSlug = $this->property('brandSlug');
    }

    public function listBrands()
    {
        $brands = Brand::listFrontEnd([
            'page' => $this->property('brandSlug'),
            'perPage' => $this->property('brandsPerPage'),
        ]);
        
        return $brands;
    }
}
