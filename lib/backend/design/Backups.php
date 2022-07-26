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

namespace backend\design;

use yii\helpers\FileHelper;

class Backups
{
    public static function create($themeName, $backupId)
    {
        $path = DIR_FS_CATALOG . 'lib'
            . DIRECTORY_SEPARATOR . 'backend'
            . DIRECTORY_SEPARATOR . 'design'
            . DIRECTORY_SEPARATOR . 'backups'
            . DIRECTORY_SEPARATOR . $themeName;
        FileHelper::createDirectory($path, 0777);
        $zipName = $path . DIRECTORY_SEPARATOR . $backupId . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipName, \ZipArchive::CREATE) === TRUE) {

            foreach ([$themeName, $themeName . '-mobile'] as $theme) {
                $json = \backend\design\Theme::getThemeJson($theme);
                $zip->addFromString ($theme . '.json', $json);
            }

            $zip->close();
        }
    }

    public static function backupRestore($backupId, $themeName)
    {
        $zipName = DIR_FS_CATALOG . 'lib'
            . DIRECTORY_SEPARATOR . 'backend'
            . DIRECTORY_SEPARATOR . 'design'
            . DIRECTORY_SEPARATOR . 'backups'
            . DIRECTORY_SEPARATOR . $themeName
            . DIRECTORY_SEPARATOR . $backupId . '.zip';

        foreach ([$themeName, $themeName . '-mobile'] as $theme) {
            $zipText = file_get_contents('zip://' . $zipName . '#' . $theme . '.json');
            $themeArray = json_decode($zipText, true);

            if (is_array($themeArray)){
                Theme::importTheme($themeArray, $theme);
                Style::createCache($theme);
            }
        }
    }

    public static function delete($backupId)
    {
        if (!$backupId) {
            return false;
        }

        $designBackup = \common\models\DesignBackups::findOne(['backup_id' => $backupId]);

        if (!$designBackup) {
            return false;
        }

        $designBackup->delete();

        $zipName = DIR_FS_CATALOG . 'lib'
            . DIRECTORY_SEPARATOR . 'backend'
            . DIRECTORY_SEPARATOR . 'design'
            . DIRECTORY_SEPARATOR . 'backups'
            . DIRECTORY_SEPARATOR . $designBackup->theme_name
            . DIRECTORY_SEPARATOR . $backupId . '.zip';
        if (is_file($zipName)) {
            unlink($zipName);
        }
    }
}