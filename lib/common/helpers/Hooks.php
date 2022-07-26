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
            }
        }
        return $response;
    }
    
    public static function rebuildHooks()
    {
        self::resetHooks();
        $path = \Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR;
        if ($dir = @dir($path)) {
            while ($file = $dir->read()) {
                if ($ext = \common\helpers\Acl::checkExtension($file, 'getAdminHooks')) {
                    $Items = $ext::getAdminHooks();
                    if (is_array($Items)) {
                        foreach ($Items as $item) {
                            $record = new \common\models\Hooks();
                            $record->loadDefaultValues();
                            $record->page_name = $item['page_name'];
                            $record->page_area = $item['page_area'];
                            $record->sort_order = ($item['sort_order'] ?? 100);
                            $record->extension_name = $file;
                            $record->extension_file = $item['extension_file'];
                            $record->save(false);
                        }
                    }
                    unset($Items);
                }
            }
            $dir->close();
        }
    }
    
    public static function resetHooks()
    {
        \Yii::$app->getDb()->createCommand("TRUNCATE " . \common\models\Hooks::tableName())->execute();
    }

}
