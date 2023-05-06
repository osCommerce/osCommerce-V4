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
        $counter = \common\models\Hooks::find()->count();
        if ($counter == 0) {
            self::rebuildHooks();
        }
       
        $response = [];
        $queryRaw = \common\models\Hooks::find()
                ->where(['page_name' => $pageName])
                ->andWhere(['page_area' => $pageArea])
                ->orderBy('sort_order', 'hook_id');
        foreach ($queryRaw->each() as $row) {
            if (file_exists($row->extension_file)) {
                $response[] = $row->extension_file;
            } else {
                \Yii::warning("File for hook $pageName area $pageArea is not found: $row->extension_file");
            }
        }
        return $response;
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
    }

    public static function resetHooks()
    {
        \Yii::$app->getDb()->createCommand("TRUNCATE " . \common\models\Hooks::tableName())->execute();
    }


    public static function isHookExists($pageName, $pageArea = '')
    {
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
        return defined('YII_DEBUG') && YII_DEBUG && file_exists(\Yii::getAlias('@common/extensions/methodology.txt')) && is_array(self::getAvailableHooks());
    }

    private static function getHookNames()
    {
        if (($file = @file_get_contents(\Yii::getAlias('@common/extensions/methodology.txt'))) === false) {
            throw new \Exception('Error while reading methodology.txt: ' . (error_get_last()['message'] ?? 'Unknown error'));
        }

        if (!preg_match('/<HOOKS>(.*?)<\/HOOKS>/si', $file, $find)) {
            throw new \Exception('Tag HOOKS not found');
        }

        if (!preg_match_all("#'(.*)'\s*,\s*'(.*)'#", $find[1], $out/*, PREG_PATTERN_ORDER*/)) {
            throw new \Exception('Wrong structure inside tag HOOKS');
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
            \Yii::warning('Error while get hooks names: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
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
