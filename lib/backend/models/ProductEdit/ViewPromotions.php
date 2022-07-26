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

use common\helpers\Translation;

class ViewPromotions
{
    /**
     * @var \objectInfo
     */
    protected $productInfoRef;

    public function __construct($productInfo)
    {
        $this->productInfoRef = $productInfo;
    }

    public function populateView($view)
    {
        $view->assigned_promotions = [];
        if (\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
            Translation::init('admin/promotions');

            $pInfo = $this->productInfoRef;
            $list = \common\models\promotions\PromotionService::getProductPromoList($pInfo->products_id);
            foreach ( $list as $promo){
                $view->assigned_promotions[] = [
                    'promo_id' => $promo->promo_id,
                    'promo_priority' => $promo->promo_priority,
                    'label' => $promo->textDescription->promo_label,
                    'status' => $promo->promo_status,
                    'date_start' => $promo->promo_date_start>2000?\common\helpers\Date::datetime_short($promo->promo_date_start):'',
                    'date_expired' => $promo->promo_date_expired>2000?\common\helpers\Date::datetime_short($promo->promo_date_expired):'',
                ];
            }
        }
    }
}