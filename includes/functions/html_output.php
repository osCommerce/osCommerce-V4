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

  function tep_seo_urlencode($str) {
    $str = str_replace('&', urlencode(urlencode('&')), $str);
    $str = str_replace('+', urlencode(urlencode('+')), $str);
    $str = str_replace('#', urlencode(urlencode('#')), $str);
    return str_replace(' ', '+', $str);
  }

/**
 * The HTML href link wrapper function
 * @global type $request_type
 * @global type $session_started
 * @global type $SID
 * @global type $languages_id
 * @global type $languages_id
 * @global type $languages_id
 * @param string $page
 * @param type $parameters
 * @param type $connection
 * @param type $add_session_id
 * @param type $search_engine_safe
 * @return type
 */
  function tep_href_link($page = '', $parameters = '', $connection = '', $add_session_id = true, $search_engine_safe = true) {
    global $request_type, $session_started, $SID;
    
    if (empty($connection)) {
        $connection = (ENABLE_SSL == true ? 'SSL' : 'NONSSL');
    }

    if (!tep_not_null($page)) {
        $page = '/';
        //die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>');
    }

// {{ New TL Yii URLs
    if (class_exists('Yii') && is_object(Yii::$app)) {
      $url_params_array = array($page);
      if ( !empty($parameters) ) {
        $__url_params = false;
        parse_str($parameters, $__url_params);
        if ( is_array($__url_params) ) {
          $url_params_array = array_merge($url_params_array, $__url_params);
        }
      }
// products from other platforms
      if (in_array($page, ['catalog/gift-card', 'catalog/product']) && !empty($url_params_array['platform_id']) && PLATFORM_ID != $url_params_array['platform_id']) {
        // save current params
        $HostInfo = Yii::$app->urlManager->getHostInfo();
        $BaseUrl = Yii::$app->urlManager->getBaseUrl();

        $pc = new \common\classes\platform_config($url_params_array['platform_id']);
        $parsed = parse_url($pc->getCatalogBaseUrl());

        Yii::$app->urlManager->setHostInfo($parsed['scheme'] . '://' . $parsed['host'] . (!empty($parsed['port']) && ! in_array($parsed['port'], ['80', '443'])?':'.$parsed['port']:''));
        Yii::$app->urlManager->setBaseUrl(rtrim($parsed['path']));
        if ($parsed['scheme'] != 'https') {
          $url_params_array[tep_session_name()] = tep_session_id();
        }

        $crossUrl = Yii::$app->urlManager->createAbsoluteUrl($url_params_array, $parsed['scheme'], true);

// restore params
        Yii::$app->urlManager->setHostInfo($HostInfo);
        Yii::$app->urlManager->setBaseUrl($BaseUrl);
        return $crossUrl;
      }


      return Yii::$app->urlManager->createAbsoluteUrl($url_params_array, (($connection == 'SSL' && ENABLE_SSL == true) ? 'https' : 'http'));
    }
// }}

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br><br>');
    }

    if (tep_not_null($parameters)) {
      $link .= $page . '?' . \common\helpers\Output::output_string($parameters);
      $separator = '&';
    } else {
      $link .= $page;
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (tep_not_null($SID)) {
        $_sid = $SID;
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
          $_sid = tep_session_name() . '=' . tep_session_id();
        }
      }
    }

    if ( ((SEARCH_ENGINE_FRIENDLY_URLS == 'true') && (SEARCH_ENGINE_UNHIDE == 'True')) && ($search_engine_safe == true) ) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
