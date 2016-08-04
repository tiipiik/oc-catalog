<?php namespace Tiipiik\Catalog\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Tiipiik\Catalog\Models\Product;

/**
 * Products Back-end Controller
 */
class Products extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
        'Backend.Behaviors.ImportExportController',
        'Backend.Behaviors.ReorderController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $importExportConfig = 'config_import_export.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = ['tiipiik.catalog.manage_products'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'products');
    }

    public function create()
    {
        $this->bodyClass = 'compact-container';

        return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        $this->bodyClass = 'compact-container';

        return $this->asExtension('FormController')->update($recordId);
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $itemId) {
                if ((!$item = Product::find($itemId))) {
                    continue;
                }

                $item->delete();
            }

            Flash::success(e(trans('tiipiik.catalog::lang.products.delete_success')));
        }

        return $this->listRefresh();
    }
}
