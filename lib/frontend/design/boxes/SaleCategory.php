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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class SaleCategory extends Widget
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
        if (\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
            return '';
        }
        $languages_id = \Yii::$app->settings->get('languages_id');

        if (!$this->settings[0]['promo_id']) {
            return '';
        }
        $promo_id = (int)$this->settings[0]['promo_id'];

        $promo = \common\models\promotions\Promotions::find()
            ->alias('p')
            ->innerJoin(\common\models\promotions\PromotionsToPlatform::tableName() . ' p2p', 'p.promo_id = p2p.promo_id and platform_id = ' . PLATFORM_ID)
            ->where(['p.promo_id' => $promo_id])
            ->andWhere('promo_date_expired >= curdate() or promo_date_expired = "0000-00-00" or promo_date_expired is null')
            ->andWhere('promo_date_start <= curdate() or promo_date_start = "0000-00-00" or promo_date_start is null')
            ->with('sets')
            ->with('conditions')
            ->one();

        if ($promo->conditions[0]['promo_type'] != 1) {
            return '';
        }

        if (!count($promo->sets)){
            return '';
        }
        $categories = [];
        foreach($promo->sets as $set){
            $categories[] = [
                'type' => $set->getAttribute('promo_slave_type'),
                'id' => $set->getAttribute('promo_slave_id'),
            ];
        }
        $key = array_rand($categories);
        $set = $categories[$key];

        $type = $set['type'];
        $id = $set['id'];
        $name = '';
        if ($type == 1) {

            $categoryQuery = tep_db_fetch_array(tep_db_query("
                select cd.categories_name, c.categories_image
                from " . TABLE_CATEGORIES . " c 
                    left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on cd.categories_id = c.categories_id and cd.language_id = '" . (int)$languages_id . "'
                where c.categories_id = '" . (int)$id . "'"));
            $name = $categoryQuery['categories_name'];
            $img = $categoryQuery['categories_image'];

        } elseif ($type == 2) {

            $manufacturer = tep_db_fetch_array(tep_db_query("
                select manufacturers_name, manufacturers_image
                from " . TABLE_MANUFACTURERS . " 
                where manufacturers_id = '" . (int)$id . "'"));
            $name = $manufacturer['manufacturers_name'];
            $img = $manufacturer['manufacturers_image'];

        }

        if (!$name) {
            return '';
        }
        $category = [
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'img' => Yii::getAlias('@webCatalogImages/'.$img)
        ];


        $save = $promo->conditions[0]['promo_deduction'];

        $days = $hours = $minutes = $seconds = 0;
        $expiresDate = 0;

        if (strtotime($promo->promo_date_expired)) {
            $lastTime = strtotime($promo->promo_date_expired) - date("U");

            if ($lastTime < 0) {
                return '';
            }

            $days = floor($lastTime / (3600 * 24));
            $lastTime = $lastTime - $days * (3600 * 24);
            $hours = floor($lastTime / 3600 );
            $lastTime = $lastTime - $hours * 3600;
            $minutes = floor($lastTime / 60);
            $lastTime = $lastTime - $minutes * 60;
            $seconds = $lastTime;

            $expiresDate = \common\helpers\Date::date_short($promo->promo_date_expired);
        }

        $link = Yii::$app->urlManager->createAbsoluteUrl(['catalog/index', 'cPath' => $category['id']]);
        
        return IncludeTpl::widget(['file' => 'boxes/sale-category.tpl', 'params' => [
            'category' => $category,//
            'expiresDate' => $expiresDate,
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'save' => $save,
            'imageUrl' => $category['img'],//
            'link' => $link,//
            'id' => $this->id,
        ]]);
    }
}