// {{ SEO
      global $languages_id;
      if (strstr($page, FILENAME_DEFAULT) && strstr($parameters, 'cPath='))
      {
        $link = substr($link, 0, strpos($link, FILENAME_DEFAULT));

        $cPath_param = substr($parameters, strpos($parameters, 'cPath=') + 6);
        if (strpos($cPath_param, '&') !== false)
        {
          $cPath_param = substr($cPath_param, 0, strpos($cPath_param, '&'));
          $parameters = str_replace('cPath=' . $cPath_param . '&', '', $parameters);
        }
        else
        {
          $parameters = str_replace('cPath=' . $cPath_param, '', $parameters);
        }

        $cPath_array = \common\helpers\Categories::parse_category_path($cPath_param);
        $categories_id_param = $cPath_array[(sizeof($cPath_array)-1)];
        $category = tep_db_fetch_array(tep_db_query("select categories_seo_page_name from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id_param . "'"));
        if (tep_not_null($category['categories_seo_page_name']))
        {
          $url .= tep_seo_urlencode($category['categories_seo_page_name']);
        }
        else
        {
          if ($cPath_param) $parameters = 'cPath=' . $cPath_param . '&' . $parameters;
        }

        $parameters = preg_replace("/&+$/", '', $parameters);

        if (tep_not_null($parameters))
        {
          $link .= $url . '?' . \common\helpers\Output::output_string($parameters);
          $separator = '&';
        }
        else
        {
          $link .= $url;
          $separator = '?';
        }
      }
      elseif ( strstr($page, FILENAME_PRODUCT_INFO) && strstr($parameters, 'products_id=') &&
               !strstr($parameters, '{') && !strstr($parameters, '}') )
      {
        $link = substr($link, 0, strpos($link, FILENAME_PRODUCT_INFO));

        $products_id_param = substr($parameters, strpos($parameters, 'products_id=') + 12);
        if (strpos($products_id_param, '&') !== false)
        {
          $products_id_param = substr($products_id_param, 0, strpos($products_id_param, '&'));
          $parameters = str_replace('products_id=' . $products_id_param . '&', '', $parameters);
        }
        else
        {
          $parameters = str_replace('products_id=' . $products_id_param, '', $parameters);
        }

        if (strstr($parameters, 'cPath='))
        {
          $cPath_param = substr($parameters, strpos($parameters, 'cPath=') + 6);
          if (strpos($cPath_param, '&') !== false)
          {
            $cPath_param = substr($cPath_param, 0, strpos($cPath_param, '&'));
            $parameters = str_replace('cPath=' . $cPath_param . '&', '', $parameters);
          }
          else
          {
            $parameters = str_replace('cPath=' . $cPath_param, '', $parameters);
          }
        }

        $search = array("/manufacturers_id=\d*/", "/&+$/");
        $replace = array('', '');
        $parameters = preg_replace($search, $replace, $parameters);

        $product = tep_db_fetch_array(tep_db_query("select if(length(pd.products_seo_page_name) > 0, pd.products_seo_page_name, p.products_seo_page_name) as products_seo_page_name from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' where p.products_id = '" . (int)$products_id_param . "' order by length(if(length(pd.products_seo_page_name) > 0, pd.products_seo_page_name, p.products_seo_page_name)) desc limit 1"));

        if (tep_not_null($product['products_seo_page_name']))
        {
          $url .= tep_seo_urlencode($product['products_seo_page_name']);
        }
        else
        {
          if ($products_id_param) $parameters = 'products_id=' . $products_id_param . '&' . $parameters;
        }

        $parameters = preg_replace("/&+$/", '', $parameters);

        if (tep_not_null($parameters))
        {
          $link .= $url . '?' . \common\helpers\Output::output_string($parameters);
          $separator = '&';
        }
        else
        {
          $link .= $url;
          $separator = '?';
        }
      }
// {{{
      elseif (strstr($page, FILENAME_INFORMATION) && strstr($parameters, 'info_id='))
      {
        $link = substr($link, 0, strpos($link, FILENAME_INFORMATION));

        $info_id_param = substr($parameters, strpos($parameters, 'info_id=') + 8);
        if (strpos($info_id_param, '&') !== false)
        {
          $info_id_param = substr($info_id_param, 0, strpos($info_id_param, '&'));
          $parameters = str_replace('info_id=' . $info_id_param . '&', '', $parameters);
        }
        else
        {
          $parameters = str_replace('info_id=' . $info_id_param, '', $parameters);
        }

        global $languages_id;
        $information = tep_db_fetch_array(tep_db_query("select seo_page_name, information_id from " . TABLE_INFORMATION . " where information_id = '" . (int)$info_id_param . "' and languages_id = '" . (int)$languages_id . "'"));
        if (tep_not_null($information['seo_page_name']))
        {
          $url .= tep_seo_urlencode($information['seo_page_name']);
        }
        else
        {
          if ($info_id_param) $parameters = 'info_id=' . $info_id_param . '&' . $parameters;
        }

        $parameters = preg_replace("/&+$/", '', $parameters);

        if (tep_not_null($parameters))
        {
          $link .= $url . '?' . \common\helpers\Output::output_string($parameters);
          $separator = '&';
        }
        else
        {
          $link .= $url;
          $separator = '?';
        }
      }
// SEO Manufacturers
      elseif (strstr($page, FILENAME_DEFAULT) && strstr($parameters, 'manufacturers_id='))
      {
        $link = substr($link, 0, strpos($link, FILENAME_DEFAULT));

        $manufacturers_id_param = substr($parameters, strpos($parameters, 'manufacturers_id=') + 17);
        if (strpos($manufacturers_id_param, '&') !== false)
        {
          $manufacturers_id_param = substr($manufacturers_id_param, 0, strpos($manufacturers_id_param, '&'));
          $parameters = str_replace('manufacturers_id=' . $manufacturers_id_param . '&', '', $parameters);
        }
        else
        {
          $parameters = str_replace('manufacturers_id=' . $manufacturers_id_param, '', $parameters);
        }

        global $languages_id;
        $information = tep_db_fetch_array(tep_db_query("select manufacturers_seo_name, manufacturers_id from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id_param . "' and languages_id = '" . (int)$languages_id . "'"));
        if (tep_not_null($information['manufacturers_seo_name']))
        {
          $url .= tep_seo_urlencode($information['manufacturers_seo_name']);
        }
        else
        {
          if ($manufacturers_id_param) $parameters = 'manufacturers_id=' . $manufacturers_id_param . '&' . $parameters;
        }

        $parameters = preg_replace("/&+$/", '', $parameters);

        if (tep_not_null($parameters))
        {
          $link .= $url . '?' . \common\helpers\Output::output_string($parameters);
          $separator = '&';
        }
        else
        {
          $link .= $url;
          $separator = '?';
        }
      }
// eof SEO Manufacturers
// }}}
      else
      {
         if (tep_not_null($parameters))
         {
           $separator = '&';
         }
         else
         {
            $separator = '?';
         }
      }
// }}
    }

    if (isset($_sid) && ($session_started)) {
      $link .= $separator . \common\helpers\Output::output_string($_sid);
    }

    $link = preg_replace("/&(?!amp;)/", "&amp;", $link);
    $link = str_replace(' ', '%20', $link);

    $link = str_replace('/' . FILENAME_DEFAULT, '/', $link);

    return $link;
  }

////
// The HTML image wrapper function
  function tep_image($image_src, $alt = '', $width = '', $height = '', $parameters = '', $flag = true) {
    Global $language;
    if ( is_array($image_src) ) {
      $image_filename = $image_src['file'];
      $src = $image_src['src'];
    }else{
      $image_filename = DIR_FS_CATALOG . "/" .$image_src;
      $src = $image_src;
    }

    if ($flag)
    {
      $size = @GetImageSize($image_filename);
      if (!is_array($size)) {
          $size = [0,0];
      }

      if(!($size[0] <= $width && $size[1] <= $height)) {
        $newsize = \common\helpers\Image::getNewSize($image_filename, $width, $height);

        $width = (int)$newsize[0];
        $height = (int)$newsize[1];

      } else {
        $width = (int)$size[0];
        $height = (int)$size[1];
      }

      if (($size[0] == 0 || $size[1] == 0) && (IMAGE_REQUIRED == 'true'))
      {
          $src = DIR_WS_TEMPLATE_IMAGES . 'buttons/'.$language.'/na.gif';
          $newsize = \common\helpers\Image::getNewSize($image_filename, $width, $height);
          $width = (int)$newsize[0];
          $height = (int)$newsize[1];
      }
    }
    if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
      return false;
    }

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . \common\helpers\Output::output_string($src) . '" border="0" alt="' . \common\helpers\Output::output_string($alt) . '"';

    if (tep_not_null($alt)) {
      $image .= ' title=" ' . \common\helpers\Output::output_string($alt) . ' "';
    }

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($image_filename)) {
        if (empty($width) && tep_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = (int)($image_size[0] * $ratio);
        } elseif (tep_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = (int)($image_size[1] * $ratio);
        } elseif (empty($width) && empty($height)) {
          $width = (int)$image_size[0];
          $height = (int)$image_size[1];
        }
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }

    if (tep_not_null($width) && tep_not_null($height)) {
      $image .= ' width="' . \common\helpers\Output::output_string($width) . '" height="' . \common\helpers\Output::output_string($height) . '"';
    }

    if (tep_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= '>';

    return $image;
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_TEMPLATE_IMAGES . '' . $image, '', $width, $height, '', false);
  }

