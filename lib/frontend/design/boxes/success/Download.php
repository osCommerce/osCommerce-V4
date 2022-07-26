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

namespace frontend\design\boxes\success;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Download extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  { global $order;
    parent::init();
    if (is_object($order)){
        $this->params['orders_id'] = $order->order_id;
    }
  }

  public function run()
  {
    if (DOWNLOAD_ENABLED == 'true' && isset($this->params['orders_id']) && (int)$this->params['orders_id'] > 0) {
        $customer_id = (int)Yii::$app->user->getId();
        $downloadContents = [];
            \common\helpers\Translation::init('admin/sitemap');
            \common\helpers\Translation::init('account/history-info');
            $downloads_query = tep_db_query("select o.orders_status, date_format(o.last_modified, '%Y-%m-%d') as date_purchased_day, opd.download_maxdays, op.products_name, opd.orders_products_download_id, opd.orders_products_filename, opd.download_count, opd.download_maxdays from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd where o.customers_id = '" . (int)$customer_id . "' and o.orders_status IN (" . DOWNLOADS_CONTROLLER_ORDERS_STATUS . ") and o.orders_id = '" . (int)$this->params['orders_id'] . "' and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_filename != ''");
            if (tep_db_num_rows($downloads_query) > 0) {
                while ($downloads = tep_db_fetch_array($downloads_query)) {
                    list($dt_year, $dt_month, $dt_day) = explode('-', $downloads['date_purchased_day']);
                    $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads['download_maxdays'], $dt_year);
                    $download_expiry = date('Y-m-d H:i:s', $download_timestamp);
                      if ( ($downloads['download_count'] > 0) && (file_exists(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'])) && ( ($downloads['download_maxdays'] == 0) || ($download_timestamp > time())) ) {
                        $text = '<a href="' . tep_href_link(FILENAME_DOWNLOAD, 'order=' . $this->params['orders_id'] . '&id=' . $downloads['orders_products_download_id']) . '">' . $downloads['products_name'] . '</a>';
                        $btn = '<a href="' . tep_href_link(FILENAME_DOWNLOAD, 'order=' . $this->params['orders_id'] . '&id=' . $downloads['orders_products_download_id']) . '" class="btn">' . TEXT_DOWNLOAD_FILE . '</a>';
                      } else {
                        $text = $downloads['products_name'];
                          $btn = '';
                      }

                    $downloadContents[] = [
                        $text,
                        TABLE_HEADING_DOWNLOAD_DATE . ' ' . \common\helpers\Date::date_long($download_expiry),
                        $downloads['download_count'] . ' ' . TABLE_HEADING_DOWNLOAD_COUNT,
                        $btn
                    ];
                }
                }
                $downloads_check_query = tep_db_query("select o.orders_id, opd.orders_products_download_id from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd where o.orders_id = opd.orders_id and o.orders_id = '" . (int)$this->params['orders_id'] . "' and opd.orders_products_filename != ''");
                if (tep_db_num_rows($downloads_check_query) > 0 and tep_db_num_rows($downloads_query) < 1) {
                    $downloadContents[] = [
                        DOWNLOADS_CONTROLLER_ON_HOLD_MSG,
                    ];
                }
        return IncludeTpl::widget(['file' => 'boxes/success/download.tpl', 'params' => ['downloads' => $downloadContents]]);
    }
    return '';    
  }
}