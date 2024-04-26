<?php

namespace common\helpers;

class Smarty
{

    public static function renderStr($strTemplate, $params = [])
    {
        $smarty = new \Smarty();

        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $smarty->assign($key, $value);
            }
        }

        return $smarty->fetch('string:' . $strTemplate);
    }

}