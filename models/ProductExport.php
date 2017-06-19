<?php namespace Tiipiik\Catalog\Models;

/**
 * ProductExport Model
 */
class ProductExport extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        $products = Product::all();
        $products->each(function ($product) use ($columns) {
            $product->addVisible($columns);
        });
        return $products->toArray();
    }
}
