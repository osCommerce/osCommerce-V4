<?php
// ignore me

/**
 * /usr/bin/php /home/user/public_html/site/yii.php events - > /dev/null
 */

namespace console\controllers;

use backend\services\ConfigurationService;
use common\classes\Currencies;
use common\classes\platform;
use common\components\Customer;
use common\models\Customers;
use common\services\CustomersService;
use yii\console\Controller;

/**
 * Events controller
 */
class EventsController extends Sceleton {

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
     * @param boolean $runNow allowed [1, '1', 'true', 'yes', 'y'] everything else == false
     */
    public function actionDatasource($runNow = false) {
        if (!in_array(trim($runNow), [1, '1', 'true', 'yes', 'y'])) {
            $runNow = false;
        }

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
        /**
         * @var $ext \common\extensions\ReportByEmail\ReportByEmail
         */
        if ($ext = \common\helpers\Extensions::isAllowed('ReportByEmail')) {
            $ext::doSendAll();
        }
        echo 'Done!';
    }

    public function actionAutoCalcProductPriceBySupplier()
    {
        \common\helpers\PriceFormula::batchProductAutoCalcPriceBySupplier();
    }
}