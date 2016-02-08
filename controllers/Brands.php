<?php namespace Tiipiik\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Brands Back-end Controller
 */
class Brands extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['tiipiik.catalog.manage_brands'];

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'brands');
    }
}
