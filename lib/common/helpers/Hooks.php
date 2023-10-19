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

namespace common\helpers;

use Yii;

class Hooks
{
 
   public static function getList($pageName, $pageArea = '')
    {
        self::checkHookExists($pageName, $pageArea, " in common/extensions/methodology.txt\nDear developer - don't forget add your hook into this file");
        self::rebuildHooksIfNeeded();

        $response = [];
        $queryRaw = \Yii::$app->getCache()->getOrSet($pageName . '|' . $pageArea, function () use ($pageName, $pageArea) {
            return \common\models\Hooks::find()->where(['page_name' => $pageName, 'page_area' => $pageArea])->orderBy('sort_order, hook_id')->asArray()->all();
        },0, new \yii\caching\TagDependency(['tags'=>['hooks_all']]));

        foreach ($queryRaw as $row) {
            if (\common\helpers\Extensions::isAllowed($row['extension_name'])) { // mostly for disabled
                if (file_exists($row['extension_file'])) {
                    \common\helpers\Dbg::ifDefined($row['extension_name'])::logf('Hook will be called (ext=%s, page=%s, area=%s)', $row['extension_name'], $pageName, $pageArea);
                    $response[] = $row['extension_file'];
                } else {
                    \common\helpers\Php::throwOrLog("File for hook $pageName area $pageArea is not found: " . $row['extension_file']);
                }
            }
        }
        return $response;
    }


    private static $hasRecords = null;

    private static function rebuildHooksIfNeeded()
    {
        if (is_null(self::$hasRecords)) {
            self::$hasRecords = \common\models\Hooks::find()->select(new \yii\db\Expression('1'))->limit(1)->exists();
        }
        if (!self::$hasRecords) {
            self::$hasRecords = true;
            self::buildHooks();
        }
    }

    private static function getDefHookPath($extCode)
    {
        return \Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $extCode . DIRECTORY_SEPARATOR . 'hooks' . DIRECTORY_SEPARATOR;
    }

    public static function unregisterHooks($extCode)
    {
        \common\models\Hooks::deleteAll(['extension_name' => $extCode]);
    }

    public static function registerHooks($Items, $extCode)
    {
        $defPath = self::getDefHookPath($extCode);
        if (is_array($Items)) {
            foreach ($Items as $item) {
                $record = \common\models\Hooks::findOne(['page_name' => $item['page_name'], 'page_area' => $item['page_area'] ?? '', 'sort_order' => $item['sort_order'] ?? 100, 'extension_name' => $extCode]);
                if (empty($record)) {
                    $record = new \common\models\Hooks(); // there is no sense to do find before
                    $record->loadDefaultValues();
                }
                $record->page_name = $item['page_name'];
                $record->page_area = $item['page_area'] ?? '';
                self::checkHookExists($record->page_name, $record->page_area, " for module $extCode");
                $record->sort_order = ($item['sort_order'] ?? 100);
                $record->extension_name = $extCode;
                if (empty($item['extension_file'])) {
                    $baseName = $item['page_name'] . '.' . (empty($item['page_area'])? 'php' : ($item['page_area'] . '.tpl'));
                    $record->extension_file = $defPath . str_replace('/', '.', $baseName);
                } else {
                    $record->extension_file = $item['extension_file'];
                }
                if (!file_exists($record->extension_file)) {
                    \Yii::warning("Registering hook for $extCode: file '$record->extension_file' not exists");
                }
                try {
                    $record->save(false);
                } catch (\Exception $e) {
                    \Yii::warning("Registering hook for $extCode db error: " . $e->getMessage());
                }
            }
        }
    }

    public static function rebuildHooks()
    {
          self::resetHooks();
          self::buildHooks();
    }

    private static function buildHooks()
    {
        if (\Yii::$app->mutex->acquire('build-hooks')) {
            try {
                $path = \Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR;
                if ($dir = @dir($path)) {
                    while ($file = $dir->read()) {
                        /** @var \common\classes\modules\ModuleExtensions $ext */
                        if (($ext = \common\helpers\Acl::checkExtension($file, 'getAdminHooks')) && \common\helpers\Extensions::isInstalled($ext::getModuleCode())) {
                            self::registerHooks($ext::getAdminHooks(), $file);
                        }
                    }
                    $dir->close();
                }
            } finally {
                Yii::$app->mutex->release('build-hooks');
                self::clearCache();
            }
        }
    }

