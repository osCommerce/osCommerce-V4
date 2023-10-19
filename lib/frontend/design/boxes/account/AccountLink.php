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

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class AccountLink extends Widget
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
        $languages_id = \Yii::$app->settings->get('languages_id');
        if (!Info::isAdmin()) {
            if ( (isset($this->settings[0]['hide_link']) && $this->settings[0]['hide_link'] == 'credit_amount_history')
                    && 
                  (isset($this->params['mainData']['count_credit_amount']) && $this->params['mainData']['count_credit_amount'] == 0)
                ) {
                return '';
            }
            if (
                  (isset($this->settings[0]['hide_link']) && $this->settings[0]['hide_link'] == 'points_earnt_history')
                    && 
                  (isset($this->params['mainData']['has_customer_points_history']) && !$this->params['mainData']['has_customer_points_history'])
                ) {
                return '';
            }
            if (
                  (isset($this->settings[0]['hide_link']) && $this->settings[0]['hide_link'] == 'wishlist')
                    && 
                  !(isset($this->params['wishlist']) && is_array($this->params['wishlist']) && count($this->params['wishlist']) > 0)
                ) {
                return '';
            }
            if (
                  (isset($this->settings[0]['hide_link']) && $this->settings[0]['hide_link'] == 'orders')
                    && 
                  (isset($this->params['mainData']['total_orders']) && $this->params['mainData']['total_orders'] == 0)
                ) {
                return '';
            }
            if (isset($this->settings[0]['hide_link']) && $this->settings[0]['hide_link'] == 'quotations') {
                $quotations = [];
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('Quotations', 'allowed')) {
                    $quotations = $ext::getQuotationList(Yii::$app->user->getId(), $languages_id);
                }
                if (count($quotations) == 0) {
                    return '';
                }
            }
            if (isset($this->settings[0]['hide_link']) && $this->settings[0]['hide_link'] == 'samples') {
                $samples = [];
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('Samples', 'allowed')) {
                    $samples = $ext::getSamplesList(Yii::$app->user->getId(), $languages_id);
                }
                if (count($samples) == 0) {
                    return '';
                }
            }
            if (isset($this->settings[0]['hide_link']) && $this->settings[0]['hide_link'] == 'review') {
                $reviews = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_REVIEWS . " where customers_id = '" . (int)Yii::$app->user->getId() . "'"));
                if ($reviews['total'] == 0) {
                    return '';
                }
            }
            if (isset($this->settings[0]['link']) && $this->settings[0]['link'] == 'trade_form' && !\common\helpers\Acl::checkExtensionAllowed('TradeForm')) {
                return '';
            }
        }
        
        $this->settings[0]['link'] = (isset($this->settings[0]['link']) ? $this->settings[0]['link'] : '');
        
        $text = '';
        if (isset($this->settings[0]['text']) && defined($this->settings[0]['text'])) {
            $text = constant($this->settings[0]['text']);
        }
        if (!$text && $this->settings[0]['link'] == 'logoff') {
            $text = TEXT_LOGOFF;
        }  elseif (!$text && $this->settings[0]['link'] == 'bonus_program') {
            $text = TEXT_BONUS_PROGRAM_LINK;
        } elseif (!$text && $this->settings[0]['link']) {
            $text = $this->settings[0]['link'];
            if (!$this->settings[0]['link']) {
                $text = TEXT_DASHBOARD;
            }
        }
        
        $page = \common\classes\design::pageName($this->settings[0]['link']);
        
        $is_multi = \Yii::$app->get('storage')->get('is_multi');
        /** @var \common\extensions\CustomersMultiEmails\CustomersMultiEmails $CustomersMultiEmails */
        if ($is_multi) {
            if ($CustomersMultiEmails = \common\helpers\Acl::checkExtensionAllowed('CustomersMultiEmails', 'allowed')) {
              if (!$CustomersMultiEmails::checkLink($this->settings[0]['link'])) {
                  if ($this->settings[0]['link'] == 'Order History') {
                      return TEXT_NOT_ALLOWED;
                  }
                  return '';
              }
              if ($this->settings[0]['link'] == 'logoff') {
                 $multiCustomerInfo = $CustomersMultiEmails::getMultiCustomerInfo();
                 if (is_object($multiCustomerInfo)) {
                    $text .= ' ('.$multiCustomerInfo->customers_firstname . ' ' . $multiCustomerInfo->customers_lastname . ')';
                 }
              }
            }
            if ($DealersMultiCustomers = \common\helpers\Acl::checkExtensionAllowed('DealersMultiCustomers', 'allowed')) {
                if (!$DealersMultiCustomers::checkLink($this->settings[0]['link'])) {
                    if ($this->settings[0]['link'] == 'Order History') {
                        return TEXT_NOT_ALLOWED;
                    }
                    return '';
                }
                if ($this->settings[0]['link'] == 'logoff') {
                    $multiCustomerInfo = $DealersMultiCustomers::getMultiCustomerInfo();
                    if (is_object($multiCustomerInfo)) {
                       $text .= ' ('.$multiCustomerInfo->customers_firstname . ' ' . $multiCustomerInfo->customers_lastname . ')';
                    }
                }
            }
        }
            /*
        if ($this->settings[0]['link'] == 'delete' || $page == 'address_book' || $page == 'account_edit' || $page == 'my_password') {
            $manager = new \common\services\OrderManager(Yii::$app->get('storage'));
            if ($manager->get('is_multi') == 1) {
                return '';
            }
        }*/

        /** @var \common\extensions\PersonalCatalog\PersonalCatalog $personalCatalog */
        if (isset($this->settings[0]['hide_link']) && $this->settings[0]['hide_link'] == 'personal_catalog') {
          if ($personalCatalog = \common\helpers\Acl::checkExtension('PersonalCatalog', 'allowed')) {
            if (!$personalCatalog::allowed()) {
              return '';
            }
          }
        }

        $this->settings[0]['order_id'] = (isset($this->settings[0]['order_id']) ? $this->settings[0]['order_id'] : 0);
