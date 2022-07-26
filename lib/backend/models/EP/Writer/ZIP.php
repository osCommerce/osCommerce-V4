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

namespace backend\models\EP\Writer;


use backend\models\EP\Exception;
use yii\base\BaseObject;

class ZIP extends BaseObject implements WriterInterface
{

    public $filename;
    public $feed;
    public $feedWriter;

    protected $tmpfilename = false;
    /**
     * @var \ZipArchive
     */
    protected $zip;
    protected $_first_write = true;

    protected $columns = [];

    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        }catch (\Exception $ex){}
    }

    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    public function write(array $writeData)
    {
        if ( $this->_first_write ) {
            $this->_first_write = false;
            $this->zip = new \ZipArchive();
            if ( $this->filename=='php://output' ) {
                $this->tmpfilename = tempnam(sys_get_temp_dir(), 'ep_zip_write');
                $archiveStatus = $this->zip->open($this->tmpfilename, \ZipArchive::OVERWRITE);
            }else {
                $archiveStatus = $this->zip->open($this->filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            }
            if ( $archiveStatus!==true ) {
                throw new Exception('Create file error ['.$archiveStatus.']');
            }
            if ( is_array($this->feed) && !empty($this->feed['feed_filename']) && $this->feed['format'] ) {
                $this->feed['temporary_filename'] = tempnam(sys_get_temp_dir(), 'ep_zip_sub_feed');
                $this->feedWriter = \Yii::createObject([
                    'class' => 'backend\\models\\EP\\Writer\\'.$this->feed['format'],
                    'filename' => $this->feed['temporary_filename'],
                ]);
                $this->feedWriter->setColumns($this->columns);
            }
        }
        if (substr(strval(key($writeData)),0,1)==':') {
            if (isset($writeData[':feed_data'])) {
                $this->feedWriter->write($writeData);
            }
            if (isset($writeData[':attachments'])) {
                foreach($writeData[':attachments'] as $writeFile) {
                    $this->checkArchiveDirectory($writeFile['localname']);
                    $this->zip->addFile($writeFile['filename'], $writeFile['localname']);
                }
            }
            return;
        }else{
            if ( !isset($writeData[0]) && $this->feedWriter ) {
                $this->feedWriter->write($writeData);
            }else{
                foreach ($writeData as $writeFile) {
                    if (isset($writeFile['localname'])) {
                        $this->checkArchiveDirectory($writeFile['localname']);
                        $this->zip->addFile($writeFile['filename'], $writeFile['localname']);
                    } elseif (isset($writeFile['string'])) {
                        $this->checkArchiveDirectory($writeFile['filename']);
                        $this->zip->addFromString($writeFile['filename'], $writeFile['string']);
                    }
                }
            }
        }
    }

    protected function checkArchiveDirectory($archiveFilename)
    {
        $archiveDir = dirname($archiveFilename).'/';
        if ( dirname($archiveFilename)!='./' ) {
            if (false===$this->zip->locateName($archiveDir)){
                $this->zip->addEmptyDir($archiveDir);
            }
        }
    }

    public function close()
    {
        if ( $this->zip ) {
            if ( $this->feedWriter && is_array($this->feed) && !empty($this->feed['feed_filename']) ) {
                $this->feedWriter->close();

                $this->zip->addFile($this->feed['temporary_filename'], $this->feed['feed_filename']);

                $this->zip->close();

                if (is_array($this->feed) && !empty($this->feed['temporary_filename']) && is_file($this->feed['temporary_filename']) ) {
                    @unlink($this->feed['temporary_filename']);
                }

            }else{
                $this->zip->close();
            }

            //$this->file_handle = null;
        }

        if ( $this->filename=='php://output' && is_file($this->tmpfilename) ) {
            readfile($this->tmpfilename);
            unlink($this->tmpfilename);
        }

    }
}