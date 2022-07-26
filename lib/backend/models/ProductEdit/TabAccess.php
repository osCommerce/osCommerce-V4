<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace backend\models\ProductEdit;


class TabAccess
{
    protected $subProduct = false;
    protected $supplierDataAllowed = true;

    public function setProduct($product)
    {
        if ( is_object($product) && $product->parent_products_id ) {
            $this->subProduct = true;
            $this->supplierDataAllowed = $product->parent_products_id!=$product->products_id_stock;
        }
    }

    public function checkSubProductTabs($tabCode)
    {
        $allowedForSubProducts = [
                'TEXT_NAME_DESCRIPTION',
                'TEXT_MAIN_DETAILS',
                'TAB_PROPERTIES',
                'TAB_IMAGES',
                'TEXT_VIDEO',
                'TEXT_SEO',
                'TEXT_MARKETING',
                'TAB_DOCUMENTS',
                'TAB_IMPORT_EXPORT',
                'TAB_NOTES',
        ];
        if (true) {
            $allowedForSubProducts[] = 'TEXT_PRICE_COST_W';
            $allowedForSubProducts[] = 'TEXT_ATTR_INVENTORY';
        }
        return in_array($tabCode, $allowedForSubProducts);
    }

    public function isSubProduct()
    {
        return $this->subProduct;
    }

    public function allowSuppliersData()
    {
        return $this->supplierDataAllowed;
    }

    public function tabDataSave($tabCode)
    {
        if ( $this->subProduct && !$this->checkSubProductTabs($tabCode)) {
            return false;
        }
        return \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT', $tabCode]);
    }

    public function tabView($tabCode)
    {
        if ( $this->subProduct && !$this->checkSubProductTabs($tabCode)) {
            return false;
        }
        return \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT', $tabCode]);
    }


}
