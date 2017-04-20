<?php
namespace Tiipiik\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Categories Back-end Controller
 */
class Categories extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController',

    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = ['tiipiik.catalog.manage_categories'];

    // public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'categories');
    }

    public function reorder()
    {
        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'reorder');

        $this->asExtension('ReorderController')->reorder();
    }
}