////
// Output a form
  function tep_draw_form($name, $action, $method = 'post', $parameters = '') {
    $form = '<form name="' . \common\helpers\Output::output_string($name) . '" action="' . \common\helpers\Output::output_string($action) . '" method="' . \common\helpers\Output::output_string($method) . '"';

    if (tep_not_null($parameters)) $form .= ' ' . $parameters;

    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . \common\helpers\Output::output_string($type) . '" name="' . \common\helpers\Output::output_string($name) . '"';

    if ( ($reinsert_value == true) && ( (isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])) ) ) {
      if (isset($_GET[$name]) && is_string($_GET[$name])) {
        $field .= ' value="' . \common\helpers\Output::output_string_protected(stripslashes($_GET[$name])) . '"';
      } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
        $field .= ' value="' . \common\helpers\Output::output_string_protected(stripslashes($_POST[$name])) . '"';
      }
    }elseif (tep_not_null($value)) {
      $field .= ' value="' . \common\helpers\Output::output_string_protected($value) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $parameters = 'maxlength="40"') {
    return tep_draw_input_field($name, $value, $parameters, 'password', false);
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . \common\helpers\Output::output_string($type) . '" name="' . \common\helpers\Output::output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . \common\helpers\Output::output_string($value) . '"';

    if ( ($checked == true) || (isset($_GET[$name]) && is_string($_GET[$name]) && (($_GET[$name] == 'on') || (stripslashes($_GET[$name]) == $value))) || (isset($_POST[$name]) && is_string($_POST[$name]) && (($_POST[$name] == 'on') || (stripslashes($_POST[$name]) == $value))) ) {
      $selection .= ' CHECKED';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= '>';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }

////
// Output a form textarea field
  function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . \common\helpers\Output::output_string($name) . '" cols="' . \common\helpers\Output::output_string($width) . '" rows="' . \common\helpers\Output::output_string($height) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( ($reinsert_value == true) && ( (isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])) ) ) {
      if (isset($_GET[$name]) && is_string($_GET[$name])) {
        $field .= \common\helpers\Output::output_string_protected(stripslashes($_GET[$name]));
      } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
        $field .= \common\helpers\Output::output_string_protected(stripslashes($_POST[$name]));
      }
    } elseif (tep_not_null($text)) {
      $field .= \common\helpers\Output::output_string_protected($text);
    }

    $field .= '</textarea>';

    return $field;
  }

