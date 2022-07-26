<?php
namespace backend\controllers;

use Yii;
use common\models\CouponsCustomerCodesList;
use common\models\Coupons;
use yii\web\Response;
use yii\helpers\Url;
use yii\db\Query;

/**
 * default controller to handle user requests.
 */
class Coupon_admincustomerscodesController extends Sceleton
{    
    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_COUPON_ADMIN'];
    
    public function beforeAction($action) {
        if (false === \common\helpers\Acl::checkExtensionAllowed('CouponsAndVauchers', 'allowed')) {
            $this->redirect(array('/'));
            return false;
        }
        return parent::beforeAction($action);
    }    

    public function actionCoupon_csv_loaded() 
    {        
        global $language;
        \common\helpers\Translation::init('admin/coupon_admin');
        $this->layout = false;
        $cid = Yii::$app->request->get('cid', '');

        $ajaxListUrl = Yii::$app->urlManager->createUrl(["/coupon_admincustomerscodes/list", "cid"=>$cid]);
        $ajaxEditUrl = Yii::$app->urlManager->createUrl(["/coupon_admincustomerscodes/edit", "cid"=>$cid]);
        $ajaxSaveUrl = Yii::$app->urlManager->createUrl(["/coupon_admincustomerscodes/save", "cid"=>$cid]);
        $ajaxDeleteUrl = Yii::$app->urlManager->createUrl(["/coupon_admincustomerscodes/delete", "cid"=>$cid]);
        $ajaxDeleteAllUrl = Yii::$app->urlManager->createUrl(["/coupon_admincustomerscodes/deleteall", "cid"=>$cid]);
        
        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
        
        return $this->render('../coupon_admin/customers_coupons', [
            'ajaxListUrl' => $ajaxListUrl,
            'ajaxEditUrl' => $ajaxEditUrl,
            'ajaxSaveUrl' => $ajaxSaveUrl,
            'ajaxDeleteUrl' => $ajaxDeleteUrl,
            'ajaxDeleteAllUrl' => $ajaxDeleteAllUrl,
            'cid' => $cid,
            'couponUsed' => $this->getCouponUsedTimes($cid, ''),
        ]);
    }
    
    public function getCouponUsedTimes($cid, $email)
    {
      if (empty($email) ) {
        $count_redemptions = tep_db_fetch_array(tep_db_query(
            "select count(*) as cnt from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $cid . "'"
        ));
        $couponUsed = $count_redemptions['cnt'];
      } else {
        $couponUsed = \common\helpers\Coupon::couponUsedBy($cid, $email);
      }
      return $couponUsed;
    }

    public function actionList()
    {              
        global $languages_id;
        \common\helpers\Translation::init('admin/coupon_admin');           
               
        $draw = Yii::$app->request->get('draw');
        $start = Yii::$app->request->get('start');
        $length = Yii::$app->request->get('length');
        
        $cid = Yii::$app->request->get('cid');
       
        $recordsTotalCount = CouponsCustomerCodesList::find()->where('coupon_id='.(int) $cid)->count();        
        //$recordsTotal = CouponsCustomerCodesList::find()->orderBy('valid_from DESC')->all();
        
        $records = CouponsCustomerCodesList::find()->where('coupon_id='.(int) $cid);
        
        if (isset($_GET['search']['value']) && !empty($_GET['search']['value'])) {
            $keywords = $_GET['search']['value'];
            $records->andWhere(" only_for_customer LIKE '%" . $keywords . "%' OR coupon_code LIKE '%" . $keywords . "%' ");          
        }
        
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "only_for_customer " . $_GET['order'][0]['dir'];
                    break;
                case 1:
                    $orderBy = "coupon_code " . $_GET['order'][0]['dir'];
                    break;
                case 2:
                    $orderBy = "date_added " . $_GET['order'][0]['dir'];
                    break;              
                default:
                    $orderBy = "only_for_customer DESC";
                    break;
            }
        } else {
            $orderBy = "only_for_customer DESC";
        }
        
        $recordsFilteredCount = $records->count();
        $recordsTotal = $records->orderBy($orderBy)->all(); 
