<?php

/**
 * /usr/bin/php /home/user/public_html/site/yii.php events - > /dev/null
 */

namespace console\controllers;

use backend\services\ConfigurationService;
use common\classes\Currencies;
use common\classes\platform;
use common\components\Customer;
use common\helpers\Points;
use common\models\Customers;
use common\services\BonusPointsService\DTO\TransferData;
use common\services\CustomersService;
use yii\console\Controller;
use common\services\BonusPointsService\BonusPointsService;

/**
 * Events controller
 */
class EventsController extends Controller {

    public $runNow;

    public function options($actionID)
    {
        if ( $actionID=='datasource' ) {
            return ['runNow'];
        }
        return [];
    }

   /* public function options($actionID)
    {
        return ['r'=>'run'];
    }*/

    public function bindActionParams($action, $params){
        $language_id = \common\classes\language::defaultId();
        \common\helpers\Translation::init('main', $language_id);
        return parent::bindActionParams($action, $params);
    }

    /**
     * Default cron event
     */
    public function actionIndex() {
        echo "cron service running\n";
    }

    /**
     * cron event
     */
    public function actionReset() {
        echo "cron service 2 running\n";
    }

    /**
     * cron event save analytics.js locally
     */
    public function actionGoogleAnalytics() {
        $path = dirname($_SERVER['SCRIPT_NAME']);
        $remoteFile = 'https://www.google-analytics.com/analytics.js';
        $localfile = $path . '/themes/basic/js/analytics.js';
        $response = file_get_contents($remoteFile);
        if ($response != false) {
            //www.google-analytics.com/plugins/ua/
            $response = preg_replace("/\/\/www\.google\-analytics\.com\/plugins\/ua\//im", $path . "/themes/basic/js/", $response);
            if (!file_exists($localfile)) {
                fopen($localfile, 'w');
            }
            if (is_writable($localfile)) {
                if ($fp = fopen($localfile, 'w')) {
                    fwrite($fp, $response);
                    fclose($fp);
                }
            }
        }
    }

    /**
     * cron event save gtm file locally as gtm_{$gtm_code}.js
     */
    public function actionGoogleGtm() {
        $path = dirname($_SERVER['SCRIPT_NAME']);

        foreach(\common\classes\platform::getList() as $platform){
            $modules = \common\components\Google::getInstalledModules($platform['id'], true);
            if (isset($modules['tagmanger'])) {
                $data_module = \common\components\Google::getInstalledModule($modules['tagmanger']['google_settings_id'], true);
                $gtm_code = $data_module->config[$data_module->code]['fields'][0]['value'];
                if ($gtm_code){
                    $remoteScriptPath = "https://www.googletagmanager.com/gtm.js?id={$gtm_code}";
                    $theme_array = tep_db_fetch_array(tep_db_query("select t.theme_name,p.platform_url from " . TABLE_PLATFORMS_TO_THEMES . " AS p2t INNER JOIN " . TABLE_THEMES . " as t ON (p2t.theme_id=t.id) inner join " . TABLE_PLATFORMS . "  p on (p.platform_id=p2t.platform_id) where p2t.is_default = 1 and p2t.platform_id = " . (int)$platform['id']));

                    $localScriptPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . 'themes/' . $theme_array['theme_name']. "/js/gtm_{$gtm_code}.js";
                    $gaPath = '/themes/basic/js/';
                    if (!is_dir($gaPath)){ @mkdir($gaPath, 0777);}
                    $ctx = stream_context_create(array('http' => array('timeout' => 1)));
                    $response = @file_get_contents($remoteScriptPath, 0, $ctx);
                    if ($response != false) {
                        $fp = fopen($localScriptPath, 'w');
                        if (is_writable($localScriptPath)) {
                            $response = preg_replace("/\/\/www\.google\-analytics\.com\//im", "//" . $theme_array['platform_url'] . $gaPath, $response);
                            fwrite($fp, $response);
                            fclose($fp);
                        }
                    }
                }
            }
        }
    }