    public static function resetHooks()
    {
        \Yii::$app->getDb()->createCommand("TRUNCATE " . \common\models\Hooks::tableName())->execute();
    }

    private static function clearCache()
    {
        \yii\caching\TagDependency::invalidate(\Yii::$app->cache, 'hooks_all');
    }

    private static $depricatedHooks = [
        ['page_name' => 'Order', 'page_area' => 'save_order/before'],
        ['page_name' => 'Order', 'page_area' => 'save_order/after'],
        ['page_name' => 'Order', 'page_area' => 'notify_customer'],
        ['page_name' => 'account/order_history_info', 'page_area' => 'order-product'],
        ['page_name' => 'checkout/index', 'page_area' => ''],
        ['page_name' => 'checkout/process', 'page_area' => ''],
        ['page_name' => 'checkout/after-process', 'page_area' => ''],
        ['page_name' => 'checkout/success', 'page_area' => ''],
        ['page_name' => 'sceleton/register-href-lang', 'page_area' => ''],
        ['page_name' => 'sceleton/set-meta', 'page_area' => ''],
        ['page_name' => 'catalog/search-suggest', 'page_area' => ''],
    ];

    public static function isHookExists($pageName, $pageArea = '')
    {
        if (!empty(array_filter(self::$depricatedHooks, function($val) use($pageName, $pageArea) { return $val['page_name'] === $pageName && $val['page_area'] === $pageArea;} ))) {
            return true;
        }
        $hooks = self::getAvailableHooks();
        return is_array($hooks) && !empty(array_filter($hooks, function($val) use($pageName, $pageArea) { return $val['page_name'] === $pageName && $val['page_area'] === $pageArea;} ));
    }
    public static function checkHookExists($pageName, $pageArea = '', $suffixMessage = '')
    {
        if (self::canCheckHook()) {
            \common\helpers\Assert::assert(self::isHookExists($pageName, $pageArea), "There is no hook '$pageName', '$pageArea'" . $suffixMessage);
        }
    }

    public static function canCheckHook()
    {
        return defined('YII_DEBUG') && YII_DEBUG && \common\helpers\System::isDevelopment() && file_exists(\Yii::getAlias('@common/extensions/methodology.txt')) && is_array(self::getAvailableHooks());
    }

    private static function getHookNames()
    {
        if (($file = @file_get_contents(\Yii::getAlias('@common/extensions/methodology.txt'))) === false) {
            throw new \Exception('Error while reading methodology.txt: ' . (error_get_last()['message'] ?? 'Unknown error'));
        }

        if (!preg_match('/<HOOKS>\s*(.*)<\/HOOKS>/si', $file, $find)) {
            throw new \Exception('Tag HOOKS not found');
        }

        $res = preg_match_all("#^'([-\w/]*)'\s*,\s*'([-\w/]*)'#mx", $find[1], $out/*, PREG_PATTERN_ORDER*/);
        if (!$res) {
            throw new \Exception('Wrong structure inside tag HOOKS: ' . $res === false? \common\helpers\Php8::pregLastErrorMsg() : 'Hooks not found');
        }

        $res = [];
        foreach ($out[1] as $key => $hookName) {
            $res[] = ['page_name' => $hookName, 'page_area' => $out[2][$key]];
        }
        return $res;
    }

    private static function getHookNamesSafe()
    {
        try {
            return self::getHookNames();
        } catch (\Throwable $e) {
            \common\helpers\Php::handleErrorProd($e, 'Error while get hooks names');
            return null;
        }
    }

    private static $availableHooks = null;
    private static function getAvailableHooks()
    {
        if (is_null(self::$availableHooks)) {
            self::$availableHooks = self::getHookNamesSafe();
        }
        return self::$availableHooks;
    }

}
