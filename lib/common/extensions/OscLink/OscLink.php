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

namespace common\extensions\OscLink;

use Yii;
use common\extensions\OscLink\models\Configuration;
use common\extensions\OscLink\models\Entity;
use common\extensions\OscLink\models\Mapping;
use common\extensions\OscLink\Render;
use \common\helpers\Assert;

require_once (Yii::$aliases['@common'] . '/extensions/OscLink/lib/autoload.php');

class OscLink extends \common\classes\modules\ModuleExtensions
{

    private static $platformArray = false;

    const FEEDS       = ['tax_zones', 'taxes', 'brands', 'categories', 'products_options', 'products', 'customers', 'groups', 'reviews', 'orders',];
    const FEEDS_NAMES = ['Tax Zones', 'Taxes', 'Brands', 'Categories', 'Products Options', 'Products', 'Customers', 'Groups', 'Reviews', 'Orders'];
    const FEED_GROUPS = [
        'taxes' => ['tax_zones', 'taxes'],
        'products' => ['brands', 'categories', 'products_options', 'products'],
        'customers' => ['customers', 'groups', 'reviews', 'orders'],
    ];

// <editor-fold defaultstate="collapsed" desc="actions">

    public static function adminActionIndex() {
        if (!self::allowed()) {
            tep_redirect(Yii::$app->urlManager->createUrl(['modules', 'set' => 'extensions']));
            return '';
        }
        \Yii::$app->controller->view->tab = \Yii::$app->request->get('tab');
        if (!in_array(\Yii::$app->controller->view->tab, ['tab_connection', 'tab_mapping', 'tab_actions'])) {
            \Yii::$app->controller->view->tab = 'tab_connection';
        }

        // Settings in controller->view
        \Yii::$app->controller->view->OscStateStatusArray = [];
        try {
            self::downloader()->testConnection();
            \Yii::$app->controller->view->OscStateStatusArray = self::getOrderStateStatusArray();
            \Yii::$app->controller->view->connectionSuccess = true;
        } catch (\Exception $ex) {
            \Yii::$app->controller->view->connectionSuccess = false;
        }
        \Yii::$app->controller->view->isMappedExist = Entity::isMappedExist();

        Yii::$app->controller->navigation[] = array(
            'link' => Yii::$app->urlManager->createUrl(['extensions', 'module' => 'OscLink']),
            'title' => \common\helpers\Php8::getConst('BOX_MODULES_CONNECTORS_OSCLINK')
        );
        Yii::$app->controller->view->headingTitle = \common\helpers\Php8::getConst('BOX_MODULES_CONNECTORS_OSCLINK');
        Yii::$app->controller->view->ViewTable = array(
            array(
                'title' => \common\helpers\Php8::getConst('EXTENSION_OSCLINK_CONFIGURATION_KEY'),
                'not_important' => 0,
            ),
            array(
                'title' => \common\helpers\Php8::getConst('EXTENSION_OSCLINK_CONFIGURATION_VALUE'),
                'not_important' => 0,
            )
        );
        Yii::$app->controller->view->configurationArray = self::getConfigurationArray();
        if (\Yii::$app->controller->view->connectionSuccess) {
            Yii::$app->controller->view->configurationArray['api_status_map']['cmc_value'] = self::getMappingTable(\Yii::$app->controller->view->OscStateStatusArray);
        }
        Yii::$app->controller->view->platformList = self::getPlatformList(); //[0 => \common\helpers\Php8::getConst('EXTENSION_OSCLINK_API_PLATFORM_DEFAULT')];
        Yii::$app->controller->view->writerHtmlInterface = [];
        Yii::$app->controller->view->orderStatusArray = ([-1 => EXTENSION_OSCLINK_TEXT_MAPPING_ITEM_SKIPPED] + \common\helpers\Order::getStatusList(false, false, 0));
        Yii::$app->controller->view->actionsArray = self::getActionsArray();
        Yii::$app->controller->view->cleaningArray = self::getCleaningArray();

        $html = Render::widget(['template' => 'index.tpl', 'params' => ['tab' => Yii::$app->controller->view->tab]]);
        return Yii::$app->controller->renderContent($html);
    }