//        echo "<pre>";
//        print_r($records->createCommand()->sql);
//        echo "</pre>";
        
        //$couponUsed = (int)$this->getCouponUsedTimes($cid);
        
        $responseList = [];
        foreach ($recordsTotal as $key => $record) {
          $couponUsed = (int)$this->getCouponUsedTimes($cid, $record->only_for_customer);
            $responseList[] = 
                [
                'only_for_customer' => 
                    (
                    $couponUsed > 0 ?
                    $record->only_for_customer :
                    '<a href="javascript:void(0);" onClick="return editCustomersCoupon(' . $record->customercode_id . ', ' . $record->coupon_id . ')" title="'.COUPON_CUSTOMERS_COUPONS_EDIT.'">' 
                    . $record->only_for_customer 
                    . '</i></a>'
                    ), 
                'coupon_code' => $record->coupon_code, 
                'date_added' => !is_null($record->date_added) ? date('d-m-Y',strtotime($record->date_added)) : ' - - - - - - - ',
                ];
        }
                       
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $recordsTotalCount,
            'recordsFiltered' => $recordsFilteredCount,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    
    public function actionEdit()
    {        
        \common\helpers\Translation::init('admin/coupon_admin');
        $cid = Yii::$app->request->post('cid');
        $customersCouponRecordId = (int) Yii::$app->request->post('u_id');
        $ajaxSaveUrl = (int) Yii::$app->urlManager->createUrl(["/coupon_admincustomerscodes/save", "cid"=>$cid, "customercode_id"=>$customersCouponRecordId]);
                  
            $customersCoupontRecord = new \stdClass();           
            if ($customersCouponRecordId > 0) {
                $orderNameRecord = CouponsCustomerCodesList::find()->where(['customercode_id' => $customersCouponRecordId])->one();                
                if ($orderNameRecord !== null) {
                    $customersCoupontRecord->id = $orderNameRecord->customercode_id;
                    $customersCoupontRecord->cid = $orderNameRecord->coupon_id;
                    $customersCoupontRecord->only_for_customer = $orderNameRecord->only_for_customer;
                    $customersCoupontRecord->coupon_code = $orderNameRecord->coupon_code;                    
                } else {
?>
<div class="alert alert-error fade in">
    <i data-dismiss="alert" class="icon-remove close"></i>
    Customers coupon record is not valid!
</div>
<?php
                    die();
                }
            } else {
                $customersCoupontRecord->id = 0;
                $customersCoupontRecord->cid = $orderNameRecord->coupon_id;
                $customersCoupontRecord->only_for_customer = '';
                $customersCoupontRecord->coupon_code = '';               
            }
            return $this->renderAjax(                
                '@backend/themes/basic/coupon_admin/customers_coupons_edit.tpl',
                [
                'messages' => [], 
                'cid' => $cid, 
                'customersCouponRecordId' => $customersCouponRecordId, 
                'customersCoupontRecord' => $customersCoupontRecord,
                'ajaxSaveUrl' => $ajaxSaveUrl
                ]
            );
    }
    
    public function actionSave() 
    {
        \common\helpers\Translation::init('admin/coupon_admin');
        $cid = Yii::$app->request->post('cid');
        $customersCouponRecordId = (int) Yii::$app->request->post('id');
        
        $only_for_customer = trim(Yii::$app->request->post('only_for_customer'));
        $coupon_code = Yii::$app->request->post('coupon_code', '');
        if (trim($coupon_code) === '') {
          $coupon_code = \common\helpers\Coupon::create_coupon_code();
        }
        
        if (!$cid || !$only_for_customer) {
            echo json_encode(array('message' => COUPON_REQUIRED_FIELDS_EMPTY));
            exit();
        }
        
        $validator = new \yii\validators\EmailValidator();
        if (!$validator->validate($only_for_customer)) {
            echo json_encode(array('message' => COUPON_EMAIL_IS_NOT_VALID));
            exit();
        }

        
        if (!$customersCouponRecordId) {
            $CouponsCustomerCodesList = CouponsCustomerCodesList::find()
                ->where(
                    [
                    'only_for_customer' => $only_for_customer,
                    'coupon_id'=>$cid,                    
                    ])
                ->one();
            
            //attempt to save one more record for the existing email           
            if ($CouponsCustomerCodesList) {
                echo json_encode(array('message' => COUPON_EMAIL_DUPLICATE));
                exit();
            }   
            $CouponsCustomerCodesList = new CouponsCustomerCodesList();            
        } else {              
            
            $CouponsCustomerCodesList = CouponsCustomerCodesList::find()
                ->where(
                    ['customercode_id' => $customersCouponRecordId, 
                    'only_for_customer'=>$only_for_customer,
                    'coupon_id'=>$cid,
                    'coupon_code'=>trim($coupon_code)
                    ])
                ->one();
            //attempt to save the same record
            if ($CouponsCustomerCodesList) {
                echo json_encode(array('message' => 'ok'));
                exit();
            }            
            $CouponsCustomerCodesList = CouponsCustomerCodesList::find()
                ->where(['customercode_id' => $customersCouponRecordId, 'coupon_id'=>$cid])
                ->one();

            if ($this->getCouponUsedTimes($cid, $CouponsCustomerCodesList->only_for_customer)) {
                echo json_encode(array('message' => TEXT_COUPON_REDEEMED));
                exit();
            }

            $check = Coupons::getCouponByCode($coupon_code);
            if ($check &&  $CouponsCustomerCodesList->coupon_code!=trim($coupon_code)) {
                echo json_encode(array('message' => TEXT_COUPON_INCORRECT_DUPLICATE_CODE));
                exit();
            }
        }
        $CouponsCustomerCodesList->date_added = date('Y-m-d H:i:s');
        $CouponsCustomerCodesList->coupon_id = $cid;
        $CouponsCustomerCodesList->only_for_customer = $only_for_customer;
        $CouponsCustomerCodesList->coupon_code = trim($coupon_code);
        
        //$CouponsCustomerCodesList->validate();
        //var_dump($CouponsCustomerCodesList->errors);
        
        if ($CouponsCustomerCodesList->save()){
            echo json_encode(array('message' => 'ok'));
        } else {
            echo json_encode(array('message' => COUPON_CANNOT_PROCESS));
            exit();
        }
        exit();
    }    
    
    public function actionDelete()
    {
        \common\helpers\Translation::init('admin/coupon_admin');
        
        if (Yii::$app->request->post() == false) {
            return false;
        }
    
        $customersCouponRecordId = (int) Yii::$app->request->post('id');
        $cid = (int) Yii::$app->request->post('cid');
        
        if (!$customersCouponRecordId || !$cid) {
            echo json_encode(array('message' => COUPON_CANNOT_PROCESS));
            exit();
        }
        
        $CouponsCustomerCodesList = CouponsCustomerCodesList::find()
            ->where(['customercode_id' => $customersCouponRecordId, 'coupon_id'=>$cid])->one();
        
        if($CouponsCustomerCodesList && $CouponsCustomerCodesList instanceof CouponsCustomerCodesList){
            if($CouponsCustomerCodesList->delete()){
                echo json_encode(array('message' => 'ok'));                
            } else {
                echo json_encode(array('message' => COUPON_CANNOT_PROCESS ));                
            }
        } else {
            echo json_encode(array('message' => COUPON_CANNOT_PROCESS));
        }
        exit();
    }
    
    public function actionDeleteall()
    {
        \common\helpers\Translation::init('admin/coupon_admin');
        
        if (Yii::$app->request->post() == false) {
            return false;
        }
    
        $cid = (int) Yii::$app->request->post('cid');
        
        if (!$cid) {
            echo json_encode(array('message' => COUPON_CANNOT_PROCESS_DELETE));
            exit();
        }
        
        $CouponsCustomerCodesList = CouponsCustomerCodesList::find()
            ->where(['coupon_id'=>$cid])
            ->all();

        $deletionError = false;
        foreach ($CouponsCustomerCodesList as $item) {
            if (!$item->delete()) {
                $deletionError = true;
            }               
        }
        
        if ($deletionError) {
            echo json_encode(array('message' => COUPON_CANNOT_PROCESS_DELETE));
        } else {
            echo json_encode(array('message' => 'ok'));
        }
        exit();
    }
}
