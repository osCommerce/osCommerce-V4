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
namespace backend\components;

use yii;

trait LocationSearchTrait
{

    public function actionAddressState() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));

        $zones = [];
        $zones_queryActive = \common\models\Zones::find()
            ->where(['zone_country_id' => $country])
            ->andfilterWhere(['like', 'zone_name', $term])
            ->orderBy('zone_name')
            ->asArray()
            ->all();

        foreach ($zones_queryActive as $response) {
            $zones[] = $response['zone_name'];
        }
        echo json_encode($zones);
    }

    public function actionAddressCity() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $state = tep_db_prepare_input(Yii::$app->request->get('state',''));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));
        $out_data = Yii::$app->request->get('out_data',[]);

        $cities = [];
        $cities_queryActive = \common\models\Cities::find()
            ->alias('c')
            ->filterWhere(['c.city_country_id' => $country])
            ->join('left join', \common\models\Zones::tableName().' z', 'z.zone_id=c.city_zone_id')
            ->andFilterWhere(['like', 'c.city_name', $term])
            ->orderBy('c.city_name')
            ->select(['c.city_name','z.zone_name']);
        if ( $state ){
          $zones_queryActive = clone $cities_queryActive;
          $zones_queryActive->andFilterWhere(['z.zone_name'=>$state]);
          if ( $zones_queryActive->count()>0 ){
              $cities_queryActive = $zones_queryActive;
          }
        }

        if ( count($out_data)>0 ){
            $cities_queryActive
                ->join('left join', \common\models\Countries::tableName().' cc', "cc.countries_id=c.city_country_id and cc.language_id='".\Yii::$app->settings->get('languages_id')."'");
            $cities_queryActive->addSelect(['c.city_id', 'cc.countries_id', 'cc.countries_name','z.zone_id']);
        }

        foreach ($cities_queryActive->asArray()->all() as $response) {
            if ( count($out_data)>0 ){
                $cities[] = [
                    'value' => $response['city_name'],
                    'city_id' => $response['city_id'],
                    'city_name' => $response['city_name'],
                    'zone_id' => $response['zone_id'],
                    'zone_name' => $response['zone_name'],
                    'country_id' => $response['countries_id'],
                    'country_name' => $response['countries_name'],
                ];
            }else {
                $cities[] = [
                    'id' => $response['city_name'],
                    'value' => $response['city_name'],
                    'state' => (string)$response['zone_name'],
                ];
            }
        }
        echo json_encode($cities);
    }

    public function actionAddressPostcode() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));

        $addresses = [];

        $searchAddress = \common\models\PostalCodes::find()
            ->alias('p')
            ->where(['like', 'postcode', $term.'%', false])
            ->andFilterWhere(['country_id' => $country])
            ->join('left join', \common\models\Cities::tableName().' c', 'c.city_id=p.city_id')
            ->join('left join', \common\models\Zones::tableName().' z', 'z.zone_id=p.zone_id')
            ->orderBy(['p.postcode'=>SORT_ASC])
            ->select(['p.postcode', 'p.suburb', 'c.city_name', 'z.zone_name']);

        foreach ($searchAddress->asArray()->all() as $addr){
            $addresses[] = [
                'id' => $addr['postcode'],
                'value' => $addr['postcode'],
                'suburb' => (string)$addr['suburb'],
                'city' => (string)$addr['city_name'],
                'state' => (string)$addr['zone_name'],
            ];
        }

        echo json_encode($addresses);
    }

}
