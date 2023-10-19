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

namespace OscLink\XML;


use yii\helpers\FileHelper;

class IOAttachment extends Complex
{
    public $location = '';

    public $attach_file;
    public $url;
    public $checksum_sha1;
    public $checksum_md5;

    public $limitMode = [];

    public $options = [];

    public function getAttachmentFileName()
    {
        if ( !empty($this->value) ) {
            $physicalFile = IOCore::get()->getLocalLocation($this->location.'/'.$this->value);
            if ( is_file($physicalFile) ) {
                return $physicalFile;
            }
        }
        return false;
    }

    public function getAttachmentModeVariants()
    {
        return array(/*'value',*/ 'attach_file', 'file_info', 'url', 'inline','checksum_md5','checksum_sha1');
    }


    public function serializeTo(\SimpleXMLElement $parent)
    {
        if ( !empty($this->value) ) {
            //$parent->url = \Yii::getAlias($this->uri.$this->value);
            //$parent->file = \Yii::getAlias($this->path.$this->value);
            if ( !(isset($this->options['noValue']) && $this->options['noValue']===true) ) {
                $parent->value = $this->value;
            }

            $physicalFile = IOCore::get()->getLocalLocation($this->location.'/'.$this->value);
            if ( is_file($physicalFile) && is_array($this->limitMode) )
            {
                if ( (isset($this->options['mergeLimitOptions']) && is_array($this->options['mergeLimitOptions'])) ) {
                    $workModes = array_unique(array_merge($this->options['mergeLimitOptions'],(count($this->limitMode) > 0 ? $this->limitMode : IOCore::get()->getAttachmentModes())));
                }else {
                    $workModes = count($this->limitMode) > 0 ? $this->limitMode : IOCore::get()->getAttachmentModes();
                }
                if ( in_array('attach_file', $workModes) )
                {
                    $parent->attach_file = empty($this->attach_file)?$physicalFile:$this->attach_file;
                }

                if ( in_array('url', $workModes) )
                {
                    $parent->url = IOCore::get()->getPublicLocation($this->location.'/'.$this->value);
                }

                if ( in_array('checksum_md5', $workModes) )
                {
                    $parent->checksum_md5 = md5_file($physicalFile);
                }

                if ( in_array('checksum_sha1', $workModes) )
                {
                    $parent->checksum_sha1 = sha1_file($physicalFile);
                }

                if ( in_array('inline', $workModes) )
                {
                    $parent->inline = base64_encode(file_get_contents($physicalFile));
                }

                if ( in_array('file_info',$workModes) )
                {
                    $parent->filesize = filesize($physicalFile);
                    $parent->last_modified = date('c',filemtime($physicalFile));
                    if ( function_exists('mime_content_type') ) {
                        $parent->content_type = mime_content_type($physicalFile);
                        if (strpos($parent->content_type, 'image/') === 0) {
                            $imageInfo = @getimagesize($physicalFile);
                            $parent->image_width = $imageInfo[0];
                            $parent->image_height = $imageInfo[1];
                        }
                    }
                }
            }
        }
    }

    static public function restoreFrom(\SimpleXMLElement $node, $obj)
    {
        if ( trim($node->value)!=='' || trim($node->attach_file)!=='' || trim($node->url)!=='' || trim($node->inline)!=='' ) {
            if ( !is_object($obj) || !($obj instanceof Complex) ) {
                $obj = IOCore::createObject('IOAttachment');
            }
            $obj->value = strval($node->value);
            if ( trim($node->attach_file)!=='' ) {
                $obj->attach_file = trim($node->attach_file);
                //if ( empty($obj->value) ) $obj->value = basename($obj->attach_file);
            }elseif( trim($node->inline)!=='' ){
                $sourceFile = tempnam(IOCore::get()->getLocalLocation('@attachment_root/'),basename(strval($node->value)));
                $node->attach_file = $sourceFile;
                @file_put_contents($sourceFile, base64_decode($node->inline));
            }
            if ( trim($node->url)!=='' ) {
                $obj->url = trim($node->url);
                //if ( empty($obj->value) ) $obj->value = basename($obj->url);
            }
            if ( trim($node->checksum_md5)!='' ) {
                $obj->checksum_md5 = trim($node->checksum_md5);
            }
            if ( trim($node->checksum_sha1)!='' ) {
                $obj->checksum_sha1 = trim($node->checksum_sha1);
            }

            return $obj;
        }
        return '';
    }

    public function toImportModel()
    {
        $sourceFile = '';

        /*if ( !empty($this->attach_file) ) {
            $sourceFile = IOCore::get()->getLocalLocation('@attachment_root/'.$this->attach_file);
        }else*/if ( !empty($this->url) ) {
            $sourceFile = $this->url;
        }

        if ( !empty($sourceFile) ) {
            if ( empty($this->value) ) {
                $this->value = IOCore::get()->normalizeLocalFileName(basename($sourceFile));
            }
            if (isset($this->options) && !empty($this->options['importVia']) && $this->options['importVia'] === 'File') {
                $_fileParams = [
                    'sourceFile' => $sourceFile,
                ];
                if ( $this->checksum_sha1 ) {
                    $_fileParams['checksum_sha1'] = $this->checksum_sha1;
                }
                return new \common\models\File\Upload($_fileParams);
            } else {
                $this->value = IOCore::get()->normalizeLocalFileName($this->value);
                $origin = $physicalFile = IOCore::get()->getLocalLocation($this->location.'/'.$this->value);

                IOCore::get()->download($sourceFile, $physicalFile);
                if ($origin != $physicalFile) {
                    \OscLink\Logger::print("Warning while saving images: originFN=$origin but result=$physicalFile");
                }
                if ($pos = strpos($this->location, '/')) {
                    $this->value = rtrim(substr($this->location, $pos+1), '/') . '/' . $this->value;
                }
            }
        }

        return parent::toImportModel();
    }


}
