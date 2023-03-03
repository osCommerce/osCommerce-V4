<?php

namespace common\classes;

class TlMainFileLogWriter extends \yii\log\FileTarget
{

    protected function getContextMessage()
    {
        $sysInfo = \common\helpers\System::get_system_information();
        $sysInfo['osCommerce version'] = defined('PROJECT_VERSION')? PROJECT_VERSION : 'unknown' ;
        $sysInfo['osCommerce revision'] = defined('MIGRATIONS_DB_REVISION')? MIGRATIONS_DB_REVISION : 'unknown' ;
        $s = '';
        foreach($sysInfo as $key => $info) {
            if (!empty($info) && $info != 'unknown') {
                $s .= "$key: $info\n";
            }
        }
        return $s . parent::getContextMessage();
    }

}