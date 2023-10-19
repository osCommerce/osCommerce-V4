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

namespace backend\design\editor;


use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class OrderTotals extends Widget {

    /** @var \common\services\OrderManager */
    public $manager;
    private $js = '';
    private $lines = [];
    private $readonly;
    
    public function init(){
        parent::init();
    }
    
    public function run(){
        /** @var \common\classes\shopping_cart $cart */
        $cart = $this->manager->getCart();
        $currencies = Yii::$container->get('currencies');
        $currency_value = $currencies->currencies[$cart->currency]['value'];
        $this->readonly = $this->manager->getTotalCollection()->readonly;
        $output = $this->manager->getTotalOutput(false);
        $modules = $this->manager->getTotalCollection()->getEnabledModules();
        
        $subtotal = [];
        foreach ($output as $total) {
            if ($total['code'] == 'ot_subtotal') {
                $subtotal = $total;
            }
        }

        $unused_modules = [];

        if (is_array($modules)){
            foreach ($modules as $module){
                if (is_array($module->output) && count($module->output)) {
                    for ($i = 0; $i < count($module->output); $i++) {
                        $this->js .= 'var ' . $module->code . ' = {prefix: "' . ($module->output[$i]['prefix'] ?? null) . '", sort_order:"' . $module->sort_order . '"};' . "\n";
                        $total_value_ex = $module->output[$i]['value_exc_vat'];
                        $total_value_in = $module->output[$i]['value_inc_tax'];
                        if (($_t = $cart->getTotalKey($module->code)) !== false && (!in_array($module->code, $this->readonly) || $module->code == 'ot_tax' || $module->code == 'ot_paid')) {
                            if (is_array($_t)) {
                                $total_value_ex = (float)$_t['ex'];
                                $total_value_in = (float)$_t['in'];
                            } else {
                                $total_value_ex = (float)$_t;
                                $total_value_in = (float)$_t;
                            }
                        }

                        if (in_array($module->code, $this->readonly)) {
                            $this->lines[] = $this->drawReadonlyRow($module, $i);
                        } else {
                            if (isset($module->output[$i]['tax_class_id']) && $module->output[$i]['tax_class_id']){
                                $rate = \common\helpers\Tax::get_tax_rate($module->output[$i]['tax_class_id']);
                                $this->js .= $module->code . '.diff = "' . ($rate? (100 + $rate)/100 : 1) . '";' . "\n";
                            } elseif ($total_value_in == 0 && $total_value_ex == 0) {
                                // if empty values - take diff from subtotal
                                $this->js .= $module->code . '.diff = "' . $subtotal['value_inc_tax'] / ($subtotal['value_exc_vat'] != 0 ? $subtotal['value_exc_vat'] : 1) . '";' . "\n";
                            } else {
                                $this->js .= $module->code . '.diff = "' . $total_value_in / ($total_value_ex != 0 ? $total_value_ex : 1) . '";' . "\n";
                            }
                            $this->lines[] = $this->drawEditabelRow($module, $i, ['ex' => $total_value_ex,'in' => $total_value_in]);
                        }
                    }
                } else {
                    $unused_modules[$module->code] = $module->title;
                }
            }
        }
            
        unset($unused_modules['ot_bonus_points']);

        $this->js .= " var list_modules='" . str_replace(array("\n", "\r"), array("\\n", "\\r"), \yii\helpers\Html::checkboxList('new_module[]', '', $unused_modules, ['class' => "form-control new-modules"])) . "'; ";
        $paid_diff = 0;
        $_paid = $cart->getTotalKey('ot_paid');
        if ($_paid){
            $paid_diff = $_paid['in'];
        }
        
        return $this->render('order-totals', [
            'manager' => $this->manager,
            'modules' => $modules,
            'lines' => $this->lines,
            'js' => $this->js,
            'proposeToPaid' => Formatter::priceClear($this->manager->getOrderInstance()->info['total_inc_tax']-$paid_diff, 0, 1, $cart->currency, $currency_value),
            'currency' => $cart->currency,
            'urlCheckout' => Yii::$app->urlManager->createAbsoluteUrl(array_merge(['editor/checkout'], Yii::$app->request->getQueryParams())),
        ]);
        
        
    }
    
    private function drawReadOnlyRow($module, $index){
        $cart = $this->manager->getCart();
        $currencies = Yii::$container->get('currencies');
        $currency_value = $currencies->currencies[$cart->currency]['value'];
        switch($module->code){
            case 'ot_subtotal':
            case 'ot_subtax':
                return [
                        (isset($module->output[$index]['title']) ? $module->output[$index]['title'] : $module->title),
                        Formatter::price($module->output[$index]['value_exc_vat'], 0, 1, $cart->currency, $currency_value),
                        Formatter::price($module->output[$index]['value_inc_tax'], 0, 1, $cart->currency, $currency_value),
                        '',
                    ];
                break;
            case 'ot_tax':
                return [
                        (isset($module->output[$index]['title']) ? $module->output[$index]['title'] : $module->title),
                        '',
                        Formatter::price($module->output[$index]['value_inc_tax'], 0, 1, $cart->currency, $currency_value),
                        (abs($module->output[$index]['difference']??null) != 0 ? '<a href="javascript:void(0)" class="adjust_tax" data-prefix="' . (is_numeric($module->output[$index]['difference']) && $module->output[$index]['difference'] >= 0 ? "+" : "-") . '"><div>' . sprintf(TEXT_ADJUST_TAX, ($module->output[$index]['difference']>0?"+":"-") . abs($module->output[$index]['difference']??null)) . '<div class="adjust_explanation">' . TEXT_ADJUST_EXPLANATION . '</div></div></a>' : ''),
                    ];
                break;
            case 'ot_total':
                return [
                        '<div class="total-row">'.(isset($module->output[$index]['title']) ? $module->output[$index]['title'] : $module->title).'</div>',
                        '',
                        Formatter::price($module->output[$index]['value_inc_tax'], 0, 1, $cart->currency, $currency_value).
                        Html::hiddenInput("update_totals[" . $module->code . "]", $module->output[$index]['value_inc_tax']),
                        '',
                    ];
                break;
            case 'ot_paid':
            case 'ot_due':
            case 'ot_refund':
                $info = false;
                if ($module->code == 'ot_paid'){
                    $info = $cart->getPaidInfo();
                    if ($info){
                        $info = $info['info'];
                        $info = implode("<br>", \yii\helpers\ArrayHelper::getColumn($info, 'comment'));
                    } 
                }
                $allowEditPaid = false;
                if (\common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_EDIT_PAID']) ) {
                    $allowEditPaid = true;
                }
                return [
                        (isset($module->output[$index]['title']) ? $module->output[$index]['title'] : $module->title),
                        '',
                        Formatter::price($module->output[$index]['value_inc_tax'], 0, 1, $cart->currency, $currency_value).
                        ($module->code == 'ot_paid'? Html::hiddenInput("update_totals[" . $module->code . "]", $module->output[$index]['value_inc_tax']) : ''),
                        ($module->code == 'ot_paid' && $allowEditPaid ? '<div class="totals edit-paid"><i class="icon-pencil"></i></div>'.
                                ($info ? '<div class="totals comment"><i class="icon-comment-alt">'.
                                '<div class="paid-total-info">'. $info.'</div>'
                                .'</i>': '<div class="totals comment"></div>')
                            :''),
                    ];
                break;
        }        
    }
    
    private function drawEditabelRow($module, $index, array $params){
        $cart = $this->manager->getCart();
        $currencies = Yii::$container->get('currencies');
        $currency_value = $currencies->currencies[$cart->currency]['value'];
        if ($module->code == 'ot_custom'){
            
        } elseif (in_array($module->code, ['ot_bonus_points'])){//without currency exchange
            return [
                (isset($module->output[$index]['title']) ? $module->output[$index]['title'] : $module->title),
                Formatter::price($module->output[$index]['value_exc_vat'], 0, 1, $cart->currency, $currency_value).
                Html::hiddenInput("update_totals[" . $module->code . "][ex]", $params['ex'], ['class' => 'form-control', 'data-control'=>"{$module->code}", 'data-marker'=>"ex"]),
                Formatter::price($module->output[$index]['value_inc_tax'], 0, 1, $cart->currency, $currency_value).
                Html::hiddenInput("update_totals[" . $module->code . "][in]", $params['in'], ['class' => 'form-control', 'data-control'=>"{$module->code}", 'data-marker'=>"in"]),
                '<div class="totals edit-pt"><i class="icon-pencil"></i></div> ' . ($module->code != 'ot_shipping'? '<div class="totals del-pt" onclick="removeModule(\'' . $module->code . '\')"></div>': '<div></div>'),
            ];
        } else {
            $coupon = '';
            $showEdit = true;
            $showDelete = true;
            if ($module->code != 'ot_shipping') {
                $showDelete = false;
            }
            if ($module->code == 'ot_coupon') {
                $showEdit = false;
                $showDelete = false;
                $ex = [];
                preg_match("/\((.*)\):$/", $module->output[$index]['title'], $ex);
                if (is_array($ex) && isset($ex[1]) && !empty($ex[1])) {
                    $coupon = $ex[1];
                    $showDelete = true;
                }
            }
            
            return [
                (isset($module->output[$index]['title']) ? $module->output[$index]['title'] : $module->title),
                Html::hiddenInput("update_totals[" . $module->code . "][ex]", $params['ex'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $cart->currency), ['class' => 'form-control use-recalculation', 'data-control'=>"{$module->code}", 'data-marker'=>"ex"]).
                '<span>'. (($module->credit_class??null) ? '-' : '')
                        .Formatter::price($module->output[$index]['value_exc_vat'], 0, 1, $cart->currency, $currency_value).'</span>',
                Html::hiddenInput("update_totals[" . $module->code . "][in]", $params['in'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $cart->currency), ['class' => 'form-control use-recalculation', 'data-control'=>"{$module->code}", 'data-marker'=>"in"] ).
                '<span>'.(($module->credit_class??null) ? '-' : '')
                        .Formatter::price($module->output[$index]['value_inc_tax'], 0, 1, $cart->currency, $currency_value).'</span>',
                ($showEdit?'<div class="totals edit-pt"><i class="icon-pencil"></i></div> ':'') . ($showDelete? '<div class="totals del-pt" onclick="removeModule(\'' . $module->code . '\', \''.$coupon.'\')"></div>': '<div class="totals"></div>'),
            ];
        }
    }
    
}