    public static function adminActionSave()
    {
        self::allowedOrDie();
        \common\helpers\Translation::init('extensions/osclink');
        Assert::assert(Yii::$app->request->isPost, 'Bad request');

        try {
            self::saveConfiguration(Yii::$app->request->post());

            self::checkPrerequisites();
            $connection = self::downloader();
            $connection->testConnection();
            return 'success';
        } catch (\Exception $ex) {
            \Yii::warning($ex->getMessage()."\n".$ex->getTraceAsString());

            return \common\helpers\Php8::getConst('EXTENSION_OSCLINK_MSG_CONNECT_ERROR') . ': ' . $ex->getMessage();
        }
    }

    public static function adminActionCancel()
    {
        self::allowedOrDie();
        //Assert::assert(Yii::$app->request->isPost, 'Bad request');
        Configuration::createCancelSign();
        echo "Import process will be canceled soon. Wait for finish message";
    }

    public static function adminActionExecute()
    {
        self::allowedOrDie();
        if (Yii::$app->request->isPost) {
            $feed = trim(Yii::$app->request->post('feed'));
            if (strtoupper($feed) == 'ALL') {
                $feed = self::FEEDS;
            } else if (!in_array($feed, self::FEEDS)) {
                return false;
            }

            try {
                header('X-Accel-Buffering: no');
                //header('Content-Encoding: none;'); don't use - the error under Chrome
                Configuration::deleteCancelSign();
                self::checkPrerequisites();
                $importer = new \OscLink\Importer( self::getConfigurationArray() );
                $importer->Import($feed);
                $success = true;
            } catch( \yii\base\UserException $ex) {
                \OscLink\Progress::Log('Import was interrupted: ' . $ex->getMessage());
                $success = false;
            } catch( \Throwable $ex) {
                \Yii::warning("Import was interrupted due an error: ".$ex->getMessage() . "\n". $ex->getTraceAsString(), 'Extensions\OscLink');
                \OscLink\Progress::Log('Import was interrupted due an error: '. $ex->getMessage());
                $success = false;
            }
            \OscLink\Progress::Done($success);
            Configuration::deleteCancelSign();
        }
    }

    public static function adminActionClean()
    {
        self::allowedOrDie();
        if (\Yii::$app->request->isPost) {
            $clean_fully = \Yii::$app->request->post('CleanFully') == 'fully';
            $selected_feeds = $clean_fully ? self::FEEDS : array_filter(self::FEEDS, function ($feed) { return \Yii::$app->request->post($feed) == 'on'; } );
            if (count($selected_feeds) > 0) {
                try {
                    Configuration::deleteCancelSign();
                    $importer = new \OscLink\Importer( self::getConfigurationArray() );
                    $error_sum = $importer->Clean($selected_feeds);

                    if ($clean_fully) {
                        if ($error_sum > 0) {
                            \OscLink\Helper::ProgressAndLog("Mapped data won't be removed because: $error_sum error(s) during cleaning.");
                        } else {
                            \OscLink\Helper::ProgressAndLog("Start clean mapped data.");
                            Entity::cleanMapping();
                            \OscLink\Helper::ProgressAndLog("Mapped data was successfully removed!");
                        }

                    }

                } catch( \yii\base\UserException $ex) {
                    \OscLink\Progress::Log($ex->getMessage());

                } catch( \Throwable $ex) {
                    $msg = "Cleaning was interrupted due an error: ".$ex->getMessage();
                    \Yii::warning( "$msg\n" . $ex->getTraceAsString(), 'Extensions\OscLink');
                    \OscLink\Helper::ProgressAndLog($msg);
                }
                \OscLink\Progress::Done();
                Configuration::deleteCancelSign();
            }
        }
    }

