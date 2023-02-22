<?php

namespace common\classes;

class TlMainFileLogWriter extends \yii\log\FileTarget
{

    protected function getContextMessage()
    {
        $sysInfo = \common\helpers\System::get_system_information();
        $sysInfo['ocCommerce version'] = defined('PROJECT_VERSION')? PROJECT_VERSION : 'unknown' ;
        $sysInfo['ocCommerce revision'] = defined('MIGRATIONS_DB_REVISION')? MIGRATIONS_DB_REVISION : 'unknown' ;
        $s = '';
        foreach($sysInfo as $key => $info) {
            if (!empty($info) && $info != 'unknown') {
                $s .= "$key: $info\n";
            }
        }
        return $s . parent::getContextMessage();
    }

}