////
// Output a form hidden field
  function tep_draw_hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . \common\helpers\Output::output_string($name) . '"';

    if (tep_not_null($value)) {
      $field .= ' value="' . \common\helpers\Output::output_string_protected($value) . '"';
    } elseif ( (isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])) ) {
      if ( (isset($_GET[$name]) && is_string($_GET[$name])) ) {
        $field .= ' value="' . \common\helpers\Output::output_string_protected(stripslashes($_GET[$name])) . '"';
      } elseif ( (isset($_POST[$name]) && is_string($_POST[$name])) ) {
        $field .= ' value="' . \common\helpers\Output::output_string_protected(stripslashes($_POST[$name])) . '"';
      }
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

////
// Hide form elements
  function tep_hide_session_id() {
    global $session_started, $SID;

    if (($session_started == true) && tep_not_null($SID)) {
      return tep_draw_hidden_field(tep_session_name(), tep_session_id());
    }
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . \common\helpers\Output::output_string($name) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && ( (isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])) ) ) {
      if (isset($_GET[$name]) && is_string($_GET[$name])) {
        $default = stripslashes($_GET[$name]);
      } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
        $default = stripslashes($_POST[$name]);
      }
    }

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . \common\helpers\Output::output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' SELECTED';
      }
      $field .= ' ' . ($values[$i]['params']??null) ;

      $field .= '>' . \common\helpers\Output::output_string($values[$i]['text'], array('&' => '&amp;', '"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return str_replace(array('&amp;nbsp;', '&amp;pound;'), array('&nbsp;', '&pound;'), $field);
  }

////
// Creates a pull-down list of countries
  function tep_get_country_list($name, $selected = '', $parameters = '') {
    $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
    $countries = \common\helpers\Country::get_countries();

    for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
      $countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
    }

    return tep_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
  }

  /*
  Output product as table in design
  Input: array contained products_id, products_name, products_image, products_description_short, products_tax_class_id
  */

  function tep_output_product_table_sell($product_array, $buy_now_buttom = false){
    $currencies = \Yii::$container->get('currencies');
    $special_price = \common\helpers\Product::get_products_special_price($product_array['products_id']);
    if ($special_price){
      $str = '<table border="0" cellpadding="0" cellspacing="0" class="productTable">
                <tr>
                  <td></td>
                  <td class="productImageCell"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_array["products_id"]) . '">' . tep_image(DIR_WS_IMAGES . $product_array['products_image'], $product_array['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>
                  </td>
                </tr>
                <tr>
                  <td valign="top" style="padding-top:5px;padding-right:3px;">' . tep_image(DIR_WS_TEMPLATE_IMAGES . 'contentbox/arrow.gif', $product_array['products_name'], 10, 10) . '</td>
                  <td valign="top" class="productNameCell"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_array['products_id']) . '">' . $product_array['products_name'] . '</a>
                  </td>
                </tr>';
      if ($product_array['products_description_short']){
        $str .= '<tr>
                    <td></td>
                    <td class="productNameCell">'.$product_array['products_description_short'] . '</td>
                 </tr>';
      }
      $str .= '<tr>
                  <td></td>
                  <td class="productPriceCell"><span class="productPriceOld">' . $currencies->display_price(\common\helpers\Product::get_products_price($product_array['products_id'], 1, $product_array['products_price']), \common\helpers\Tax::get_tax_rate($product_array['products_tax_class_id'])) . '</span><br><span class="productPriceSpecial">' . $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($product_array['products_tax_class_id'])) . '</span>
                  </td>
               </tr>
               ' . ($buy_now_buttom?'<tr><td></td><td align="center"><a href="' . tep_href_link(FILENAME_DEFAULT, \common\helpers\Output::get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $product_array['products_id'], 'NONSSL') . '">' . tep_template_image_button('button_buy_now.' . BUTTON_IMAGE_TYPE, TEXT_BUY . $product_array['products_name'] . TEXT_NOW, 'class="transpng"') .'</a></td></tr>':'') . '
               </table>';
    }else{
      $str = '<table border="0" cellpadding="0" cellspacing="0" class="productTable">
               <tr>
                <td></td>
                <td align="center" style="height:100%" valign="top" class="productImageCell"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_array["products_id"]) . '">' . tep_image(DIR_WS_IMAGES . $product_array['products_image'], $product_array['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>
                </td>
             </tr>
             <tr>
               <td valign="top" style="padding-top:5px;padding-right:3px;">' . tep_image(DIR_WS_TEMPLATE_IMAGES . 'contentbox/arrow.gif', $product_array['products_name'], 10, 10) . '</td>
              <td valign="top" class="productNameCell"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_array['products_id']) . '">' . $product_array['products_name'] . '</a>
              </td>
            </tr>';
      if ($product_array['products_description_short']){
        $str .= '<tr>
                   <td></td>
                   <td class="productNameCell">'.$product_array['products_description_short'] . '</td>
                 </tr>';
      }
      $str .= '<tr>
              <td></td>
              <td class="productPriceCell"><span class="productPriceCurrent">' . $currencies->display_price(\common\helpers\Product::get_products_price($product_array['products_id'], 1, $product_array['products_price']), \common\helpers\Tax::get_tax_rate($product_array['products_tax_class_id'])) . '</span>
              </td>
            </tr>
            ' . ($buy_now_buttom?'<tr><td></td><td align="center"><a href="' . tep_href_link(FILENAME_DEFAULT, \common\helpers\Output::get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $product_array['products_id'], 'NONSSL') . '">' . tep_template_image_button('button_buy_now.' . BUTTON_IMAGE_TYPE, TEXT_BUY . $product_array['products_name'] . TEXT_NOW, 'class="transpng"') .'</a></td></tr>':'') . '
            </table>';
    }
    return $str;
  }

  function tep_cut_text($text,$cut=50){
    $text = preg_replace('/\s{2,}/', ' ', strip_tags($text) );
    $text = trim($text);
    $orl = strlen($text);
    if ( $cut!==false ) { 
      $text = substr( $text, 0, $cut );
    }
    if ( $orl!=strlen($text) ) {
      $text = preg_replace( '/(\w+)$/', '',$text );
      $text .= ' ...';    
    }
    return $text;
  }