    /**
     * cron event save ec.js file locally
     */
    public function actionGoogleEc() {
        $path = dirname($_SERVER['SCRIPT_NAME']);
        $remoteFile = 'https://www.google-analytics.com/plugins/ua/ec.js';
        $localfile = $path . '/themes/basic/js/ec.js';
        $response = file_get_contents($remoteFile);
        if ($response != false) {
            if (!file_exists($localfile)) {
                fopen($localfile, 'w');
            }
            if (is_writable($localfile)) {
                if ($fp = fopen($localfile, 'w')) {
                    fwrite($fp, $response);
                    fclose($fp);
                }
            }
        }
    }

    /**
     * cron event
     */
    public function actionPayments() {
        echo "cron service 4 running\n";
    }

    /**
     * cron EP export
     */
    public function actionExport() {
        echo "cron service Export running\n";

        \backend\models\EP\Cron::runExport();

        echo "cron service Export - done\n";
    }

    /**
     * cron EP import
     */
    public function actionImport() {
        echo "cron service Import running\n";

        \backend\models\EP\Cron::runImport();

        echo "cron service Import - done\n";
    }

    /**
     * cron EP import
     */
    public function actionDatasource($runNow = false) {

        echo "cron service Datasource running\n";

        $language_id = \common\classes\language::defaultId();
        \common\helpers\Translation::init('admin/main', $language_id);
        \common\helpers\Translation::init('admin/easypopulate', $language_id);

        \backend\models\EP\Cron::runDatasource(boolval($runNow));

        echo "cron service Datasource - done\n";
    }

    public function actionRestore()
    {
        global $argv;

        define('DIR_FS_BACKUP', \Yii::getAlias('@app') . '/../../admin/backups/');
        define('LOCAL_EXE_GUNZIP', '/bin/gunzip');
        define('LOCAL_EXE_UNZIP', '/usr/bin/unzip');
        include \Yii::getAlias('@app') . '/../../includes/local/configure.php';

        $read_from = $argv[2];

        if (file_exists(DIR_FS_BACKUP . $read_from)) {
            $restore_file = DIR_FS_BACKUP . $read_from;
            $extension = substr($read_from, -3);

            if (($extension == 'sql') || ($extension == '.gz') || ($extension == 'zip')) {
                switch ($extension) {
                    case 'sql':
                        $restore_from = $restore_file;
                        $remove_raw = false;
                        break;
                    case '.gz':
                        $restore_from = substr($restore_file, 0, -3);
                        exec(LOCAL_EXE_GUNZIP . ' ' . $restore_file . ' -c > ' . $restore_from);
                        $remove_raw = true;
                        break;
                    case 'zip':
                        $restore_from = substr($restore_file, 0, -4);
                        exec(LOCAL_EXE_UNZIP . ' ' . $restore_file . ' -d ' . DIR_FS_BACKUP);
                        $remove_raw = true;
                }

                if (isset($restore_from) && file_exists($restore_from) && (filesize($restore_from) > 15000)) {
                    exec('mysql -h' . DB_SERVER . ' -u' . DB_SERVER_USERNAME . ' -p' . DB_SERVER_PASSWORD . ' ' . DB_DATABASE . ' < ' . $restore_from);
                }
            }
        }
    }

    public function actionUpdatePopularity(){
        (new \common\components\Popularity())->process();
    }

    /**
     * cron customers newsletters sync (mailchimp) Params (optional) max count, delay between each
     * @param int $count
     * @param int $timeout
     */
    public function actionNewslettersSubscription($count=0, $timeout=0) {
      /** @var \common\extensions\Newsletters\Newsletters $ext */
      if($ext = \common\helpers\Acl::checkExtension('Newsletters', 'subscribeAll')){
        if ($ext::allowed()){
            $ext::subscribeAll($count, $timeout);
        }
      } else {
        echo 'Module disabled' . "\n";
      }
      echo '<hr>Done.' . "\n";
    }

/**
 * not implemented yet
 */
    public function actionNewslettersProducts() {
      /** @var \common\extensions\Newsletters\Newsletters $ext */
      if($ext = \common\helpers\Acl::checkExtension('Newsletters', 'productsAll')){
        if ($ext::allowed()){
            $ext::productsAll();
        }
      } else {
        echo 'Module disabled';
      }
      echo '<hr>Done.';
    }

/**
 * not implemented yet
 */
    public function actionNewslettersOrders() {
      /** @var \common\extensions\Newsletters\Newsletters $ext */
      if($ext = \common\helpers\Acl::checkExtension('Newsletters', 'ordersAll')){
        if ($ext::allowed()){
            $ext::ordersAll();
        }
      } else {
        echo 'Module disabled';
      }
      echo '<hr>Done.';
    }

