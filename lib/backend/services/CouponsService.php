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

namespace backend\services;

use common\models\Coupons;
use common\models\repositories\CouponRepository;


class CouponsService
{

    /** @var CouponsRepository */
    private $couponsRepository;

    public function __construct(CouponRepository $couponsRepository)
    {
        $this->couponsRepository = $couponsRepository;
    }

    public function setActive(Coupons $coupon)
    {
        if(!is_object($coupon)){
            throw new \RuntimeException('Coupon error data.');
        }
        if($coupon->coupon_active === Coupons::STATUS_ACTIVE){
            return true;
        }
        return $this->couponsRepository->edit($coupon,['coupon_active' => Coupons::STATUS_ACTIVE]);
    }

    public function setDisable(Coupons $coupon)
    {
        if(!is_object($coupon)){
            throw new \RuntimeException('Coupon error data.');
        }
        if($coupon->coupon_active === Coupons::STATUS_DISABLE){
            return true;
        }
        return $this->couponsRepository->edit($coupon,['coupon_active' => Coupons::STATUS_DISABLE]);
    }

    public function getById(int $id)
    {
        return $this->couponsRepository->getById($id);
    }

}