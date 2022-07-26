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

namespace common\api\models\XML;


use yii\helpers\FileHelper;

class IOGalleryAttachment extends IOAttachment
{
    public $record;
    public $archiveFileName;

    public static function canUseArchiveName($name, $physicalFile)
    {
        static $pool = [];
        $checkFile_sha1 = '';
        if ( isset($pool[$name]) ) {
            if ( empty($pool[$name]['sha1']) ) $pool[$name]['sha1'] = sha1_file($pool[$name]['file']);
            $checkFile_sha1 = sha1_file($physicalFile);
            if ( $checkFile_sha1!==$pool[$name]['sha1'] ) {
                return false;
            }else{
                return true;
            }
        }

        $pool[$name] = ['file'=>$physicalFile,'sha1'=>$checkFile_sha1];

        return true;
    }

    public function getAttachmentFileName()
    {
        $AttachmentFileName = parent::getAttachmentFileName();

        if ( !empty($this->value) && $AttachmentFileName ) {
            $this->archiveFileName = $this->value;
            if ( is_object($this->record) && !empty($this->record->orig_file_name) ) {
                $this->archiveFileName = $this->record->orig_file_name;
                if(!static::canUseArchiveName($this->archiveFileName, $AttachmentFileName)){
                    $this->archiveFileName = implode('_', $this->record->getPrimaryKey(true)).'_'.$this->record->orig_file_name;
                }
            }
        }

        return $AttachmentFileName;
    }

}
