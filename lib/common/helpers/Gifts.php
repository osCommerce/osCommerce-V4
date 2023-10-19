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

namespace common\helpers;

use Yii;
use yii\helpers\ArrayHelper;

class Gifts {
    
    private static function getCart(){
        $manager = \common\services\OrderManager::loadManager();
        return $manager->getCart();
    }

    public static function allow_gift_wrap($products_id) {
      $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        if (!defined('MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS') || MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS != 'true')
            return false;
        $currency_id = \Yii::$app->settings->get('currency_id');
        $check_gift_wrap_r = tep_db_query(
                "SELECT COUNT(*) AS c " .
                "FROM " . TABLE_GIFT_WRAP_PRODUCTS . " " .
                "WHERE products_id='" . (int) $products_id . "' " .
                " and groups_id='" . (int) $customer_groups_id . "' ".
                (USE_MARKET_PRICES != 'True'? " and currencies_id in (0, '" . (int) $currency_id . "')" : " and currencies_id='" . (int) $currency_id . "' ")
        );
        if (tep_db_num_rows($check_gift_wrap_r) > 0) {
            $check_gift_wrap = tep_db_fetch_array($check_gift_wrap_r);
            return $check_gift_wrap['c'] > 0;
        }
        return false;
    }

    public static function get_gift_wrap_price($products_id) {
      $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        if (!defined('MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS') || MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS != 'true')
            return false;
        $currency_id = \Yii::$app->settings->get('currency_id');
        $gift_wrap_price = false;
        $check_gift_wrap_price_r = tep_db_query(
                "SELECT gift_wrap_price " .
                "FROM " . TABLE_GIFT_WRAP_PRODUCTS . " " .
                "WHERE products_id='" . (int) $products_id . "' " .
                " and groups_id='" . (int) $customer_groups_id . "' ".
                (USE_MARKET_PRICES != 'True'? " and currencies_id in (0, '" . (int) $currency_id . "') " : " and currencies_id='" . (int) $currency_id . "' ") .
                "LIMIT 1"
        );
        if (tep_db_num_rows($check_gift_wrap_price_r) > 0) {
            $_gift_wrap_price = tep_db_fetch_array($check_gift_wrap_price_r);
            $gift_wrap_price = $_gift_wrap_price['gift_wrap_price'];
        }
        return $gift_wrap_price;
    }

