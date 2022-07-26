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

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Totals extends Widget {

    public $id;
    public $file;
    public $params;
    public $settings;

    public function init() {
        parent::init();
    }

    public function run() {
        
        /*to get correct settings */
        if ($this->params['order']->manager->getPlatformId() != $this->params['order']->info['platform_id']){
            $this->params['order']->manager->set('platform_id', $this->params['order']->info['platform_id']);
        }
        
        $order_total_output = $this->params['order']->manager->wrapTotals($this->params['order']->totals, 'TEXT_INVOICE');

        return $this->renderFile(Yii::$aliases['@frontend']. '/themes/basic/boxes/invoice/totals.tpl', [
                'order' => $this->params['order'],
                'order_total_output' => $order_total_output,
                'currencies' => $this->params['currencies'],
                'to_pdf' => (\Yii::$app->request->get('to_pdf') ? 1 : 0),
                'width' => Info::blockWidth($this->id)
        ]);
    }
}