    public static function adminActionShowLog()
    {
        self::allowedOrDie();
        $basename = \Yii::$app->request->get('log');
        if (\Yii::$app->request->isGet && !empty($basename)) {
            try {
                $fn = \OscLink\Logger::buildFileName($basename);
                \common\helpers\Assert::fileExists($fn);
                //\Yii::$app->response->sendFile($fn)->send();
                Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
                return file_get_contents($fn);
            } catch(\Exception $e) {
                $errorMsg = 'Error: ' . $e->getMessage() . "\n";
                \Yii::warning( $errorMsg . $e->getTraceAsString(), 'Extensions\OscLink');
                return \common\helpers\System::isProduction()? '' : $errorMsg;
            }
        }
    }

    public static function adminActionTabStates()
    {
        self::allowedOrDie();

        $res['cleaning'] = Entity::isMappedExist();

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $res;
    }

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="private functions">

    private static function getPlatformList()
    {
        $res = [];
        foreach (\common\classes\platform::getList(true, true) as $platformRecord) {
            $res[(int)$platformRecord['id']] = $platformRecord['text'];
        }
        asort($res, SORT_STRING);
        return $res;
    }

    private static function correctPlatformIfNotInList($platform_id)
    {
        $list = self::getPlatformList();
        return isset($list[(int) $platform_id]) ? $platform_id : \common\helpers\Php8::array_key_first($list);
    }

    private static function getActionsArray()
    {
        $result = [];
        foreach (self::FEEDS as $feed)
        {
            $groupInfo = \OscLink\Helper::getFeedGroupInfo($feed);
            $result[$feed] = [
                'feed'          => $feed,
                'entity'        => \OscLink\Helper::getFeedName($feed),
                'group_name'    => $groupInfo['index'] == 0? \OscLink\Helper::getGroupName($groupInfo['group']): '',
                'group_count'   => $groupInfo['index'] == 0? $groupInfo['count'] : 0,
                'description'   => \common\helpers\Php8::getConst('EXTENSION_OSCLINK_TEXT_DESCRIPTION_' . strtoupper($feed) ),
                'action'        => \common\helpers\Php8::getConst('EXTENSION_OSCLINK_TEXT_REWRITE_BUTTON' ),
            ];
        }
        return $result;
    }

    private static function getCleaningArray()
    {
        $result = [];
        foreach (self::FEED_GROUPS as $group=>$feeds)
        {
            $result[$group] = [
                'group'          => $group,
                'group_name'     => \common\helpers\Php8::getConst('EXTENSION_OSCLINK_TEXT_GROUP_' . strtoupper($group) ),
            ];
            foreach ($feeds as $feed) {
                $result[$group]['feeds'][] = [
                    'feed'       => $feed,
                    'feed_name'  => \common\helpers\Php8::getConst('EXTENSION_OSCLINK_TEXT_ENTITY_' . strtoupper($feed) ),
                ];
            }
        }
        return $result;
    }