    /**
     * cron product prices re-index process
     */
    public function actionProductPricesindex(){
        if($ext = \common\helpers\Acl::checkExtensionAllowed('ProductPriceIndex', 'allowed')){
            if ($ext::isEnabled()){
                //$ext::reindex();
                $ext::checkUpdateStatus();
            }
        } else {
            echo 'Module disabled' . "\n";
        }

        echo '<hr>Done.' . "\n";
    }

    /**
     * cron product prices clean up index process
     */
    public function actionProductPricescleanup(){
        if($ext = \common\helpers\Acl::checkExtensionAllowed('ProductPriceIndex', 'allowed')){
            if ($ext::isEnabled()){
                $ext::cleanup();
            }
        } else {
            echo 'Module disabled' . "\n";
        }

        echo '<hr>Done.' . "\n";
    }

    /**
     * cron plain product, params: count, ignoreLifeTime
     * @param int $count
     * @param int $ignoreLifeTime
     */
    public function actionPlainProductTable($count=0, $ignoreLifeTime=0){
      /* @var $ext \common\extensions\PlainProductsDescription\PlainProductsDescription */
        if($ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed')){
            if ($ext::isEnabled()){
              if (!empty($ignoreLifeTime)) {
                $pid = null;
              } else {
                $pid = false;
              }
              $cnt = -1;
              if (!empty($count) && (int)$count > 0) {
                $cnt = $count;
              }
              $ext::reindex($pid, $cnt);
            } else {
                echo 'Module disabled' . "\n";
            }
        } else {
            echo 'Module is not installed' . "\n";
        }

        echo '<hr>Done.' . "\n";
    }

/**
 * cron product product clean up, params: force default 0
 * @param bool $force default 0
 */
    public function actionPlainProductTablecleanup($force = 0){
      /* @var $ext \common\extensions\PlainProductsDescription\PlainProductsDescription */
        if($ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed')){
            if ($ext::isEnabled()){
                $ext::cleanup($force);
            }
        } else {
            echo 'Module disabled' . "\n";
        }

        echo '<hr>Done.' . "\n";
    }

    public function actionClearBonusPoints(): void
    {
        if (\common\helpers\Acl::checkExtensionAllowed('BonusActions')) {
            /** @var BonusPointsService $bonusPointsService */
            $bonusPointsService = \Yii::createObject(BonusPointsService::class);
            /** @var ConfigurationService $configurationService */
            $configurationService = \Yii::createObject(ConfigurationService::class);
            $platforms = \common\models\Platforms::find()->select('platform_id')->asArray()->all();
            $rule = $configurationService->findValue('BONUS_POINTS_CLEAR_RULE');
            $bonusPointsService->clearBonusPoints($rule, -1);
            foreach ($platforms as ['platform_id'=> $platform_id]) {
                $rule = $configurationService->findValue('BONUS_POINTS_CLEAR_RULE', (int)$platform_id);
                $bonusPointsService->clearBonusPoints($rule, (int)$platform_id);
            }
        }
    }