    public static function virtual_gift_card_process($virtual_gift_card_id, $from_email = STORE_OWNER_EMAIL_ADDRESS, $from_name = STORE_OWNER) {
        global $languages_id;
        $virtual_gift_card = tep_db_fetch_array(tep_db_query("select vgcb.virtual_gift_card_basket_id, vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, c.code as currency_code, vgcb.send_card_date, vgcb.gift_card_design, vgcb.virtual_gift_card_code, pd.platform_id from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_CURRENCIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.platform_id = '" . intval(\Yii::$app->get('platform')->config()->getPlatformToDescription()) . "' where vgcb.virtual_gift_card_basket_id = '" . (int) $virtual_gift_card_id . "' and p.products_id = vgcb.products_id and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and vgcb.currencies_id = c.currencies_id and vgcb.customers_id = '" . (int) \Yii::$app->user->getId() . "'"));
        if (!$virtual_gift_card)
            return;
        if (strlen($virtual_gift_card['virtual_gift_card_code'])){
            return $virtual_gift_card['virtual_gift_card_code'];
        }
        $currencies = \Yii::$container->get('currencies');
        // Generate virtual gift card coupon
        do {
            $virtual_gift_card_code = strtoupper(\common\helpers\Password::create_random_value(10));
            $check = tep_db_fetch_array(tep_db_query("select count(*) as coupon_exists from " . TABLE_COUPONS . " where coupon_code = '" . tep_db_input($virtual_gift_card_code) . "'"));
        } while ($check['coupon_exists']);

        $sql_data_array = array('coupon_code' => $virtual_gift_card_code,
            'coupon_amount' => $virtual_gift_card['products_price'],
            'coupon_currency' => $virtual_gift_card['currency_code'],
            'coupon_type' => 'G',
            'uses_per_coupon' => 1,
            'uses_per_user' => 1,
            'coupon_minimum_order' => 0,
            'restrict_to_products' => '',
            'restrict_to_categories' => '',
            'coupon_start_date' => 'now()',
            'coupon_active' => 'N',
            'coupon_expire_date' => date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y') + 3)),
            'date_created' => 'now()',
            'date_modified' => 'now()');
        $query = tep_db_perform(TABLE_COUPONS, $sql_data_array);
        $insert_id = tep_db_insert_id();

        $sql_data_array = array('coupon_name' => $currencies->display_gift_card_price($virtual_gift_card['products_price'], \common\helpers\Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']), $virtual_gift_card['currency_code']) . ' - ' . $virtual_gift_card['products_name']);
        $sql_data_array['coupon_id'] = $insert_id;
        $sql_data_array['language_id'] = $languages_id;
        tep_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_array);
        
        tep_db_query("update " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " set virtual_gift_card_code = '" . tep_db_input($virtual_gift_card_code) . "' where length(virtual_gift_card_code) = 0 and virtual_gift_card_basket_id = '" . (int) $virtual_gift_card_id . "'");

        /*if ($virtual_gift_card['send_card_date'] == '0000-00-00 00:00:00'){//now
            $virtual_gift_card['virtual_gift_card_code'] = $virtual_gift_card_code;
            self::prepareAndSend($virtual_gift_card, $from_email, $from_name = STORE_OWNER);
        }*/
        
        return $virtual_gift_card_code;
    }
    
    public static function activate($virtual_gift_card_id, \common\classes\extended\OrderAbstract $order){
        if ($virtual_gift_card_id){
            $virtual_gift_card = \common\models\VirtualGiftCardBasket::findOne(['virtual_gift_card_basket_id' => $virtual_gift_card_id]);
            if ($virtual_gift_card && $virtual_gift_card->customers_id == $order->customer['customer_id'] && strlen($virtual_gift_card->virtual_gift_card_code)){
                if (!$virtual_gift_card->activated && $virtual_gift_card->activate()){
                    if (!intval($virtual_gift_card->send_card_date)){
                        $product = ArrayHelper::toArray($virtual_gift_card->product);
                        $virtual_gift_card = ArrayHelper::toArray($virtual_gift_card);
                        $virtual_gift_card = array_merge($product, $virtual_gift_card);
                        $giftCardInfo = new \common\models\VirtualGiftCardInfo();
                        $giftCardInfo->setAttributes($virtual_gift_card);
                        $giftCardInfo->save();
                        self::prepareAndSend($virtual_gift_card, $order->customer['email_address'], $order->customer['firstname'] . ' ' . $order->customer['lastname']);
                    }
                }
            }
        }
    }
    
    public static function prepareAndSend($virtual_gift_card, $from_email = STORE_OWNER_EMAIL_ADDRESS, $from_name = STORE_OWNER){ //2do over helpers\Mail
        
        $currencies = \Yii::$container->get('currencies');
        // Instantiate a new mail object

        $contents = Yii::$app->runAction('catalog/gift', ['page_name' => 'gift_card']);
        $contents = str_replace(array("\r\n", "\n", "\r"), '', $contents);

        $search = array(
            "'##PRICE##'i",
            "'##PERSONAL_MESSAGE##'i",
            "'##SENDERS_NAME##'i",
            "'##CARD_CODE##'i");
        $replace = array(
            $currencies->display_gift_card_price($virtual_gift_card['products_price'], \common\helpers\Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']), $virtual_gift_card['currency_code']),
            nl2br($virtual_gift_card['virtual_gift_card_message']),
            $virtual_gift_card['virtual_gift_card_senders_name'],
            $virtual_gift_card['virtual_gift_card_code']);
        foreach ($replace as $key => $val) {
            $replace[$key] = str_replace('$', '/$/', $val);
        }
        $email_text = str_replace('/$/', '$', preg_replace($search, $replace, $contents));

        $email_params['CARD'] = $email_text;
        $email_params['SENDERS_NAME'] = $virtual_gift_card['virtual_gift_card_senders_name'];

        list($email_subject, $email_content) = \common\helpers\Mail::get_parsed_email_template('Gift Card', $email_params, -1, $virtual_gift_card['platform_id'], -1);


        // Send message


        \common\helpers\Mail::send(
            $virtual_gift_card['virtual_gift_card_recipients_name'],
            $virtual_gift_card['virtual_gift_card_recipients_email'],
            $email_subject,
            $email_content,
            $from_name,
            $from_email,
            [],
            '',
            '',
            ['add_br' => 'no']
        );

        return '';
    }

    /**
     * returns give_away query object limited with standard productsQuery and extra params.
     * @param int $products_id default 0
     * @param bool $only_active default false
     * @param bool $sorted default true
     * @param bool $only_buy_get default false
     * @return array ['cart_total' => $total, 'giveaway_query' => \yii\db\Query]
     */

    public static function getGiveAwaysSQL($products_id = 0, $only_active=false, $sorted = true, $only_buy_get = false){
        //global $languages_id, $platform_id;
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        if ($only_buy_get) {
            $total = 0;
        } else {
            $total = self::getCart()->show_total();
        }

/*
        $products2c_join = '';
        if ( $platform_id ) {
            $products2c_join .=
              " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . (int)$platform_id . "' ".
              " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
              " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . (int)$platform_id . "' ";
        } else if ( \common\classes\platform::activeId() ) {
            $products2c_join .=
              " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
              " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
              " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }
        $giveaway_query =
            "select distinct p.products_id, p.products_image, p.products_status, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, if(gap.shopping_cart_price <= '" . number_format($total,4,'.','') . "', 1, 0) as active, gap.shopping_cart_price as price, gap.products_qty as qty, gap.buy_qty as buy_qty, use_in_qty_discount, gap_id as gaw_id ".
            "from " . TABLE_GIVE_AWAY_PRODUCTS . " gap, " . TABLE_PRODUCTS . " p {$products2c_join} ".
            " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.platform_id = '".($platform_id?\Yii::$app->get('platform')->config($platform_id)->getPlatformToDescription():intval(\Yii::$app->get('platform')->config()->getPlatformToDescription()))."', " .
            TABLE_PRODUCTS_DESCRIPTION . " pd ".

            "where gap.products_id = p.products_id and (gap.buy_qty > 0 or gap.shopping_cart_price > 0) " .
            " and ( (gap.begin_date<=now() or gap.begin_date='0000-00-00') and (gap.end_date>=now() or gap.end_date='0000-00-00')) ".
            ((USE_MARKET_PRICES == 'True')?" and (shopping_cart_price<=0 or gap.currencies_id='" . (int)\Yii::$app->settings->get('currency_id') . "' )":"") .
            " and gap.groups_id='" . (int)$customer_groups_id . "'" .
            " and p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' ".
            ($products_id? " and p.products_id = '" . (int)$products_id . "'" : "") .
            ($only_active?" and gap.shopping_cart_price <= '" . number_format($total,4,'.','') . "'":"") .
            ($sorted?"order by price, active desc, gap.buy_qty, products_name":"")
        ;
 */

        $giveaway_query = \common\models\GiveAwayProducts::find()
            ->andWhere([
                  'and',
                  ['groups_id' => (int)$customer_groups_id],
                  ['<=', 'begin_date', new \yii\db\Expression('now()')], // date/datetime 0000/1970 or anything else date not null
                  ['or',
                    ['>=', 'end_date', new \yii\db\Expression('now()')], // date/datetime 0000/1970 or anything else date not null
                    ['<=', 'end_date', '1980-01-01'], // date/datetime 0000/1970 or anything else date not null
                  ]
                ]);
        if ((int)$products_id>0) {
          $giveaway_query->andWhere(['products_id' => (int)$products_id]);
        }
        if ($only_active) {
          $giveaway_query->andWhere(['<=', 'shopping_cart_price',  number_format($total,4,'.','')]);
        }
        if ($only_buy_get) {
          $giveaway_query->andWhere(['>', 'buy_qty', 0]);
        }

        // check other restriction - customer, platform etc
        $products = new \common\components\ProductsQuery(['get' => [],
          'currentCategory' => false,
          'outOfStock' => false,
          'skipInTop' => false,
          'skipInTopOnly' => false,
          'customAndWhere' => ['p.products_id' => $giveaway_query->distinct()->select('products_id')->asArray()->column()],
          'orderBy' => ['products_model' => SORT_ASC]
            ]);
        $pids = $products->buildQuery()->allIds();

        $giveaway_query->alias('gaw')->andWhere(['products_id' => $pids])
            //if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.
            //if(gaw.shopping_cart_price <= '" . number_format($total,4,'.','') . "', 1, 0) as active,
            ->select('gaw.products_id, shopping_cart_price as price, gaw.products_qty as qty, gaw.buy_qty as buy_qty, use_in_qty_discount, gap_id as gaw_id')
            ->addSelect(new \yii\db\Expression('if(gaw.shopping_cart_price <= ' . number_format($total,4,'.','') . ', 1, 0) as active'));
            ;

        if ($sorted) {
          $giveaway_query->addOrderBy('shopping_cart_price');
          $giveaway_query->addOrderBy(new \yii\db\Expression('shopping_cart_price<=' . number_format($total,4,'.','') . ' desc'));
          $giveaway_query->addOrderBy('buy_qty');
        }
        
        return ['cart_total' => $total, 'giveaway_query' => $giveaway_query ];
    }

/**
 * @deprecated since version 3.3.83 use getGiveAwaysArray instead
 * @param type $products_id
 * @return type
 */
    public static function getGiveAwaysQuery($products_id = 0){
    // backward compatibility
        $response = self::getGiveAwaysSQL($products_id);
        return ['cart_total' => $response['cart_total'], 'giveaway_query' => $response['giveaway_query'] ];
    }

    /**
     *
     * @param int $products_id
     * @return array ['cart_total' => 0.00, products => []
     */
    public static function getGiveAwaysArray($products_id = 0){
        $response = self::getGiveAwaysSQL($products_id);
        $products = $response['giveaway_query']->asArray()->all();

        $details = [];
        foreach (\frontend\design\Info::getListProductsDetails(array_unique(\yii\helpers\ArrayHelper::getColumn($products, 'products_id')), ['listing_type' => 'gwa' . $response['cart_total'], 0 => ['show_attributes' => 0, 'show_image' => 0], 'itemElements'=> ['attributes' => 1, 'image' => 1] ])
            as $detail) {
          $details[$detail['products_id']] = $detail;
        }

        foreach ($products as $key => $value) {
          if (!empty($details[$value['products_id']])) {
            $products[$key] += $details[$value['products_id']];
          }
        }

        return ['cart_total' => $response['cart_total'], 'products' => $products ];
    }

/**
 * giveaways with all products details
 * @param int $products_id
 * @return type
 */
    public static function getGiveAways($products_id = 0){
    //returns array of GAW products for design module at shopping cart page.
        $cart = self::getCart();
        $currencies = \Yii::$container->get('currencies');
        $products = [];
        $response = self::getGiveAwaysArray($products_id);
        $p = $response['products'];
        $total = $response['cart_total'];
        if (count($p) > 0) {

            foreach ($p as $d)
            {
              $price_b = '';
              if ($d['buy_qty'] > 0) {
                  $inCartQty = $cart->getQty($d['products_id']);
                  $price_b = sprintf(TEXT_QTY_BEFORE, $d['buy_qty'], $d['qty']);
                  if ($d['buy_qty'] > $inCartQty) {
                      $collect = $d['buy_qty'] - $inCartQty;
                      $giveaway_note = sprintf(TEXT_SPEND_MORE_ITEMS, $collect);
                      $d['active'] = 0;
                  } else {
                   /* if (self::get_max_quantity($d['products_id'])['qty'] > $d['qty']) {
                    //don't show active GAW if more same free product available
                      continue;
                    }*/
                    $giveaway_note = TEXT_ADD_GIVEAWAY;
                  }
              } elseif ($d['active'] == 1) {
                $giveaway_note = TEXT_ADD_GIVEAWAY;
                $price_b = sprintf(TEXT_PRICE_BEFORE, $d['qty'], $currencies->format($d['price']));
              } else {
                $collect = $d['price'] - $total;
                if ($collect < 0) {
                  $collect = 0;
                }
                $giveaway_note = sprintf(TEXT_SPEND_MORE, $currencies->format($collect));
                $price_b = sprintf(TEXT_PRICE_BEFORE, $d['qty'], $currencies->format($d['price']));
              }

              $products[] = array(
                'ga_idx' => $d['gaw_id'], //$row,
                'products_link' => $d['link'],
                'products_id' => $d['products_id'],
                'image' => $d['image'],
                'products_status' => $d['products_status'],
                'products_name' => $d['products_name'],
                'price_b' => $price_b,
                'giveaway_note' => $giveaway_note,
                'ga_form_action' => tep_href_link(FILENAME_SHOPPING_CART, 'product_id=' . $d['products_id'] . '&action=' . ($cart->in_giveaway($d['products_id'], $d['qty']) ? 'remove_giveaway' : 'add_giveaway')),
                'single_checkbox' => $cart->in_giveaway($d['products_id'], $d['qty'], $d['gaw_id']),
                'attributes' =>  isset($d['product_attributes_details']['attributes_array'])?$d['product_attributes_details']['attributes_array']:[],
                'active' => $d['active'],
              );

            }
          }
          return $products;
    }


    public static function get_max_quantity($prid, $gaw_id=false){
      $ret = false;

      if ((int)$prid>0) {
        $cart = self::getCart();
        if ($gaw_id) {
          $response = \common\helpers\Gifts::getGiveAwaysSQL($prid, true, false); // product, only active , no default sort order
          $giveaway_query = $response['giveaway_query']->andWhere(['gap_id'=> (int)$gaw_id])->asArray()->all();
          $total = $response['cart_total'];
        } else {
          $response = self::getGiveAwaysArray($prid);
          $giveaway_query = $response['products'];
          $total = $response['cart_total'];
        }

        if (is_array($giveaway_query) ) {
          $inCartQty = $cart->getQty($prid);
          foreach ($giveaway_query as $d) {
            if ($d['buy_qty'] > 0) {
              if ($d['buy_qty'] <= $inCartQty) {
                $ret = array('qty' => $d['qty'], 'gaw_id' => $d['gaw_id']);
              } else {
                break;
              }
            } else {
              $ret = array('qty' => $d['qty'], 'gaw_id' => $d['gaw_id']);
              break;
            }
          }
        }
      }
      return $ret;
    }
    
    /// not required while 1 giveaway only
    //also returns matched GAW_id
    public static function allowedGAW($prid, $get_qty){
        $cart = self::getCart();

        $response = self::getGiveAwaysArray($prid);
        $giveaway_query = $response['products'];
        $total = $response['cart_total'];

        if (is_array($giveaway_query) ) {

          $inCartQty = $cart->getQty($prid);//for buy & get option
          foreach ($giveaway_query as $d) {

            if ($d['buy_qty'] > 0) {
              if ($d['buy_qty'] <= $inCartQty && $d['qty'] == $get_qty) {
                return $d['gaw_id'] ;
              }
            } else {
              if ($d['price'] <= $total && $d['qty'] == $get_qty) {
                return $d['gaw_id'];
              }
            }
          }
        }
        return false;
    }

    public static function in_qty_discount($gaw_id) {
      static $cache = [];
      if (!isset($cache[$gaw_id])) {

        $response = self::getGiveAwaysSQL(0, true, false, true);
        $cache = $response['giveaway_query']->indexBy('gap_id')->select('use_in_qty_discount')->asArray()->column();
//          $cache[$d['gaw_id']] = $d['use_in_qty_discount'];
      }
      return $cache[$gaw_id];
    }

/**
 *
 * @param type $view
 * @param int $products_id
 */
    public static function prepareGWA(&$view, $products_id) {
      $gaws = \common\models\GiveAwayProducts::find()->andWhere(['products_id' => (int)$products_id])->asArray()->all();
      $i = 0;

      $view->gaw[0] = [];
      if (is_array($gaws)) {

        foreach ($gaws as $gaw ) {
          if ($i++==0) {
            $view->give_away = 1;
            $view->shopping_cart_price = $gaw['shopping_cart_price'];
            $view->buy_qty = ($gaw['buy_qty'] > 0 ? $gaw['buy_qty'] : '');
            $view->products_qty = ($gaw['products_qty'] > 0 ? $gaw['products_qty'] : '');
            $view->use_in_qty_discount = $gaw['use_in_qty_discount'];

          }
          if ($gaw['buy_qty'] == 0) {
              $view->gaw[$gaw['groups_id']][$gaw['currencies_id']] = $gaw;
              $view->gaw[$gaw['groups_id']]['by_total'] = 1;
          } else {
              $gaw['shopping_cart_price'] = max(0, $gaw['shopping_cart_price']);//mix? shouldn't work with marketing prices.
              $view->gaw[$gaw['groups_id']][] = $gaw;
          }
          if ($gaw['begin_date'] > '1980-01-01') {
              $view->gaw[$gaw['groups_id']]['begin_date'] = \common\helpers\Date::date_short($gaw['begin_date']);
          }
          if ($gaw['end_date'] > '1980-01-01') {
              $view->gaw[$gaw['groups_id']]['end_date'] = \common\helpers\Date::date_short($gaw['end_date']);
          }
        }
      }

    }


}


