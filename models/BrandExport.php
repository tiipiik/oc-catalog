<?php namespace Tiipiik\Catalog\Models;

/**
 * BrandExport Model
 */
class BrandExport extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        $brands = Brand::all();
        $brands->each(function ($brand) use ($columns) {
            $brand->addVisible($columns);
        });
        return $brands->toArray();
    }
}
