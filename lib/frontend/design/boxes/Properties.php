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

class Properties extends Widget
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

        if (!$this->settings[0]['property']) {
            return '';
        }

        $property = tep_db_fetch_array(tep_db_query("
                select properties_type 
                from " . TABLE_PROPERTIES . " 
                where properties_id = '" . (int)$this->settings[0]['property'] . "'"));

        $propertiesQuery = tep_db_query("
                select * 
                from " . TABLE_PROPERTIES_VALUES . " 
                where 
                    properties_id = '" . $this->settings[0]['property'] . "' and 
                    language_id = '" . $languages_id . "'" .
             " order by sort_order, " . ($property['properties_type'] == 'number' || $property['properties_type'] == 'interval' ? 'values_number' : 'values_text')
            );

        $seoName = \common\helpers\Properties::get_properties_seo_page_name($this->settings[0]['property'], $languages_id);

        $properties = [];
        while($item = tep_db_fetch_array($propertiesQuery)) {
            $item['link'] = Yii::$app->urlManager->createUrl($seoName . '(' . $item['values_seo_page_name'] . ')');
            $properties[] = $item;
        }

        if (count($properties) == 0) {
            return '';
        }

        return IncludeTpl::widget([
            'file' => 'boxes/properties.tpl',
            'params' => [
                'properties' => $properties,
                'type' => $property['properties_type'],
                'id' => $this->id
            ]
        ]);
    }
}