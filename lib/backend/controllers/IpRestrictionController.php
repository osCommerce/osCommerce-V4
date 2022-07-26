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
use common\models\Restriction;
/**
 * default controller to handle user requests.
 */
class IpRestrictionController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_IP_RESTRICTION'];
    
    public function __construct($id, $module = null) {
        \common\helpers\Translation::init('admin/ip-restriction');
        parent::__construct($id, $module);
    }

    public function actionIndex() {
        global $language;

        $this->selectedMenu = array('settings', 'tools', 'ip-restriction');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('ip-restriction/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="javascript:void(0)" class="btn btn-primary" onclick="return ipEdit(0)">'.IMAGE_NEW.'</a>';
        $this->view->headingTitle = HEADING_TITLE;

        $this->view->row_id = (int)Yii::$app->request->get('row_id', 0);
        $this->view->ipTable = array(
            array(
                'title' => TABLE_IP,
                'not_important' => 0,
            ),
        );

        $messages = [];
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            if (!is_array($messages)) $messages = [];
        }
        return $this->render('index', array('messages' => $messages));
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        
        parse_str(Yii::$app->request->get('filter',''), $output);
        

        $responseList = [];
        if ($length == -1)
            $length = 10000;
        $recordsTotal = 0;
        
        $forbidden = Restriction::find();
        
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $forbidden->andWhere(['like', 'forbidden_address', $_GET['search']['value']]);
        }
        
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $forbidden->orderBy('forbidden_address ' . $_GET['order'][0]['dir']);
                    break;
                default:
                    $forbidden->orderBy('forbidden_address ');
                    break;
            }
        } else {
            $forbidden->orderBy('forbidden_address ');
        }
                
        $recordsTotal = $forbidden->count();
        $forbidden->limit($length)->offset($start);
        $rows = $forbidden->all();
        if (is_array($rows)) foreach ($rows as $key => $forbidden) {
            $responseList[] = array(
                $forbidden->getAttribute('forbidden_address') . '<input class="cell_identify" type="hidden" value="' . $forbidden->getAttribute('forbidden_id') . '">',
            );
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $responseList,
        ];
        echo json_encode($response);
    }

    public function actionPreview(){
        $forbidden_id = Yii::$app->request->post('forbidden_id', 0);
        $forbidden = Restriction::find()->where(['forbidden_id' => $forbidden_id])->one();
        $this->view->row_id = Yii::$app->request->post('row_id', 0);
        
        return $this->renderAjax('view',[
            'forbidden' => $forbidden 
        ]);
    }
    
    public function actionEdit(){
        $forbidden_id = Yii::$app->request->post('forbidden_id', 0);
        if ($forbidden_id){
            $forbidden = Restriction::find()->where(['forbidden_id' => $forbidden_id])->one();
        } else {
            $forbidden = new Restriction();
        }
        
        return $this->renderAjax('edit',[
            'forbidden' => $forbidden,
        ]);
    }
    
    public function actionSave(){
        $forbidden_id = Yii::$app->request->post('forbidden_id', 0);
        $forbidden_address = Yii::$app->request->post('forbidden_address', '');
        if ($forbidden_id){
            $forbidden = Restriction::find()->where(['forbidden_id' => $forbidden_id])->one();
        } else {
            $forbidden = new Restriction();
        }
        
        $forbidden->setAttribute('forbidden_address', $forbidden_address);
        
        if($forbidden->validate()){
            if (!$forbidden->hasErrors()){                
                $forbidden->save();
            }
        }
        //echo '<pre>';print_r();die;
        echo json_encode($forbidden->getErrors());
        exit();
    }
    
    public function actionDelete(){
        $forbidden_id = Yii::$app->request->post('forbidden_id', 0);
        if ($forbidden_id){
            $forbidden = Restriction::find()->where(['forbidden_id' => $forbidden_id])->one();
            $forbidden->delete();
        }
        echo 'ok';
    }
}
