<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

class Featured {

  public static  function featured_cleanup() {
    try {
      \common\models\Featured::deleteAll(
        ['and',
          ['status' => 0],
          ['or',
            ['<', 'start_date', new \yii\db\Expression('now()')],
            ['is', 'start_date',  new \yii\db\Expression('null')]
          ]
        ])
        ;
      \common\models\Featured::deleteAll(
          ['not in', 'products_id', (new \yii\db\Query())->from(TABLE_PRODUCTS)->select('products_id')]
        )
        ;

    } catch (\Exception $e) {
      \Yii::warning($e->getMessage(), 'featured');
      echo $e->getMessage();
    }
  }
  
  public static  function tep_expire_featured($force=false) {
    if ($force || !defined('EXPIRE_FEATURED_BY_CRON') || EXPIRE_FEATURED_BY_CRON == 'False' || date('H:i') == '00:07') {
      tep_db_query("update " . TABLE_FEATURED . " set status = 1, date_status_change = now() where status = '0' and now() >=  start_date and start_date > 0 and (expires_date is null or expires_date='0000-00-00 00:00:00' or expires_date > start_date)");
      tep_db_query("update " . TABLE_FEATURED . " set status = 0, date_status_change = now() where status = '1' and now() >= expires_date and expires_date > 0");
    }
  }
}