    public function actionBonusPointsToCreditAmount(string $sendEmails = 'not-send-email'): void
    {
        $send = $sendEmails === 'send-email';
        /** @var $platform_config \common\classes\platform_config */
        $platform_config = \Yii::$app->get('platform')->config();
        $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
        $STORE_OWNER = $platform_config->const_value('STORE_OWNER');
        /** @var BonusPointsService $bonusPointsService */
        $bonusPointsService = \Yii::createObject(BonusPointsService::class);
        if (Points::getCurrencyCoefficientNoCache() === false) {
            return;
        }
        /** @var CustomersService $customersService */
        $customersService = \Yii::createObject(CustomersService::class);
        /** @var ConfigurationService $configurationService */
        $configurationService = \Yii::createObject(ConfigurationService::class);
        /** @var \common\classes\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');
        $customersQuery = $customersService->getCustomerIdentityWithBonusPointsQuery();
        foreach ($customersQuery->batch() as $customers) {
            foreach ($customers as $customer) {
                /** @var Customer $customer */
                $bonusPointsCosts = Points::getCurrencyCoefficientNoCache($customer->groups_id, $customer->platform_id);
                if ($bonusPointsCosts === false) {
                    continue;
                }
                $transfer = TransferData::create($customer, $bonusPointsCosts, $customer->customers_bonus_points, $send, $send);
                $credit_amount = $customersService->bonusPointsToAmount($transfer);
                if ($send) {
                    $email_params['STORE_NAME'] = $STORE_OWNER;
                    $email_params['CUSTOMER_FIRSTNAME'] = $customer->customers_firstname;
                    $email_params['CUSTOMER_LASTNAME']= $customer->customers_lastname;
                    $email_params['CREDIT_AMOUNT'] = '+' . $currencies->format($credit_amount, true, DEFAULT_CURRENCY, $currencies->currencies[DEFAULT_CURRENCY]['value']);
                    $email_params['CREDIT_AMOUNT_COMMENTS'] = TEXT_CONVERT_FROM_BONUS_POINTS;
                    $email_params['BONUS_POINTS_COMMENTS'] = TEXT_CONVERT_TO_AMOUNT;
                    $email_params['BONUS_POINTS'] =  '-' . $transfer->getBonusPoints();

                    [$emailSubject, $emailContent] = \common\helpers\Mail::get_parsed_email_template('Credit amount notification', $email_params, $customer->language_id, $customer->platform_id);
                    \common\helpers\Mail::send($customer->customers_firstname . ' ' . $customer->customers_lastname, $customer->customers_email_address, $emailSubject, $emailContent, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS, [], '', '', ['add_br' => 'no']);
                    [$emailSubject, $emailContent] = \common\helpers\Mail::get_parsed_email_template('Bonus points notification', $email_params, $customer->language_id, $customer->platform_id);
                    \common\helpers\Mail::send($customer->customers_firstname . ' ' . $customer->customers_lastname, $customer->customers_email_address, $emailSubject, $emailContent, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS, [], '', '', ['add_br' => 'no']);
                }
            }
        }
    }

    /**
     * updates status of specials and featured products
     */
    public function actionMarketingStatus(){
      \common\helpers\Specials::tep_expire_specials(true);
      \common\helpers\Featured::tep_expire_featured(true);
    }

    /**
     * clean up specials and featured products
     */
    public function actionMarketingCleanup(){
      \common\helpers\Specials::specials_cleanup();
      \common\helpers\Featured::featured_cleanup();
    }

    /**
     * cron special prices clean up
     */
    public function actionSpecialsCleanup(){
      \common\helpers\Specials::specials_cleanup();
      echo '<hr>Done.' . "\n";
    }

    /**
     * cron featured clean up
     */
    public function actionFeaturedCleanup(){
      \common\helpers\Featured::featured_cleanup();
      echo '<hr>Done.' . "\n";
    }

    /**
     * cron special prices status switch
     */
    public function actionSpecialsStatus(){
      \common\helpers\Specials::tep_expire_specials(true);
      echo '<hr>Done.' . "\n";
    }

    /**
     * cron featured status switch
     */
    public function actionFeaturedStatus(){
      \common\helpers\Featured::tep_expire_featured(true);
      echo '<hr>Done.' . "\n";
    }

    public function actionReportByEmail()
    {
        \common\helpers\Acl::checkExtensionAllowed('ReportByEmail', 'doSendAll');
        echo 'Done!';
    }
}