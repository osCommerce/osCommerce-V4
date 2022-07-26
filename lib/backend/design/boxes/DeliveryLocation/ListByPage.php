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

namespace backend\design\boxes\DeliveryLocation;

use common\extensions\DeliveryLocation\helpers\DeliveryLocationHelper;
use Yii;
use yii\base\Widget;

class ListByPage extends Widget
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
        $pagesTree = DeliveryLocationHelper::getTree(1);

        return $this->render('../../views/delivery-location-list-by-page.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'pagesTree' => $pagesTree,
        ]);
    }
}