function tep_draw_barcode($filepath='', $text='0', $size='50', $orientation='horizontal', $code_type='code128', $print=true ) {
  $code_string = '';
  // Translate the $text into barcode the correct $code_type
  if ( in_array(strtolower($code_type), array("code128", "code128b")) ) {
    $chksum = 104;
    // Must not change order of array elements as the checksum depends on the array's key to validate final code
    $code_array = array(" "=>"212222","!"=>"222122","\""=>"222221","#"=>"121223","$"=>"121322","%"=>"131222","&"=>"122213","'"=>"122312","("=>"132212",")"=>"221213","*"=>"221312","+"=>"231212",","=>"112232","-"=>"122132","."=>"122231","/"=>"113222","0"=>"123122","1"=>"123221","2"=>"223211","3"=>"221132","4"=>"221231","5"=>"213212","6"=>"223112","7"=>"312131","8"=>"311222","9"=>"321122",":"=>"321221",";"=>"312212","<"=>"322112","="=>"322211",">"=>"212123","?"=>"212321","@"=>"232121","A"=>"111323","B"=>"131123","C"=>"131321","D"=>"112313","E"=>"132113","F"=>"132311","G"=>"211313","H"=>"231113","I"=>"231311","J"=>"112133","K"=>"112331","L"=>"132131","M"=>"113123","N"=>"113321","O"=>"133121","P"=>"313121","Q"=>"211331","R"=>"231131","S"=>"213113","T"=>"213311","U"=>"213131","V"=>"311123","W"=>"311321","X"=>"331121","Y"=>"312113","Z"=>"312311","["=>"332111","\\"=>"314111","]"=>"221411","^"=>"431111","_"=>"111224","\`"=>"111422","a"=>"121124","b"=>"121421","c"=>"141122","d"=>"141221","e"=>"112214","f"=>"112412","g"=>"122114","h"=>"122411","i"=>"142112","j"=>"142211","k"=>"241211","l"=>"221114","m"=>"413111","n"=>"241112","o"=>"134111","p"=>"111242","q"=>"121142","r"=>"121241","s"=>"114212","t"=>"124112","u"=>"124211","v"=>"411212","w"=>"421112","x"=>"421211","y"=>"212141","z"=>"214121","{"=>"412121","|"=>"111143","}"=>"111341","~"=>"131141","DEL"=>"114113","FNC 3"=>"114311","FNC 2"=>"411113","SHIFT"=>"411311","CODE C"=>"113141","FNC 4"=>"114131","CODE A"=>"311141","FNC 1"=>"411131","Start A"=>"211412","Start B"=>"211214","Start C"=>"211232","Stop"=>"2331112");
    $code_keys = array_keys($code_array);
    $code_values = array_flip($code_keys);
    for ( $X = 1; $X <= strlen($text); $X++ ) {
      $activeKey = substr( $text, ($X-1), 1);
      $code_string .= $code_array[$activeKey];
      $chksum=($chksum + ($code_values[$activeKey] * $X));
    }
    $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

    $code_string = "211214" . $code_string . "2331112";
  } elseif ( strtolower($code_type) == "code128a" ) {
    $chksum = 103;
    $text = strtoupper($text); // Code 128A doesn't support lower case
    // Must not change order of array elements as the checksum depends on the array's key to validate final code
    $code_array = array(" "=>"212222","!"=>"222122","\""=>"222221","#"=>"121223","$"=>"121322","%"=>"131222","&"=>"122213","'"=>"122312","("=>"132212",")"=>"221213","*"=>"221312","+"=>"231212",","=>"112232","-"=>"122132","."=>"122231","/"=>"113222","0"=>"123122","1"=>"123221","2"=>"223211","3"=>"221132","4"=>"221231","5"=>"213212","6"=>"223112","7"=>"312131","8"=>"311222","9"=>"321122",":"=>"321221",";"=>"312212","<"=>"322112","="=>"322211",">"=>"212123","?"=>"212321","@"=>"232121","A"=>"111323","B"=>"131123","C"=>"131321","D"=>"112313","E"=>"132113","F"=>"132311","G"=>"211313","H"=>"231113","I"=>"231311","J"=>"112133","K"=>"112331","L"=>"132131","M"=>"113123","N"=>"113321","O"=>"133121","P"=>"313121","Q"=>"211331","R"=>"231131","S"=>"213113","T"=>"213311","U"=>"213131","V"=>"311123","W"=>"311321","X"=>"331121","Y"=>"312113","Z"=>"312311","["=>"332111","\\"=>"314111","]"=>"221411","^"=>"431111","_"=>"111224","NUL"=>"111422","SOH"=>"121124","STX"=>"121421","ETX"=>"141122","EOT"=>"141221","ENQ"=>"112214","ACK"=>"112412","BEL"=>"122114","BS"=>"122411","HT"=>"142112","LF"=>"142211","VT"=>"241211","FF"=>"221114","CR"=>"413111","SO"=>"241112","SI"=>"134111","DLE"=>"111242","DC1"=>"121142","DC2"=>"121241","DC3"=>"114212","DC4"=>"124112","NAK"=>"124211","SYN"=>"411212","ETB"=>"421112","CAN"=>"421211","EM"=>"212141","SUB"=>"214121","ESC"=>"412121","FS"=>"111143","GS"=>"111341","RS"=>"131141","US"=>"114113","FNC 3"=>"114311","FNC 2"=>"411113","SHIFT"=>"411311","CODE C"=>"113141","CODE B"=>"114131","FNC 4"=>"311141","FNC 1"=>"411131","Start A"=>"211412","Start B"=>"211214","Start C"=>"211232","Stop"=>"2331112");
    $code_keys = array_keys($code_array);
    $code_values = array_flip($code_keys);
    for ( $X = 1; $X <= strlen($text); $X++ ) {
      $activeKey = substr( $text, ($X-1), 1);
      $code_string .= $code_array[$activeKey];
      $chksum=($chksum + ($code_values[$activeKey] * $X));
    }
    $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

    $code_string = "211412" . $code_string . "2331112";
  } elseif ( strtolower($code_type) == "code39" ) {
    $code_array = array("0"=>"111221211","1"=>"211211112","2"=>"112211112","3"=>"212211111","4"=>"111221112","5"=>"211221111","6"=>"112221111","7"=>"111211212","8"=>"211211211","9"=>"112211211","A"=>"211112112","B"=>"112112112","C"=>"212112111","D"=>"111122112","E"=>"211122111","F"=>"112122111","G"=>"111112212","H"=>"211112211","I"=>"112112211","J"=>"111122211","K"=>"211111122","L"=>"112111122","M"=>"212111121","N"=>"111121122","O"=>"211121121","P"=>"112121121","Q"=>"111111222","R"=>"211111221","S"=>"112111221","T"=>"111121221","U"=>"221111112","V"=>"122111112","W"=>"222111111","X"=>"121121112","Y"=>"221121111","Z"=>"122121111","-"=>"121111212","."=>"221111211"," "=>"122111211","$"=>"121212111","/"=>"121211121","+"=>"121112121","%"=>"111212121","*"=>"121121211");

    // Convert to uppercase
    $upper_text = strtoupper($text);

    for ( $X = 1; $X<=strlen($upper_text); $X++ ) {
      $code_string .= $code_array[substr( $upper_text, ($X-1), 1)] . "1";
    }

    $code_string = "1211212111" . $code_string . "121121211";
  } elseif ( strtolower($code_type) == "code25" ) {
    $code_array1 = array("1","2","3","4","5","6","7","8","9","0");
    $code_array2 = array("3-1-1-1-3","1-3-1-1-3","3-3-1-1-1","1-1-3-1-3","3-1-3-1-1","1-3-3-1-1","1-1-1-3-3","3-1-1-3-1","1-3-1-3-1","1-1-3-3-1");

    for ( $X = 1; $X <= strlen($text); $X++ ) {
      for ( $Y = 0; $Y < count($code_array1); $Y++ ) {
        if ( substr($text, ($X-1), 1) == $code_array1[$Y] )
          $temp[$X] = $code_array2[$Y];
      }
    }

    for ( $X=1; $X<=strlen($text); $X+=2 ) {
      if ( isset($temp[$X]) && isset($temp[($X + 1)]) ) {
        $temp1 = explode( "-", $temp[$X] );
        $temp2 = explode( "-", $temp[($X + 1)] );
        for ( $Y = 0; $Y < count($temp1); $Y++ )
          $code_string .= $temp1[$Y] . $temp2[$Y];
      }
    }

    $code_string = "1111" . $code_string . "311";
  } elseif ( strtolower($code_type) == "codabar" ) {
    $code_array1 = array("1","2","3","4","5","6","7","8","9","0","-","$",":","/",".","+","A","B","C","D");
    $code_array2 = array("1111221","1112112","2211111","1121121","2111121","1211112","1211211","1221111","2112111","1111122","1112211","1122111","2111212","2121112","2121211","1121212","1122121","1212112","1112122","1112221");

    // Convert to uppercase
    $upper_text = strtoupper($text);

    for ( $X = 1; $X<=strlen($upper_text); $X++ ) {
      for ( $Y = 0; $Y<count($code_array1); $Y++ ) {
        if ( substr($upper_text, ($X-1), 1) == $code_array1[$Y] )
          $code_string .= $code_array2[$Y] . "1";
      }
    }
    $code_string = "11221211" . $code_string . "1122121";
  }

  // Pad the edges of the barcode
  $code_length = 20;
  if ($print) {
    $text_height = 20;
  } else {
    $text_height = 0;
  }

  for ( $i=1; $i <= strlen($code_string); $i++ ) {
    $code_length = $code_length + (integer)(substr($code_string,($i-1),1));
  }

  if ( strtolower($orientation) == "horizontal" ) {
    $img_width = $code_length;
    $img_height = $size;
  } else {
    $img_width = $size;
    $img_height = $code_length;
  }

  $image = imagecreate($img_width, $img_height + $text_height);
  $black = imagecolorallocate ($image, 0, 0, 0);
  $white = imagecolorallocate ($image, 255, 255, 255);

  imagefill( $image, 0, 0, $white );
  if ( $print ) {
    imagestring($image, 5, 31, $img_height, $text, $black );
  }

  $location = 10;
  for ( $position = 1 ; $position <= strlen($code_string); $position++ ) {
    $cur_size = $location + ( substr($code_string, ($position-1), 1) );
    if ( strtolower($orientation) == "horizontal" )
      imagefilledrectangle( $image, $location, 0, $cur_size, $img_height, ($position % 2 == 0 ? $white : $black) );
    else
      imagefilledrectangle( $image, 0, $location, $img_width, $cur_size, ($position % 2 == 0 ? $white : $black) );
    $location = $cur_size;
  }

  // Draw barcode to the screen or save in a file
  if ( $filepath=="" ) {
    header ('Content-type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit();
  } else {
    imagepng($image,$filepath);
    imagedestroy($image);
  }
}
