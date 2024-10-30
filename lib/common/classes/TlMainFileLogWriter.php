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
        
        $msg = parent::getContextMessage();
        $msg = preg_replace_callback(
            "/'email_address' => '(.*)'/",
            function ($matches) {
                $email = $matches[1];
                $start = substr($email, 0, 2);
                $end = substr($email, -2);
                $maskedEmail = $start . str_repeat('*', 5) . $end;
                return "'email_address' => '" . $maskedEmail . "'";
            },
            $msg
        );
        $msg = preg_replace(
            "/'password' => '(.*)'/",
            sprintf("'password' => '%s'", str_repeat('*', 5)),
            $msg
        );
        // remove any other emails
        $msg = preg_replace_callback(
            "/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/",
            function ($matches) {
                $email = $matches[0];
                $start = substr($email, 0, 2);
                $end = substr($email, -2);
                $maskedEmail = $start . str_repeat('*', 5) . $end;
                return $maskedEmail;
            },
            $msg
        );
        
        
        return $s . $msg;
    }

}