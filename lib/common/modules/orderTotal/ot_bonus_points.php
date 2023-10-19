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
namespace common\modules\orderTotal;

use common\classes\modules\ModuleTotal;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

class ot_bonus_points extends ModuleTotal {

    var $title, $output;

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_BONUS_POINTS_TITLE' => 'Reward points',
        'MODULE_ORDER_TOTAL_BONUS_POINTS_DESCRIPTION' => 'Reward points',
    ];

    public function __construct() {
        parent::__construct();

        $this->code = 'ot_bonus_points';
        $this->title = MODULE_ORDER_TOTAL_BONUS_POINTS_TITLE;
        $this->description = MODULE_ORDER_TOTAL_BONUS_POINTS_DESCRIPTION;
        $this->enabled = defined('MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS') && MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS == 'true' && \common\helpers\Extensions::isAllowedAnd('BonusActions', 'isProductPointsEnabled');
        $this->sort_order = MODULE_ORDER_TOTAL_BONUS_POINTS_SORT_ORDER;
        //$this->credit_class = true;
        $this->output = array();
    }

    function getIncVATTitle() {
        return '';
    }

    function getIncVAT($visibility_id = 0, $checked = false) {
        return '';
    }

    function getExcVATTitle() {
        return '';
    }

    function getExcVAT($visibility_id = 0, $checked = false) {
        return '';
    }

    function process($replacing_value = -1, $visible = false)
    {
        /** @var \common\extensions\BonusActions\BonusActions $ext */
        if (!($ext=\common\helpers\Extensions::isAllowedAnd('BonusActions', 'isProductPointsEnabled')) || \Yii::$app->user->isGuest) return;

        $order = $this->manager->getOrderInstance();
        $sumPoints = 0;
        foreach ($order->getOrderedProducts() as $product) {
            $sumPoints += $product['qty'] * ($product['bonus_points_cost'] ?? 0);
        }
        if ($sumPoints > 0) {
            $this->output[] = [
                'title' => $this->title,
                'text' => $ext::formatPointAndCurrency($sumPoints),
            ];
        }
    }

    function pre_confirmation_check(){
        return 0;
    }

    function collect_posts($collect_data) {
    }


    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_BONUS_POINTS_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS' =>
            array(
                'title' => 'Display Bonus Points',
                'value' => 'true',
                'description' => 'Do you want this module to display?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_BONUS_POINTS_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '9',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
        );
    }

}
