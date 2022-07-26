<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\helpers;

use Yii;

class Cart
{
 
    public static function decodeId($cartId)
    {
        if (preg_match("/(.*)\|([\d]*)\-([\d]*)/", $cartId, $mas)) {
            return ['cart_type' => $mas[1], 'customers_id' => $mas[2], 'basket_id' => $mas[3]];
        }
    }
}
