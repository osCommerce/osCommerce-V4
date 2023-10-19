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
    private $zipPath;
    private $zipFileName;
    private $path;

    public function __construct()
    {
        parent::__construct();
        $this->zipPath = Yii::getAlias('@ext-error-log-viewer', false).DIRECTORY_SEPARATOR."tmp";
        $this->path = DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."logs";
    }

    private static function sourceList()
    {
        return [
            'frontend',
            'backend',
            'console'
        ];
    }

    public static function getFile($sourceFile)
    {
        $sourceFile = str_replace('|', '.', $sourceFile);
        $tmp = explode("/", $sourceFile);
        if(count($tmp) > 2){
            throw new \Exception("Undefined source/file");
        }
        $source = trim($tmp[0]);
        $fileName = trim($tmp[1]);

        $file = new \stdClass();
        $file->error = false;
        if(!in_array($source, self::sourceList()))
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
            $file->mask = str_replace('.', '|', $sourceFile);
            $file->fullPath = $path.DIRECTORY_SEPARATOR.$fileName;
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
        foreach (self::sourceList() as $source)
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

    public function Zipping()
    {
        $this->zipFileName = 'logs_'.Yii::$app->session->get('login_id', 0).'_'.time().'.zip';
        if(!is_dir($this->zipPath)){
            if(!@mkdir($this->zipPath)){
                return ['status' => 'error', 'description' => EXT_ELV_ERR_CREATE_TMP];
            }
        }
        try {
            $zip = new \ZipArchive();
            if($err = $zip->open($this->zipPath . DIRECTORY_SEPARATOR . $this->zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true)
            {
                return ['status' => 'error', 'description' => $err];
            }
            foreach (self::sourceList() as $source)
            {
                if(!file_exists(Yii::getAlias('@'.$source).$this->path) && !is_dir(Yii::getAlias('@'.$source).$this->path)) continue;
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(realpath(Yii::getAlias('@'.$source).$this->path)),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = $source.DIRECTORY_SEPARATOR.$file->getFilename();
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
            if(!$zip->close()){
                return ['status' => 'error', 'description' => "Check permission on dir: ".$this->zipPath];
            }
            return ['status' => 'ok', 'description' => $this->zipPath.DIRECTORY_SEPARATOR.$this->zipFileName];

        } catch (\Exception $ex)
        {
            return ['status' => 'error', 'description' => $ex->getMessage()];
        }
    }

    public function DeleteOldZip()
    {
        if(file_exists($this->zipPath) && is_dir($this->zipPath)) {
            try{
                foreach (scandir($this->zipPath) as $file) {
                    if (!is_file($this->zipPath . DIRECTORY_SEPARATOR . $file)) continue;
                    if ((time() - filemtime($this->zipPath . DIRECTORY_SEPARATOR . $file)) > 86400) unlink($this->zipPath . DIRECTORY_SEPARATOR . $file);
                }
                return true;
            }catch (\Exception $ex)
            {
                return $ex->getMessage();
            }
        }
        return true;
    }
}