    private static function saveConfiguration($arr)
    {
        $configurationArray = self::getConfigurationArray();
        foreach ($arr as $key => $value) {
            try {
                $key = trim($key);
                if (isset($configurationArray[$key])) {
                    if (is_scalar($value)) {
                        $value = trim($value);
                    } elseif (is_array($value)) {
                        if ($key == 'api_status_map') {
                            $statusMapArray = array();
                            if (isset($value['tstatus']) AND is_array($value['tstatus'])) {
                                $checkMapArray = array('t' => array(), 'm' => array());
                                foreach ($value['tstatus'] as $index => $tstatus) {
                                    $tstatus = (int) $tstatus;
                                    $mstatus = (int) (isset($value['mstatus'][$index]) ? $value['mstatus'][$index] : 0);
                                    if (($tstatus > 0) AND ($mstatus > 0)
                                            AND!isset($checkMapArray['t'][$tstatus])
                                            AND!isset($checkMapArray['m'][$mstatus])
                                    ) {
                                        $statusMapArray[$tstatus] = $mstatus;
                                        $checkMapArray['t'][$tstatus] = true;
                                        $checkMapArray['m'][$mstatus] = true;
                                    }
                                    unset($mstatus);
                                }
                                unset($checkMapArray);
                                unset($tstatus);
                                unset($index);
                                unset($index);
                            }
                            $value = $statusMapArray;
                            self::saveMappingTable($value);
                            unset($statusMapArray);
                        } elseif ($key == 'api_tax_map') {
                            $taxMapArray = array();
                            if (isset($value['ttax']) AND is_array($value['ttax'])) {
                                $checkMapArray = array('t' => array(), 'm' => array());
                                foreach ($value['ttax'] as $index => $ttax) {
                                    $ttax = (int) $ttax;
                                    $mtax = (int) (isset($value['mtax'][$index]) ? $value['mtax'][$index] : 0);
                                    if (($ttax > 0) AND ($mtax > 0)
                                            AND!isset($checkMapArray['t'][$ttax])
                                            AND!isset($checkMapArray['m'][$mtax])
                                    ) {
                                        $taxMapArray[$ttax] = $mtax;
                                        $checkMapArray['t'][$ttax] = true;
                                        $checkMapArray['m'][$mtax] = true;
                                    }
                                    unset($mtax);
                                }
                                unset($checkMapArray);
                                unset($ttax);
                                unset($index);
                            }
                            $value = $taxMapArray;
                            unset($taxMapArray);
                        }
                    }
                    if ($value != $configurationArray[$key]['cmc_value']) {
                        $configurationRecord = Configuration::findOne(['cmc_key' => $key]);
                        if (!($configurationRecord instanceof Configuration)) {
                            $configurationRecord = new Configuration();
                            $configurationRecord->cmc_key = $key;
                        }
                        $value = trim(is_array($value) ? json_encode($value) : $value);
                        if ($configurationRecord->isNewRecord OR ($configurationRecord->cmc_value != $value)) {
                            $configurationRecord->cmc_value = $value;
                            $configurationRecord->save(false);
                        }
                    }
                }
            } catch (\Exception $exc) {
                \Yii::error('Error while saving configuration key '.$key.': '.$exc->getMessage(), 'Extensions\OscLink');
            }
        }
        unset($configurationArray);
        unset($value);
        unset($key);
        self::clearConfigCache();
    }

    private static function allowedOrDie()
    {
        if (!self::allowed()) {
            die();
        }
    }

    private static $config = null;

    private static function clearConfigCache()
    {
        self::$config = null;
    }

    public static function getConfigurationArray($key = '')
    {
        if (is_null(self::$config)) {
            $key = trim($key);
            $return = [];

            foreach (['connection' => 'api_url, api_method, api_key', 'mapping' => 'api_platform, api_measurement, api_status_map'/*, api_tax_map'*/] as $type => $keys) {
                foreach (explode(',', $keys) as $k) {
                    $return[trim($k)] = ['cmc_key' => trim($k), 'cmc_value' => '', 'cmc_type' => $type];
                }
            }

            $configurationArray = Configuration::find()->asArray(true)->all();
            foreach ($configurationArray as $itemArray) {
                if (isset($return[$itemArray['cmc_key']])) {
                    $return[$itemArray['cmc_key']]['cmc_value'] = $itemArray['cmc_value'];
                }
            }
            unset($configurationArray);
            unset($itemArray);
            foreach ($return as &$itemArray) {
                $title = ('EXTENSION_OSCLINK_' . strtoupper($itemArray['cmc_key']));
                $title = \common\helpers\Php8::getConst($title);
                $itemArray['title'] = $title;
                unset($title);
                if (in_array($itemArray['cmc_key'], array('api_status_map', 'api_tax_map'))) {
                    $itemArray['cmc_value'] = json_decode($itemArray['cmc_value'], true);
                    $itemArray['cmc_value'] = (is_array($itemArray['cmc_value']) ? $itemArray['cmc_value'] : array());
                }
            }
            unset($itemArray);
            $return['api_platform']['cmc_value'] = self::correctPlatformIfNotInList($return['api_platform']['cmc_value']);
            if (!in_array($return['api_measurement']['cmc_value'], ['english', 'metric'])) {
                $return['api_measurement']['cmc_value'] = 'metric';
            }
            self::$config = $return;
        }
        return (($key == '') ? self::$config : (isset(self::$config[$key]['cmc_value']) ? self::$config[$key]['cmc_value'] : false));
    }


