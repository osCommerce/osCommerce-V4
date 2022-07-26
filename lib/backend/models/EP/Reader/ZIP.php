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

namespace backend\models\EP\Reader;


use yii\base\BaseObject;

class ZIP extends BaseObject implements ReaderInterface
{
    protected $numFiles = 0;
    protected $fileCursor = 0;
    public $filename;

    protected $file_handle;

    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        }catch (\Exception $ex){}
    }

    public function readColumns()
    {
        return [];
    }

    public function read()
    {
        if (!$this->file_handle) {
            $this->file_handle = new \ZipArchive();
            $this->file_handle->open($this->filename);
            $this->numFiles = $this->file_handle->numFiles;
            $this->fileCursor = 0;
        }
        if ( $this->file_handle && $this->fileCursor<$this->file_handle->numFiles) {
            $filename = $this->file_handle->getNameIndex($this->fileCursor);
            $stream = $this->file_handle->getStream($filename);
            $this->fileCursor++;
            if ( preg_match('#[/|\\\]$#',$filename) ) {
                // skip directory
                return $this->read();
            }
            return [
                'filename' => $filename,
                'stream' => $stream,
            ];
        }
        return false;
    }

    public function currentPosition()
    {
        return $this->fileCursor;
    }

    public function setDataPosition($position)
    {
        $this->fileCursor = $position;
    }

    public function getProgress()
    {
        $filePosition = $this->currentPosition();
        $percentDone = min(100,($filePosition/filesize($this->numFiles))*100);
        return number_format(  $percentDone,1,'.','');
    }


}