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

namespace common\services;


use common\classes\Currencies;
use common\models\Inventory;
use common\models\repositories\InventoryRepository;

final class InventoryService
{
    /** @var InventoryRepository */
    private $inventoryRepository;

    /**
     * InventoryService constructor.
     * @param InventoryRepository $inventoryRepository
     */
    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * @param string $sku
     * @param bool $nonExistent
     * @param bool $withProduct
     * @param bool $asArray
     * @return array|Inventory[]
     */
    public function findByModel(string $sku, bool $nonExistent = true, bool $withProduct = false, bool $asArray = false): array
    {
        return $this->inventoryRepository->findByModel($sku, $nonExistent, $withProduct, $asArray);
    }

    /**
     * @param Inventory $inventory
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function editInventory(Inventory $inventory, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        return $this->inventoryRepository->edit($inventory, $params, $validation, $safeOnly);
    }

    /**
     * @param string $uPrId
     * @return array
     */
    public function uPrIdToParams(string $uPrId): array
    {
        $result = [
            'prId' => null,
            'uPrId' => null,
            'attributes' => null,
        ];
        if (preg_match("/^\d+$/", $uPrId)) {
            $result['prId'] = (int)$uPrId;
            return $result;
        }
        if ((int)$uPrId > 0) {
            $result['prId'] = (int)$uPrId;
        }
        preg_match_all('/(\{([\d]+)\})([\d]+)/', $uPrId, $arr);
        if (isset($arr[2], $arr[3]) && count($arr[2]) === count($arr[3])) {
            $result['uPrId'] = $uPrId;
            foreach ($arr[2] as $key => $value) {
                $result['attributes'][(int)$value] = (int)$arr[3][$key];
            }
            return $result;
        }
        return [];
    }

    /**
     * @param int $pId
     * @param array $attributes
     * @return int|string
     */
    public function getUId(int $pId, array $attributes = [])
    {
        if (empty($attributes)) {
            return $pId;
        }
        ksort($attributes);
        $uPId = (string)$pId;
        foreach ($attributes as $key => $value) {
            $uPId .= "{{$key}}{$value}";
        }
        return $uPId;
    }

    /**
     * @param int $productId
     * @param Currencies $currencies
     * @return array
     */
    public static function getInventoryInfoForListing(int $productId, Currencies $currencies): array
    {
        //$inventoryService = \Yii::createObject(InventoryService::class);
        $details = \common\helpers\Inventory::getDetails($productId, '');
        /** @var $products \common\components\ProductsContainer * */
        $products = Yii::$container->get('products');
        if (!$details || empty($details['inventory_array'])) {
            return [];
        }
        foreach ($details['inventory_array'] as $uPrId => $inventory) {
            $product = $products->getProduct((int)$uPrId);
            $details['inventory_array'][$uPrId] = array_merge(
                $details['inventory_array'][$uPrId],
                self::getInventoryPrice($uPrId, $currencies, 1, $product['products_tax_class_id'])
            );
        }
        return $details['inventory_array'];
    }

    /**
     * @param string $uPrId
     * @param Currencies $currencies
     * @param int $qty
     * @param int|null $productTaxClassId
     * @return array
     */
    public static function getInventoryPrice(string $uPrId, Currencies $currencies, int $qty = 1, ?int $productTaxClassId = null): array
    {
        $priceInstance = \common\models\Product\Price::getInstance($uPrId);
        $product_price = $priceInstance->getInventoryPrice(['qty' => $qty]);
        $special_price = $priceInstance->getInventorySpecialPrice(['qty' => $qty]);
        return [
            'product_valid' => 1,
            'link' => tep_href_link('catalog/product', 'products_id=' . $uPrId),
            'product_price' => $currencies->display_price($product_price, \common\helpers\Tax::get_tax_rate($productTaxClassId), 1, ($special_price === false ? true : '')),
            'product_unit_price' => $currencies->display_price_clear($product_price, 0),
            'tax' => \common\helpers\Tax::get_tax_rate($productTaxClassId),
            'special_price' => $special_price !== false ? $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($productTaxClassId), 1, true) : '',
            'special_unit_price' => $special_price !== false ? $currencies->display_price_clear($special_price, 0) : '',
            'product_in_cart' => \frontend\design\Info::checkProductInCart($uPrId),
            'current_uprid' => $uPrId,
        ];
    }
}