    private static function loadMappingArray($entityName)
    {
        return Entity::find()
                ->select('external_id, internal_id')
                ->joinWith('mapping', false)
                ->where(['entity_name' => $entityName])
                ->asArray()
                ->indexBy('external_id')
                ->all();
    }

    private static function downloader()
    {
        return new \OscLink\Downloader(self::getConfigurationArray());

    }

    public static function getOrderStateStatusArray()
    {

        $filename = self::downloader()->getFeed('order_statuses');

        $xml = simplexml_load_file($filename);
        $statuses_only_en = $xml->xpath("//OrdersStatuses/OrdersStatus[language_id[@language='en']]");

        // if english is not exist - try to find the first
        if (empty($statuses_only_en)) {
            $statuses_first = $xml->xpath("//OrdersStatuses/OrdersStatus[language_id]")[0] ?? null;
            if ($statuses_first instanceof \SimpleXMLElement && $statuses_first->language_id instanceof \SimpleXMLElement && !empty($statuses_first->language_id['language'])) {
                $otherLang = $statuses_first->language_id['language'][0];
                $statuses_only_en = $xml->xpath("//OrdersStatuses/OrdersStatus[language_id[@language='$otherLang']]");
            }
        }

        $result = [];
        foreach ($statuses_only_en as $value) {
//            'language' => (string) $value->language_id->attributes()['language'],
            $id = (int) $value->orders_status_id->attributes()['internalId'];
            $name = (string) $value->orders_status_name;
            $result[$id] = ucfirst($name);
        }
        return $result;
    }

    public static function getMappingTable($statusArray, $entityName = '@order_status')
    {
        $res = [];
        $map = self::loadMappingArray($entityName);
//        foreach($statusArray as $id=>$val) {
//            $res[$id] = $map[$id]['internal_id'] ?? -1;
//        }
        foreach($map as $id=>$val) {
            $res[$id] = $map[$id]['internal_id'] ?? '';
        }
        return $res;
    }

    public static function saveMappingTable($valueArray, $entityName = '@order_status')
    {
        self::clearConfigCache();
        $entity_id = Entity::forceEntityId($entityName);
        Mapping::deleteAll(['entity_id' => $entity_id]);
        foreach($valueArray as $key=>$value) {
            if (!empty($value) && !empty($key)) {
                $map = new Mapping();
                $map->entity_id = $entity_id;
                $map->internal_id = $key;
                $map->external_id = $value;
                $map->save(false);
            }
        }
    }


    public static function getLinkProductTaxArray()
    {
        return array(
            2 => 'Taxable Goods',
            4 => 'Shipping',
            6 => 'Tax Exempt'
        );
    }

