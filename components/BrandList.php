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
            'name'        => 'Brand List',
            'description' => 'Display a list of brands'
        ];
    }

    public function defineProperties()
    {
        return [
            'brandPage' => [
                'title'       => 'Page for brand details',
                'description' => 'Define witch page to use to display brand details',
                'type'        => 'dropdown',
                'default'     => 'stores/:slug',
            ],
            'brandSlug' => [
                'title'       => 'Brand slug param',
                'description' => 'Used to generate links to brand details page',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'noBrandMessage' => [
                'title'        => 'No brand message',
                'description'  => '',
                'type'         => 'string',
                'default'      => 'No brand found',
            ],
            'brandsPerPage' => [
                'title'             => 'Brands per page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => '',
                'default'           => '9',
                'group'             => 'Pagination',
            ],
            'pageParam' => [
                'title'       => 'Page param',
                'description' => '',
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
