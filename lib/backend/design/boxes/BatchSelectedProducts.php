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

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class BatchSelectedProducts extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $sortingOptions = \common\helpers\Sorting::getPossibleSortOptions();
        $sortingOptions[''] = TEXT_RANDOM;
        $sorting = \common\helpers\Html::dropDownList('setting[0][sort_order]',
            $this->settings[0]['sort_order'],
            $sortingOptions,
            ['class' => 'form-control']);

        return $this->render('batch-selected-products.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'sorting' => $sorting,
        ]);
    }
}