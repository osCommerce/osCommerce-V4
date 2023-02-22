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

namespace frontend\design\boxes\account;

use common\models\Customers;
use common\services\BonusPointsService\BonusPointsService;
use common\services\CustomersService;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class BonusPointsConverter extends Widget
{

    public $file;
    public $params;
    public $settings;
    /** @var int */
    private $languageId;
    /** @var bool|Customers */
    private $customer;
    /** @var BonusPointsService */
    private $bonusPointsService;
    /** @var CustomersService */
    private $customersService;
    /** @var \common\classes\Currencies */
    private $currencies;
    /** @var int */
    private $customerGroupId;

    public function __construct(
        BonusPointsService $bonusPointsService,
        CustomersService $customersService,
        array $config = []
    )
    {
        parent::__construct($config);
        $this->bonusPointsService = $bonusPointsService;
        $this->customersService = $customersService;
        $this->currencies = \Yii::$container->get('currencies');
        $this->customerGroupId = (int) \Yii::$app->storage->get('customer_groups_id');
        try {
            $this->customer = \Yii::$app->user->isGuest ? false : \Yii::$app->user->getIdentity();
        } catch (\Throwable $t) {
            $this->customer = false;
        }
        $this->languageId = (int)\Yii::$app->settings->get('languages_id');
    }

    public function run(): string
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('BonusActions')) return '';
        $rate = \common\helpers\Points::getCurrencyCoefficient($this->customerGroupId);
        if ($rate === false) {
            return '';
        }
        $bonusPoints = ($this->customer !== false && $this->customer->customers_bonus_points > 0) ? $this->customer->customers_bonus_points : '';
        $rate1bonuses = 1;//\common\helpers\Points::getBonusPointsPrice(1, 1, $this->customerGroupId);
        $rate1currency = round($this->currencies->rate() * \common\helpers\Points::getCurrencyCoefficient($this->customerGroupId) * $rate1bonuses, 4);
        // $rate1currency = \common\helpers\Points::getBonusPointsPriceInCurrency($rate1bonuses, $this->customerGroupId);
        $currency = \Yii::$app->settings->get('currency');
        return IncludeTpl::widget(['file' => 'boxes/account/bonus-points-converter.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'currentCurrency' => $this->currencies->currencies[$currency],
            'currencies' => $this->currencies,
            'customerBonusPoints' => $bonusPoints,
            'coefficient' => $rate,
            'customerGroupId' => $this->customerGroupId,
            'displayConvertButton' => $this->bonusPointsService->allowCustomerConvertBonusPointsToAmount() && (int)$bonusPoints > 0,
            'rate1bonuses' => $rate1bonuses,
            'rate1currency' => $rate1currency,
        ]]);
    }
}
