<?php
namespace Tiipiik\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Flash;
use Tiipiik\Catalog\Models\Brand;

/**
 * Brands Back-end Controller
 */
class Brands extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ImportExportController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public $requiredPermissions = ['tiipiik.catalog.manage_brands'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'brands');
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

            foreach ($checkedIds as $brandId) {
                if ((!$brand = Brand::find($brandId))) {
                    continue;
                }

                $brand->delete();
            }

            Flash::success(e(trans('tiipiik.catalog::lang.brands.delete_success')));
        }

        return $this->listRefresh();
    }

    public function index_onActivate()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $itemId) {
                if ((!$item = Brand::find($itemId))) {
                    continue;
                }

                $item->published = true;
                $item->save();
            }

            Flash::success(e(trans('tiipiik.catalog::lang.brands.activate_success')));
        }

        return $this->listRefresh();
    }

    public function index_onDeactivate()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $itemId) {
                if ((!$item = Brand::find($itemId))) {
                    continue;
                }

                $item->published = false;
                $item->save();
            }

            Flash::success(e(trans('tiipiik.catalog::lang.brands.deactivate_success')));
        }

        return $this->listRefresh();
    }
}