    public static function getPlatformValueArray($key = '')
    {
        $key = trim($key);
        if (!is_array(self::$platformArray)) {
            $platformArray = [
                'platform_id' => (int) \common\classes\platform::defaultId(),
                'language_id' => (int) \common\classes\language::defaultId(),
                'language_code' => trim(\common\classes\language::get_code(\common\classes\language::defaultId(), true)),
                'currency_id' => (int) \common\helpers\Currencies::getCurrencyId(\Yii::$app->settings->get('currency')),
                'currency_code' => trim(\Yii::$app->settings->get('currency')),
                'affiliate_id' => (int) 0,
                'warehouse_id' => \common\helpers\Warehouses::get_default_warehouse(),
                'supplier_id' => \common\helpers\Suppliers::getDefaultSupplierId(),
                'location_id' => (int) 0
            ];
            $platformSelectArray = false;
            $platformId = (int) self::getConfigurationArray('api_platform');
            if ($platformId > 0) {
                $platformSelectArray = (\common\models\Platforms::find()->alias('p')
                                ->leftJoin(\common\models\Languages::tableName() . ' l', 'l.code = p.default_language')
                                ->leftJoin(\common\models\Currencies::tableName() . ' c', 'c.code = p.default_currency')
                                ->where(['p.platform_id' => $platformId])
                                ->select(['p.platform_id', 'l.languages_id AS language_id', 'l.code AS language_code',
                                    'c.currencies_id AS currency_id', 'c.code AS currency_code'
                                ])
                                ->asArray(true)->one()
                        );
            }
            unset($platformId);
            $platformDefaultArray = (\common\models\Platforms::find()->alias('p')
                            ->leftJoin(\common\models\Languages::tableName() . ' l', 'l.code = p.default_language')
                            ->leftJoin(\common\models\Currencies::tableName() . ' c', 'c.code = p.default_currency')
                            ->where(['p.is_virtual' => 0, 'p.status' => 1])->orderBy(['p.is_default' => SORT_DESC, 'p.platform_id' => SORT_ASC])
                            ->select(['p.platform_id', 'l.languages_id AS language_id', 'l.code AS language_code',
                                'c.currencies_id AS currency_id', 'c.code AS currency_code'])
                            ->asArray(true)->one()
                    );
            if (!is_array($platformSelectArray)) {
                $platformSelectArray = $platformDefaultArray;
            } else {
                if ((int) $platformSelectArray['language_id'] <= 0) {
                    $platformSelectArray['language_id'] = (int) $platformDefaultArray['language_id'];
                    $platformSelectArray['language_code'] = trim($platformDefaultArray['language_code']);
                }
                if ((int) $platformSelectArray['currency_id'] <= 0) {
                    $platformSelectArray['currency_id'] = (int) $platformDefaultArray['currency_id'];
                    $platformSelectArray['currency_code'] = trim($platformDefaultArray['currency_code']);
                }
            }
            unset($platformDefaultArray);
            if (is_array($platformSelectArray)) {
                $platformSelectArray['affiliate_id'] = $platformArray['affiliate_id'];
                $platformSelectArray['warehouse_id'] = $platformArray['warehouse_id'];
                $platformSelectArray['supplier_id'] = $platformArray['supplier_id'];
                $platformSelectArray['location_id'] = $platformArray['location_id'];
                if ((int) $platformSelectArray['language_id'] <= 0) {
                    $platformSelectArray['language_id'] = (int) $platformArray['language_id'];
                    $platformSelectArray['language_code'] = trim($platformArray['language_code']);
                }
                if ((int) $platformSelectArray['currency_id'] <= 0) {
                    $platformSelectArray['currency_id'] = (int) $platformArray['currency_id'];
                    $platformSelectArray['currency_code'] = trim($platformArray['currency_code']);
                }
                $platformArray = $platformSelectArray;
            }
            unset($platformSelectArray);
            $platformArray = [
                'platform_id' => (int) $platformArray['platform_id'],
                'language_id' => (int) $platformArray['language_id'],
                'language_code' => trim($platformArray['language_code']),
                'currency_id' => (int) $platformArray['currency_id'],
                'currency_code' => trim($platformArray['currency_code']),
                'affiliate_id' => (int) $platformArray['affiliate_id'],
                'warehouse_id' => (int) $platformArray['warehouse_id'],
                'supplier_id' => (int) $platformArray['supplier_id'],
                'location_id' => (int) $platformArray['location_id']
            ];
            self::$platformArray = $platformArray;
            unset($platformArray);
        }
        return (($key == '') ? self::$platformArray : (isset(self::$platformArray[$key]) ? self::$platformArray[$key] : null
                )
                );
    }

    private static function checkPrerequisites()
    {
        \common\helpers\AssertUser::assert(ini_get('allow_url_fopen'), 'PHP option <a href="https://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen" target="_blank">allow_url_option</a> must be enabled for this operation. Please correct settings in your php.ini');
    }

// </editor-fold>


}
