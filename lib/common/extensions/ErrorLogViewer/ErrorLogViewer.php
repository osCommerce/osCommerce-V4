<?php

/**
* This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
* @author Dmitry Kodinets
* @email dkodynets@holbi.co.uk
* @link https://www.holbi.co.uk
* @copyright Copyright (c) 2000-2022 osCommerce LTD
*
* Released under the GNU General Public License
* For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*/

namespace common\extensions\ErrorLogViewer;

use Yii;

class ErrorLogViewer extends \common\classes\modules\ModuleExtensions
{

    public static function getFile($sourceFile)
    {
        $tmp = explode("/", $sourceFile);
        if(count($tmp) > 2){
            throw new \Exception("Undefined source/file");
        }
        $source = trim($tmp[0]);
        $fileName = trim($tmp[1]);



        $file = new \stdClass();
        $file->error = false;
        if(!in_array($source, array('frontend', 'backend', 'console')))
        {
            $file->error = true;
            $file->errorMessage = EXT_ELV_ERR_SOURCE;
            return $file;
        }
        $path = \Yii::getAlias('@'.$source).DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."logs";

        if(file_exists($path.DIRECTORY_SEPARATOR.$fileName) && is_file($path.DIRECTORY_SEPARATOR.$fileName))
        {
            $file->sourceFile = $sourceFile;
            $file->source = $source;
            $file->name = $fileName;
            $file->fullPath = $path.DIRECTORY_SEPARATOR.$fileName;
            $file->content = file_get_contents(htmlspecialchars($file->fullPath));
            $file->sizeText = self::formatSize(filesize($file->fullPath));
            $file->size = filesize($file->fullPath);
            $file->date = filemtime($file->fullPath) ? date("Y-m-d H:i:s", filemtime($file->fullPath)) : 'Undefined';
        }else{
            $file->error = true;
            $file->errorMessage = EXT_ELV_ERR_FILE;
        }

        return $file;
    }

    public static function getFiles($source)
    {
        $path = \Yii::getAlias('@'.$source).DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."logs";
        $files = array();
        if(is_dir($path))
        {
            foreach(scandir($path) as $file)
            {
                if(!is_file($path.DIRECTORY_SEPARATOR.$file)) continue;
                $files[] = self::getFile($source.'/'.$file);
            }
            return $files;
        }
        return false;
    }

    public static function deleteAll()
    {
        $sourceList = array('backend', 'frontend', 'console');
        foreach ($sourceList as $source)
        {
            $path = \Yii::getAlias('@'.$source).DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."logs";
            if(is_dir($path))
            {
                foreach(scandir($path) as $file)
                {
                    if(!is_file($path.DIRECTORY_SEPARATOR.$file)) continue;
                    unlink($path.DIRECTORY_SEPARATOR.$file);
                }
            }
        }
    }

    private static function formatSize($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}