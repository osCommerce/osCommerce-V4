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

use Yii;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "orders_status".
 *
 * @property int $orders_status_id
 * @property int $orders_status_groups_id
 * @property int $language_id
 * @property string $orders_status_name
 * @property string $orders_status_template
 * @property int $comment_template_id
 * @property string $orders_status_template_confirm
 * @property string $orders_status_template_sms
 * @property int $automated
 * @property int $order_evaluation_state_id
 * @property int $orders_status_allocate_allow
 * @property int $orders_status_release_deferred
 */
class OrdersStatus extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'orders_status';
    }

    public static function create($orders_status_id, $orders_status_groups_id, $language_id, $orders_status_name, $orders_status_template, $automated, $orders_status_template_confirm = null, $order_evaluation_state_id = 0, $orders_status_allocate_allow = 0, $orders_status_release_deferred = 0){
    	$model = new static();
    	$model->orders_status_id = $orders_status_id;
    	$model->orders_status_groups_id = $orders_status_groups_id;
    	$model->language_id = $language_id;
    	$model->orders_status_name = $orders_status_name;
        $model->comment_template_id = 0;
    	$model->orders_status_template = $orders_status_template;
    	$model->orders_status_template_confirm = $orders_status_template_confirm;
    	$model->automated = $automated;
        $model->order_evaluation_state_id = $order_evaluation_state_id;
        $model->orders_status_allocate_allow = $orders_status_allocate_allow;
        $model->orders_status_release_deferred = $orders_status_release_deferred;
    	return $model;
    }

    public static function insertNew($orders_status_groups_id, $orders_status_names, $params) {
      if (intval($orders_status_groups_id)>0 && !empty($orders_status_names)) {
        $languages = \common\helpers\Language::get_languages( true );
        $newId     = \common\models\OrdersStatus::newOrdersStatusId();

        try {

          foreach( $languages as $language ) {
            $model = new static();
            $model->loadDefaultValues();

            foreach (array_keys($model->getAttributes()) as $a) {
              switch ($a) {
                case 'orders_status_id':
                  $v = $newId;
                  break;
                case 'orders_status_groups_id':
                  $v = $orders_status_groups_id;
                  break;
                case 'language_id':
                  $v = $language['id'];
                  break;
                case 'orders_status_name':
                  if (is_array($orders_status_names) ) {
                    if (!empty($orders_status_names[$language['id']])) {
                      $v = $orders_status_names[$language['id']];
                    } else {
                      /// first element
                      $v = reset($orders_status_names);
                    }
                  } else {
                    $v = $orders_status_names;
                  }
                break;
                default:
                  if (isset($params[$a])) {
                    if (is_array($params[$a]) ) {
                      if (!empty($params[$a][$language['id']])) {
                        $v = $params[$a][$language['id']];
                      } else {
                        /// first element
                        $v = reset($params[$a]);
                      }
                    } else {
                      $v = $params[$a];
                    }
                  } else {
                    // do not change defaulot value if attribute is not specified
                    continue 2;
                  }
                break;
              }
              $model->setAttribute($a, $v);
            }
            $model->save();
          }
        } catch (\Exception $e) {
          \Yii::warning($e->getMessage());
        }
      }
    	return $model;
    }

    public static function newOrdersStatusId(){
    	return self::find()->max('orders_status_id') + 1;
    }

    public function getOrdersStatusHistory()
    {
        return $this->hasMany(OrdersStatusHistory::className(), ['orders_status_id' => 'orders_status_id']);
    }

/**
 * preferred (if it's has appropriate state id)|default|first (by id) order status Id in appropriate state id
 * @param int $orderEvaluationStateId
 * @param int $orderStatusPreferred
 * @return int|null
 */
    public static function getDefaultByOrderEvaluationState($orderEvaluationStateId = 0, $orderStatusPreferred = 0)
    {
        $return = null;
        $orderEvaluationStateId = (int)$orderEvaluationStateId;
        $orderStatusPreferred = (int)$orderStatusPreferred;
        foreach (self::find()->alias('os')
            ->leftJoin(OrdersStatusGroups::tableName() . ' osg', '((osg.orders_status_groups_id = os.orders_status_groups_id) AND (osg.language_id = os.language_id))')
            ->where('((os.order_evaluation_state_id = :orderEvaluationStateId) OR ((os.order_evaluation_state_id = 0) AND (osg.order_group_evaluation_state_id = :orderEvaluationStateId)))'
                , [':orderEvaluationStateId' => $orderEvaluationStateId]
            )
            ->orderBy(['os.order_evaluation_state_default' => SORT_DESC, 'os.orders_status_id' => SORT_ASC])
            ->asArray(false)->all() as $osRecord
        ) {
            if (is_null($return)) {
                $return = $osRecord;
            }
            if (($orderStatusPreferred == 0) OR ((int)$osRecord->orders_status_id == $orderStatusPreferred)) {
                $return = $osRecord;
                break;
            }
        }
        unset($osRecord);
        return $return;
    }
}