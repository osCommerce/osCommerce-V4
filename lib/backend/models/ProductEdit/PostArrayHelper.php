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

namespace backend\models\ProductEdit;

use yii;

class PostArrayHelper
{

    /**
     * check & returns data from marketing tabs if any
     * @field ['db' => 'products_price_discount_pack_unit', db field name --- not required at all :(
     * 'postreindex' => 'discount_qty_pack_unit', POST - change array keys to
     * 'post' => 'discount_price_pack_unit', POST key. Required!
     * 'flag' => 'qty_discount_status_pack_unit', POST switcher flag (1 - on!!! someone use yes o_O )
     * 'f' => ['self', 'formatDiscountString']] - validator - callback
     */
    public static function getFromPostArrays($field, $curr_id, $group_id=0)
    {
        $data = $field['dbdef'] ?? null;
        if (isset($field['f']) && is_array($field['f']) && reset($field['f']) == 'self') { // php 8.2
            $field['f'][ array_key_first($field['f'])] = self::class;
        }

        if (USE_MARKET_PRICES == 'True' && \common\helpers\Extensions::isCustomerGroupsAllowed()) {
            if (isset($field['flag'])) {
                $tmp = Yii::$app->request->post($field['flag'], 0);
                if (is_array($tmp)) {
                    $check = $tmp[$curr_id][$group_id];
                } else {
                    $check = 0;
                }
            } else {
                $check = 1;
            }
            if ($check == 1) {
                $tmp = Yii::$app->request->post($field['post'], '');
                if (is_array($tmp)) {
                    $data = tep_db_prepare_input($tmp[$curr_id][$group_id]??null);
                    if (isset($field['postreindex'])) {
                        $tmp = Yii::$app->request->post($field['postreindex'], '');
                        $a = [];
                        if (is_array($tmp[$curr_id][$group_id])) {
                            foreach ($tmp[$curr_id][$group_id] as $k => $v) {
                                $a[tep_db_prepare_input($v)] = $data[$k];
                            }
                        }
                        $data = $a;
                    }
                    if (isset($field['f'])) {
                        $data = call_user_func_array($field['f'], [$data, $field['dbdef']]);
                    }
                } else { //plain, doesnot depend on group and currency
                    $data = $tmp;
                    if (isset($field['f'])) {
                        $data = call_user_func_array($field['f'], [$data, $field['dbdef']]);
                    }
                }
            }

        } elseif (USE_MARKET_PRICES == 'True') {
            if (isset($field['flag'])) {
                $tmp = Yii::$app->request->post($field['flag'], 0);
                if (is_array($tmp)) {
                    $check = $tmp[$curr_id];
                } else {
                    $check = 0;
                }
            } else {
                $check = 1;
            }
            if ($check == 1) {
                $tmp = Yii::$app->request->post($field['post'], '');
                if (is_array($tmp)) {
                    $data = tep_db_prepare_input($tmp[$curr_id]);
                    if (isset($field['postreindex'])) {
                        $tmp = Yii::$app->request->post($field['postreindex'], '');
                        $a = [];
                        if (is_array($tmp[$curr_id])) {
                            foreach ($tmp[$curr_id] as $k => $v) {
                                $a[tep_db_prepare_input($v)] = $data[$k];
                            }
                        }
                        $data = $a;
                    }
                    if (isset($field['f'])) {
                        $data = call_user_func_array($field['f'], [$data, $field['dbdef']]);
                    }
                } else { //plain, doesnot depend on group and currency
                    $data = $tmp;
                    if (isset($field['f'])) {
                        $data = call_user_func_array($field['f'], [$data, $field['dbdef']]);
                    }
                }
            }

        } elseif (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            if (isset($field['flag'])) {
                $tmp = Yii::$app->request->post($field['flag'], 0);
                if (is_array($tmp)) {
                    $check = $tmp[$group_id] ?? null;
                } else {
                    $check = 0;
                }
            } else {
                $check = 1;
            }
            if ($check == 1) {
                $tmp = Yii::$app->request->post($field['post'], '');
                if (is_array($tmp)) {
                    $data = tep_db_prepare_input($tmp[$group_id] ?? null);
                    if (isset($field['postreindex'])) {
                        $tmp = Yii::$app->request->post($field['postreindex'], '');
                        $a = [];
                        if (is_array($tmp[$group_id])) {
                            foreach ($tmp[$group_id] as $k => $v) {
                                $a[tep_db_prepare_input($v)] = $data[$k];
                            }
                        }
                        $data = $a;
                    }
                    if (isset($field['f'])) {
                        $data = call_user_func_array($field['f'], [$data, $field['dbdef']]);
                    }
                } else { //plain, doesnot depend on group and currency
                    $data = $tmp;
                    if (isset($field['f'])) {
                        $data = call_user_func_array($field['f'], [$data, $field['dbdef']]);
                    }
                }
            }

        } else {
            if (isset($field['flag'])) {
                $check = Yii::$app->request->post($field['flag'], 0);
            } else {
                $check = 1;
            }
            if ($check == 1) {
                $data = tep_db_prepare_input(Yii::$app->request->post($field['post'], $field['dbdef']));
                if (isset($field['postreindex'])) {
                    $tmp = Yii::$app->request->post($field['postreindex'], '');
                    $a = [];
                    if (is_array($tmp)) {
                        foreach ($tmp as $k => $v) {
                            $a[tep_db_prepare_input($v)] = $data[$k];
                        }
                    }
                    $data = $a;
                }
                if (isset($field['f'])) {
                    $data = call_user_func_array($field['f'], [$data, $field['dbdef']]);
                }
            }

        }
        return $data;
    }

    private static function defGroupPrice($data, $def)
    {
        if  (!preg_match('/\-?[\d\,\.]+/', trim($data)) || ($data < 0 && (int)$data!=-1 && (int)$data!=-2 )) {
            $data = $def;
        }
        return $data;
    }

    private static function formatDiscountString($data, $def)
    {
        if (is_array($data)) {
            ksort($data);
            $ret = '';
            foreach ($data as $key => $value) {
                $ret .= $key . ':' . $value . ';';
            }
        } else {
            $ret = $def;
        }
        return $ret;
    }

}