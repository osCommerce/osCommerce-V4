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
use frontend\design\Info;

class CompanyTaxDetails extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $ret = '';
        if (!empty($this->params["order"])) {
            $order = $this->params["order"];
            $platform_id = $order->info['platform_id']??0;
            $taxDescription = '';
            if (!empty($order->info['tax_groups']) && is_array($order->info['tax_groups']) && count($order->info['tax_groups'])==1) {
                $taxDescription = key($order->info['tax_groups']);
            }

            for ($i=0;$i<3;$i++) {
                if (!empty($this->settings[0]['company_' . $i])){

                    switch ($this->settings[0]['company_' . $i]) {
                        case 'name':
                            $ret .= \common\helpers\Tax::getCompanyName($taxDescription, 'tax_description', $platform_id);
                            break;
                        case 'address':
                            $ret .= \common\helpers\Tax::getCompanyAddress($taxDescription, 'tax_description', $platform_id);
                            break;
                        case 'vat_id':
                            $ret .= \common\helpers\Tax::getCompanyVATId($taxDescription, 'tax_description', $platform_id);
                            break;
                    }

                    if (!empty($this->settings[0]['spacer_' . $i])){
                        $ret .= $this->settings[0]['spacer_' . $i];
                    }

                }
            }
        }
        return $ret;

    }
}