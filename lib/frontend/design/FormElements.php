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

namespace frontend\design;

use Yii;
use common\classes\Images;
use backend\design\Style;

class FormElements
{

    public static function radioButton()
    {
        return [
            'item' => function($index, $label, $name, $checked, $value) {
                return '
    <label class="radio-button">
        <input type="radio" name="' . $name . '" value="' . $value . '"' . ($checked ? 'checked' : '') . '>
        <span>' . $label . '</span>
    </label>';
            }
        ];
    }
}

