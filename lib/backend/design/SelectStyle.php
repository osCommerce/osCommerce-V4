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

namespace backend\design;

use common\models\ThemesStylesMain;
use Yii;
use yii\base\Widget;

class SelectStyle extends Widget
{
    public $name;
    public $value;
    public $type;
    public $theme_name;

    public function init(){
        parent::init();
    }

    public function run()
    {
        $styles = ThemesStylesMain::find()->where([
            'theme_name' => $this->theme_name,
            'type' => $this->type
        ])->asArray()->all();

        return $this->render('select-style.tpl', [
            'name' => $this->name,
            'value' => $this->value,
            'type' => $this->type,
            'styles' => $styles,
            'theme_name' => $this->theme_name,
        ]);
    }
}