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

namespace frontend\design\boxes\sitemap;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class InfoPages extends Widget {
    
    public $file;
    public $params;
    public $settings;
    public $isAjax;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $pages = [];
        $sql_query = tep_db_query(
            "SELECT i.information_id, i.languages_id, ".
            "if(length(i1.info_title), i1.info_title, i.info_title) as info_title, ".
            "i.page, i.page_type, i.scope ".
            "FROM " . TABLE_INFORMATION ." i ".
            "left join " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id and i1.languages_id = '". $languages_id . "' and i1.affiliate_id = '" . (isset($_SESSION['affiliate_ref'])?(int)$_SESSION['affiliate_ref']:0) . "' ".(\common\classes\platform::activeId()?" AND i1.platform_id='".\common\classes\platform::currentId()."' ":'')." ".
            "WHERE i.visible='1' and i.languages_id ='".$languages_id."' and i.affiliate_id = 0 ".(\common\classes\platform::activeId()?" AND i.platform_id='".\common\classes\platform::currentId()."' ":'')." ".
            "ORDER BY i.v_order"
        );
        while ($row = tep_db_fetch_array($sql_query)) {
            $check_in_menu = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM ".TABLE_MENU_ITEMS." WHERE link_id ='".$row['information_id']."' AND  link_type ='info' AND platform_id='".\common\classes\platform::currentId()."' "));
            if ( $check_in_menu['c']==0 ) continue;
            //if ( tep_not_null( $row['scope'] ) ) { $scope = explode(',',$row['scope']);}
            $location = tep_href_link(FILENAME_INFORMATION, 'info_id=' . $row['information_id'], 'NONSSL', false);
            $pages[] = '<a href="'.$location.'">'.$row['info_title'].'</a>';
        }

        if (!count($pages)) return '';

        return IncludeTpl::widget(['file' => 'boxes/sitemap/info-pages.tpl', 'params' => [
            'pages' => $pages,
        ]]);
    }
    
}