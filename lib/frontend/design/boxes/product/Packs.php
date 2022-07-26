<?php
namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Packs extends Widget {

    public $product;

    public function init() {
        parent::init();
    }

    public function run() {
        if (\common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
            if ((isset($this->product['packaging']) && $this->product['packaging']>0) || (isset($this->product['packs']) && $this->product['packs'] > 0)) {
                return IncludeTpl::widget(['file' => 'boxes/product/packs.tpl', 'params' => ['product' => $this->product]]);
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

}