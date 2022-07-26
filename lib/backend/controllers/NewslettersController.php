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

namespace backend\controllers;

use Yii;
use backend\models\Admin;

/**
 * default controller to handle user requests.
 */
class NewslettersController extends Sceleton
{
    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_NEWSLETTERS'];
    
    protected $selected_platform_id;
    protected $filters = [];
    
    public function __construct($id, $module = null) {
        parent::__construct($id, $module);
        
        $this->selected_platform_id = \common\classes\platform::firstId();
        
        $try_set_platform = Yii::$app->request->get('platform_id', 0);
        if ( Yii::$app->request->isPost ) {
          $try_set_platform = Yii::$app->request->post('platform_id', $try_set_platform);
        }
        if ( $try_set_platform>0 ) {
          foreach (\common\classes\platform::getList(false) as $_platform) {
            if ((int)$try_set_platform==(int)$_platform['id']){
              $this->selected_platform_id = (int)$try_set_platform;
            }
          }
          Yii::$app->get('platform')->config($this->selected_platform_id)->constant_up();
        }
    }
    
    private function initFilters()
    {
        $filters = Yii::$app->request->post('export',[]);
/*        $orderStatusesSelect = [
        ];
        foreach( \common\helpers\Order::getStatusesGrouped() as $option){
            $orderStatusesSelect[$option['id']] = html_entity_decode($option['text'],null,'UTF-8');
        }
        $this->filters['order_status'] = [
            'items' => $orderStatusesSelect,
            'value' => [],
        ];
        
        $this->filters['date_type_range'] = [
            'value' => [],
        ];
        $this->filters['date_type_range'] = [
            'value' => [],
        ];
        $this->filters['interval'] = [
            'items' => [
                '' => TEXT_ALL,
                '1' => TEXT_TODAY,
                'week' => TEXT_WEEK,
                'month' => TEXT_THIS_MONTH,
                'year' => TEXT_THIS_YEAR,
                '3' => TEXT_LAST_THREE_DAYS,
                '7' => TEXT_LAST_SEVEN_DAYS,
                '14' => TEXT_LAST_FOURTEEN_DAYS,
                '30' => TEXT_LAST_THIRTY_DAYS,
            ],
            'value' => Yii::$app->request->get('interval',[]),
        ];
        $this->filters['by_totals'] = [
            'items' => [
                'ot_subtotal' => SUB_TOTAL,
                'ot_total' => TABLE_HEADING_TOTAL,
            ],
            'value' => Yii::$app->request->get('interval',[]),
        ];
        $this->filters['date_from'] = [
            'value' => [],
        ];
        $this->filters['date_to'] = [
            'value' => [],
        ];
        $this->filters['re_export'] = [
            'value' => [],
        ];
        $this->filters['by_totals_val_from'] = [
            'value' => [],
        ];
        $this->filters['by_totals_val_to'] = [
            'value' => [],
        ];
        $this->filters['add_totals'] = [
            'value' => [],
        ];
        $this->filters['all_orders'] = [
            'value' => [],
        ];
        $post = Yii::$app->request->post('export');
        foreach( \common\classes\platform::getList() as $platform ) {
            foreach( array_keys($this->filters) as $key ) {
                $value = '';
                if ( $key=='date_type_range' ) $value = 'presel';
                if ( isset($post[$platform['id']]) && isset($post[$platform['id']][$key]) ) {
                    $value = $post[$platform['id']][$key];
                }
                $this->filters[$key]['value'][$platform['id']] = $value;
                if ( ($key=='date_from' || $key=='date_to') && !empty($value) ) {
                    $value_time = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, \common\helpers\Date::checkInputDate($value));
                    if ( $value_time ) {
                        $this->filters[$key]['value_datetime'][$platform['id']] = $value_time->format('Y-m-d H:i:s');
                    }
                }
            }
        }*/
    }
    
    public function actionIndex()
    {
        $this->selectedMenu = array('marketing',  );
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('newsletters'.'/'), 'title' => BOX_NEWSLETTERS/*HEADING_TITLE*/);
        $this->view->headingTitle = BOX_NEWSLETTERS/*HEADING_TITLE*/;

        $platformList = \common\classes\platform::getList();
        foreach ($platformList  as $idx=>$platformVariant){
            $platformList[$idx]['tabLink'] = Yii::$app->urlManager->createUrl(['newsletters'.'/', 'platform_id'=>$platformVariant['id']]);
        }

        if ( Yii::$app->request->isPost ){
            foreach( $platformList as $platformVariant ) {
              \common\extensions\Newsletters\Newsletters::onPlatformConfigUpdate((int)$platformVariant['id']);
            }
            $this->redirect(Yii::$app->urlManager->createUrl(['newsletters'.'/', 'platform_id'=>$this->selected_platform_id]));
        }
        
        $this->initFilters();

        return $this->render('index.tpl', [
            'platforms' => $platformList,
            'selected_platform_id' => $this->selected_platform_id,
            'isMultiPlatform' => \common\classes\platform::isMulti(),
            'form_action' => Yii::$app->urlManager->createUrl(['newsletters'.'/']),
            'urlExport' => Yii::$app->urlManager->createUrl(['newsletters'.'/export','export'=>1]),
            'filters' => $this->filters
        ]);
    }

    public function actionSubscribe() {
      $platform_id = Yii::$app->request->post('platform_id');
      $info = Yii::$app->request->post();
      $ret = \common\extensions\Newsletters\Newsletters::subscribe($platform_id, $info);
      echo json_encode($ret );
    }
    
    public function actionSubscribeupdate() {
      $platform_id = Yii::$app->request->post('platform_id');
      $info = Yii::$app->request->post();
      $ret = \common\extensions\Newsletters\Newsletters::subscriptionUpdate($platform_id, $info);
      echo json_encode($ret);
    }
    
    public function actionDelete() {
      $platform_id = Yii::$app->request->post('platform_id');
      $info = Yii::$app->request->post();
      $ret = \common\extensions\Newsletters\Newsletters::subscriptionDelete($platform_id, $info);
      echo json_encode($ret);
    }

}