// 2check outdated code below??
        if ($this->settings[0]['link'] == 'logoff') {
            $url = Yii::$app->urlManager->createUrl(['account/logoff']);
        } elseif ($this->settings[0]['link'] == 'personal-catalog') {
            $url = Yii::$app->urlManager->createUrl([FILENAME_PERSONAL_CATALOG]);
        } elseif ($this->settings[0]['link'] == 'bonus_program') {
            $url = Yii::$app->urlManager->createUrl(['promotions/actions']);
        } elseif ($this->settings[0]['link'] == 'download_orders') {
            $url = Yii::$app->urlManager->createUrl(['account/download-my-orders']);
        } elseif ($this->settings[0]['link'] == 'delete') {
          // disabled or has open orders - forward to contact page
          $hasOrders = \common\helpers\Customer::hasOpenOrders(\Yii::$app->user->getId());
          if (
              (defined('GDPR_CUSTOMER_DELETE_BY_REQUEST') && GDPR_CUSTOMER_DELETE_BY_REQUEST == 'True')
              || $hasOrders
             ) {
            $this->settings[0]['link'] = 'delete_dis';
            $contactName = Info::themeSetting('contact_name');
            if (defined('GDPR_CUSTOMER_DELETE_BY_REQUEST') && GDPR_CUSTOMER_DELETE_BY_REQUEST == 'True') {
              $up = [($contactName ? $contactName : 'contact'), 'error_message' => TEXT_DELETE_BY_REQUEST];
            } else {
              $up = [($contactName ? $contactName : 'contact'), 'error_message' => TEXT_CANT_DELETE_WITH_OPEN_ORDERS];
            }
            $url = Yii::$app->urlManager->createUrl($up);

          } else {
            $url = Yii::$app->urlManager->createUrl(['account/delete']);
          }
          
        } elseif ($this->settings[0]['link'] == 'trade_form') {
            $url = Yii::$app->urlManager->createUrl(['trade-form']);
        } elseif ($this->settings[0]['link']) {
            if ($this->settings[0]['order_id']) {
                $orderId = (int)Yii::$app->request->get('order_id');
                $url = Yii::$app->urlManager->createUrl(['account', 'page_name' => $page, 'order_id' => $orderId]);
            } else {
                $url = Yii::$app->urlManager->createUrl(['account', 'page_name' => $page]);
            }
        } else {
            $url = Yii::$app->urlManager->createUrl(['account']);
        }

        $active = false;
        $activeArr = explode(',', (isset($this->settings[0]['active_link']) ? $this->settings[0]['active_link'] : ''));
        foreach ($activeArr as $activePge) {
            $activePge = \common\classes\design::pageName($activePge);
            if ($activePge && Yii::$app->request->get('page_name') == $activePge) {
                $active = true;
            }
        }

        if (Yii::$app->request->get('page_name') == $page) {
            $active = true;
        }
        
        $this->settings[0]['popup'] = (isset($this->settings[0]['popup']) ? $this->settings[0]['popup'] : 0);
        $this->settings[0]['like_button'] = (isset($this->settings[0]['like_button']) ? $this->settings[0]['like_button'] : 0);
        $this->settings[0]['popup_class'] = (isset($this->settings[0]['popup_class']) ? $this->settings[0]['popup_class'] : 0);
        
        

        return IncludeTpl::widget(['file' => 'boxes/account/account-link.tpl', 'params' => [
            'settings' => $this->settings,
            'text' => $text,
            'url' => $url,
            'active' => $active,
            'id' => $this->id,
        ]]);
    }
}
