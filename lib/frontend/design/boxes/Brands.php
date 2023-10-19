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
use frontend\design\Info;

class Brands extends Widget
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

        $manufacturersQuery = \common\models\Manufacturers::find()->alias('m')->joinWith('manufacturersInfo')
            ->addSelect('m.manufacturers_id, manufacturers_name, manufacturers_image, manufacturers_h2_tag')
            ->addSelect(['f_letter' => new \yii\db\Expression('lower(left(manufacturers_name,1))')]);

        if (($this->settings[0]['brands_with_active_products'] ?? false) || Info::themeSetting('hide_empty_brands')) {
            $manufacturersQuery->andWhere(['IN', 'm.manufacturers_id', \common\models\Products::find()
                ->alias('p')
                ->innerJoinWith('platform')
                ->where(['platform_id' => \common\classes\platform::currentId()])
                ->active()
                ->select('p.manufacturers_id')->distinct()->column()]);
        }

        $manufacturers = $manufacturersQuery->orderBy('manufacturers_name')->asArray()->all();

        $alphabets = $alphabet = [];
        $prevChar = '';
        $_lng = '0-9';


        if (!empty($manufacturers)) {
          foreach($manufacturers as $k => $m) {
            $manufacturers[$k]['link'] = \Yii::$app->urlManager->createUrl(['catalog', 'manufacturers_id' => $m['manufacturers_id']]);
            $manufacturers[$k]['h2'] = $m['manufacturers_h2_tag'];
            $manufacturers[$k]['img'] = \common\classes\Images::getImageSet(
                  $m['manufacturers_image'],
                  'Brand gallery',
                  [],
                  Info::themeSetting('na_category', 'hide')
              );

            if (!empty($this->settings[0]['show_abc']) && $prevChar != $m['f_letter']) {
              $prevChar = $m['f_letter'];
              if (preg_match('/\d+/', $prevChar)){
                if (!isset($alphabets['0-9'])) {
                  $alphabets[$_lng]['letters'] = ['0-9'];//range(0, 9);
                  $alphabets[$_lng]['active'][] = '0-9';//$prevChar;
                }
                $manufacturers[$k]['f_letter'] = '0-9';
              } elseif (preg_match('/\pL/', $prevChar) ) {
                if (!isset($alphabet[$prevChar])) {
                  $tmp = \common\helpers\Language::getPossibleLanguage($prevChar);
                  if (!empty($tmp) && !isset($alphabets[$tmp]['letters'])) {
                    $_lng = $tmp;
                    $alphabets[$_lng]['letters'] = \common\helpers\Language::alphabets([$_lng]);
                    $alphabet += array_flip($alphabets[$_lng]['letters']);
                  } elseif (!in_array($prevChar, $alphabets[$_lng]['letters'])) {
                    $alphabets[$_lng]['letters'][] = $prevChar;
                    $alphabet[$prevChar] = $_lng;
                  }
                }
                if (!is_array($alphabets[$_lng]['active']) || !in_array($prevChar, $alphabets[$_lng]['active'])) {
                  $alphabets[$_lng]['active'][] = $prevChar;
                }
              }
            }

          }

            return IncludeTpl::widget([
                'file' => 'boxes/brands.tpl',
                'params' => ['brands' => $manufacturers, 'alphabets' => $alphabets]
            ]);

        }

        return '';
    }
}