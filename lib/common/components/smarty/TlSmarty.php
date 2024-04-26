<?php

namespace common\components\smarty;

class TlSmarty extends \yii\smarty\Extension
{
    public function __construct($viewRenderer, $smarty){
        
        parent::__construct($viewRenderer, $smarty);
        $smarty->registerPlugin('modifier', 'json_encode', 'json_encode');
        $smarty->registerPlugin('modifier', 'is_array', 'is_array');
        $smarty->registerPlugin('modifier', 'intval', 'intval');
        $smarty->registerPlugin('modifier', 'trim', 'trim');
        $smarty->registerPlugin('modifier', 'constant', 'constant');
    }
  
}