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

namespace common\models;

use yii\db\ActiveRecord;

class GiveAwayProducts extends ActiveRecord {

    public static function tableName() {
        return 'give_away_products';
    }

    public function getProduct() {
      return $this->hasOne(\common\models\Products::class, ['products_id' => 'products_id']);
    }

    public function getBackendDescription() {
      $languages_id = \Yii::$app->settings->get('languages_id');

      if (\backend\models\ProductNameDecorator::instance()->useInternalNameForListing()) {
        $nameColumn = new \yii\db\Expression("IF(LENGTH(products_internal_name), products_internal_name, products_name)");
      } else {
        $nameColumn = 'products_name';
      }

      return $this->hasOne(\common\models\ProductsDescription::class, ['products_id' => 'products_id'])
                ->select(['products_name' => $nameColumn])
                ->addSelect(['platform_id', 'products_id', 'language_id'])
                ->where(['language_id' => (int)$languages_id,
                         'platform_id' => intval(\common\classes\platform::defaultId())
                  ])
                ->orderBy($nameColumn);
    }
    
    public function getCustomerGroup() {
      return $this->hasOne(\common\models\Groups::class, ['groups_id' => 'groups_id']);
    }
}
