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

namespace common\components;


use common\models\Information;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use function GuzzleHttp\Psr7\str;

class InformationPage extends Model
{


    public static function getFrontendData($page_id, $language_id=null, $platform_id=null)
    {
        if ( empty($language_id) ) $language_id = \Yii::$app->settings->get('languages_id');
        if ( empty($platform_id) ) $platform_id = \Yii::$app->get('platform')->config()->getId();

        $platform_ids = [];
        $platform_ids[] = (int)$platform_id;
        if ( (int)$platform_id!=\common\classes\platform::defaultId() ){
            $platform_ids[] = (int)\common\classes\platform::defaultId();
        }

        $cache_key = 'information_'.(int)$page_id.'_'.(int)$language_id.'@'.implode('_', $platform_ids);
        $info_data = \Yii::$app->getCache()->getOrSet($cache_key,function() use ($page_id, $language_id, $platform_ids){
            $info_data = Information::find()
                ->where(['information_id' => (int)$page_id, 'languages_id' => $language_id,])
                ->andWhere(['affiliate_id' => 0])
                ->andWhere(['IN', 'platform_id', $platform_ids])
                ->asArray()->all();
            return ArrayHelper::index($info_data, 'platform_id');
        },0, new TagDependency([
            'tags' => ['information','information_page_'.(int)$page_id]
        ]));

        $result_data = false;
        if ( !isset($info_data[$platform_id]) ){
            if ( isset($info_data[\common\classes\platform::defaultId()]) ){
                $result_data = $info_data[\common\classes\platform::defaultId()];
            }
        }else{
            $result_data = $info_data[$platform_id];
            foreach ( Information::mergeDescriptionColumnList() as $column ){
                if ( strlen($result_data[$column])==0 ){
                    $result_data[$column] = $info_data[\common\classes\platform::defaultId()][$column];
                }
            }
        }

        return $result_data;
    }

    public static function getFrontendDataVisible($page_id, $language_id=null, $platform_id=null)
    {
        $data = static::getFrontendData($page_id, $language_id, $platform_id);
        if ( is_array($data) && $data['visible'] ){
            return $data;
        }
        return false;
    }

    public static function findPageIdBySeo($seo_path, $language_id=null, $platform_id=null)
    {
        if ( empty($language_id) ) $language_id = \Yii::$app->settings->get('languages_id');
        if ( empty($platform_id) ) $platform_id = \Yii::$app->get('platform')->config()->getId();

        $data = Information::find()
            ->where(['seo_page_name'=>$seo_path, 'platform_id'=>(int)$platform_id])
            ->select(['information_id', 'languages_id', 'visible'])->asArray()
            ->orderBy([
               new \yii\db\Expression("IF(languages_id='".(int)$language_id."', 0, 1)"),
            ])
            ->limit(1)->one();
        if ( is_array($data) ) {
            if ($data['visible']) {
                return $data;
            }
        }else{
            $data = Information::find()
                ->alias('i')
                ->where(['i.seo_page_name'=>$seo_path, 'i.platform_id'=>\common\classes\platform::defaultId()])
                ->join('left join', Information::tableName().' pi',
                    "pi.information_id=i.information_id and pi.languages_id=i.languages_id and pi.platform_id='".(int)$platform_id."' and pi.affiliate_id=i.affiliate_id"
                )
                ->select(['i.information_id', 'i.languages_id', 'def_seo' => 'i.seo_page_name', 'p_seo' => 'pi.seo_page_name', 'visible'=>new \yii\db\Expression('IFNULL(pi.visible,i.visible)')])->asArray()
                ->orderBy([
                    new \yii\db\Expression("IF(i.languages_id='".(int)$language_id."', 0, 1)"),
                ])
                ->limit(1)->one();
            if ( is_array($data) && $data['visible']) {
                if ( empty($data['p_seo']) ) {
                    return $data;
                }
            }
        }
        return false;
    }

}