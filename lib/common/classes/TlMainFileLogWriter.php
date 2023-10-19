<?php

namespace common\classes;

class TlMainFileLogWriter extends \yii\log\FileTarget
{

    protected function getContextMessage()
    {
        $sysInfo = \common\helpers\System::getSysInfo();
        $s = "\n";
        foreach($sysInfo as $key => $info) {
            if (!empty($info) && $info != 'unknown') {
                $s .= "$key: $info\n";
            }
        }
        return $s . parent::getContextMessage();
    }

}