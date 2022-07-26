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

namespace common\classes;

use frontend\design\Info;
use Yii;

class Blog
{
    public $wp_head;
    public $wp_sidebar_1;
    public $wp_sidebar_2;
    public $wp_sidebar_3;
    public $wp_content;
    public $wp_footer;

    public function init(){}

    public function wpLoad()
    {
        if (!\frontend\design\Info::hasBlog()){
            return '';
        }

        if (!function_exists('wp')) {
            $wp_did_header = true;

            define('WP_HOME', rtrim(Yii::$app->getUrlManager()->createAbsoluteUrl('index'),'/') . '/blog');
            define('WP_SITEURL', \yii\helpers\Url::home(true) . '_blog');

            // Load the WordPress library.
            require_once(DIR_FS_CATALOG . '/_blog/wp-load.php');

            // Set up the WordPress query.
            wp();
        }

    }
  
  public function getBlog($page = ''){
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    global $request_type;

    $default = tep_db_fetch_array(tep_db_query("SELECT platform_url FROM ".TABLE_PLATFORMS." WHERE is_default=1 "));

    $platform = Info::platformData();

    $this->wpLoad();
    define('WP_USE_THEMES', true);
    ob_start();
    require_once( ABSPATH . WPINC . '/template-loader.php' );
    $all_content = ob_get_contents();
    ob_end_clean();
    //$all_content = file_get_contents(($request_type == 'SSL' ? 'https' : 'http') . '://'.$platform['url'].'/blog/'.($page ? $page : $_GET['url_path']));



    $all_content = explode("<!--wphead-->", $all_content);

    if ($all_content[1]) {
      $wp_head = $all_content[0];

      $default_url = str_replace('/', '\/', str_replace('.', '\.', addslashes($default['platform_url'])));
      $wp_head = preg_replace("/" . $default_url . "/", $platform['url'], $wp_head);
      $wp_head = preg_replace("/\/_blog\//", "/blog/", $wp_head);
      $wp_head = preg_replace("/\/blog\/wp-content\//", "/_blog/wp-content/", $wp_head);
      $wp_head = preg_replace("/\/blog\/wp-admin\//", "/_blog/wp-admin/", $wp_head);
      $wp_head = preg_replace("/\/blog\/wp-includes\//", "/_blog/wp-includes/", $wp_head);
      $wp_head = preg_replace("/\/blog\/wp-json\//", "/_blog/wp-json/", $wp_head);
      $this->wp_head = preg_replace("/\/blog\/wp-comments-post.php/", "/_blog/wp-comments-post.php", $wp_head);

      $all_content[1] = preg_replace("/" . $default_url . "/", $platform['url'], $all_content[1]);
      $all_content[1] = preg_replace("/\/_blog\//", "/blog/", $all_content[1]);
      $all_content[1] = preg_replace("/\/blog\/wp-content\//", "/_blog/wp-content/", $all_content[1]);
      $all_content[1] = preg_replace("/\/blog\/wp-admin\//", "/_blog/wp-admin/", $all_content[1]);
      $all_content[1] = preg_replace("/\/blog\/wp-includes\//", "/_blog/wp-includes/", $all_content[1]);
      $all_content[1] = preg_replace("/\/blog\/wp-json\//", "/_blog/wp-json/", $all_content[1]);
      $all_content[1] = preg_replace("/\/blog\/wp-comments-post.php/", "/_blog/wp-comments-post.php", $all_content[1]);

      $all_content = explode("<!--wpsidebar-1-->", $all_content[1]);
      $this->wp_sidebar_1 = $all_content[0];
      $all_content = explode("<!--wpsidebar-2-->", $all_content[1]);
      $this->wp_sidebar_2 = $all_content[0];
      $all_content = explode("<!--wpsidebar-3-->", $all_content[1]);
      $this->wp_sidebar_3 = $all_content[0];
      $all_content = explode("<!--wpcontent-->", $all_content[1]);
      $this->wp_content = $all_content[0];
      $this->wp_footer = $all_content[1];
    } else {
      $this->wp_content = $all_content[0];
    }
  }

  public function head()
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    if (!$this->wp_head){
      $this->getBlog();
    }
    return $this->wp_head;
  }

  public function footer()
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    if (!$this->wp_footer){
      $this->getBlog();
    }
    return $this->wp_footer;
  }

  public function content($page = '')
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    if ($page){
      $this->getBlog($page);
    } elseif (!$this->wp_content){
      $this->getBlog();
    }
    return $this->wp_content;
  }

  public function sidebar_1()
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    if (!$this->wp_sidebar_1){
      $this->getBlog();
    }
    return $this->wp_sidebar_1;
  }

  public function sidebar_2()
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    if (!$this->wp_sidebar_2){
      $this->getBlog();
    }
    return $this->wp_sidebar_2;
  }

  public function sidebar_3()
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    if (!$this->wp_sidebar_3){
      $this->getBlog();
    }
    return $this->wp_sidebar_3;
  }

  public function editAdmin($admin_id)
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    $this->wpLoad();
    //$user_info = get_user_by('admin_id', $admin_id);
    $user_info_arr = get_users(array('meta_key' => 'admin_id', 'meta_value' => $admin_id));
    $user_info = $user_info_arr[0];

    $admin_data = tep_db_fetch_array(tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'"));

    if ($user_info && count($admin_data) > 0){// edit user

      $arr = array(
        'ID' => $user_info->ID,
        'nickname' => $admin_data['admin_firstname'] . ' ' . $admin_data['admin_lastname'],
        'display_name' => $admin_data['customers_firstname'] . ' ' . $admin_data['	customers_lastname'],
        'first_name' => $admin_data['admin_firstname'],
        'last_name' => $admin_data['admin_lastname'],
        'role' => 'administrator'
      );
      wp_update_user($arr);

    } elseif (!$user_info && count($admin_data) > 0) {// create user

      $username = preg_replace('/[^a-z0-9_-]/', '',  $admin_data['admin_email_address']);

      $user_id = wp_create_user( $username, '', '' );
      $n = 1;
      while (count($user_id->errors) > 0){
        $user_id = wp_create_user( $username . $n, '', '' );
        $n++;
      }
      add_user_meta( $user_id, 'admin_id', $admin_id);

      $arr = array(
        'ID' => $user_id,
        'nickname' => $admin_data['admin_firstname'] . ' ' . $admin_data['admin_lastname'],
        'display_name' => $admin_data['admin_firstname'] . ' ' . $admin_data['admin_lastname'],
        'first_name' => $admin_data['admin_firstname'],
        'last_name' => $admin_data['admin_lastname'],
        'role' => 'administrator'
      );
      wp_update_user($arr);


    } elseif ($user_info && count($admin_data) == 0) {// "remove user" change role to subscriber

      $arr = array(
        'ID' => $user_info->ID,
        'role' => 'subscriber'
      );
      wp_update_user($arr);

    }
  }

  public function editCustomer($customer_id)
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    $this->wpLoad();
    $user_info_arr = get_users(array('meta_key' => 'customer_id', 'meta_value' => $customer_id));
    $user_info = $user_info_arr[0];

    $admin_data = tep_db_fetch_array(tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'"));

    if ($user_info && count($admin_data) > 0){// edit user

      $arr = array(
        'ID' => $user_info->ID,
        'nickname' => $admin_data['customers_firstname'] . ' ' . $admin_data['	customers_lastname'],
        'display_name' => $admin_data['customers_firstname'] . ' ' . $admin_data['	customers_lastname'],
        'first_name' => $admin_data['customers_firstname'],
        'last_name' => $admin_data['customers_lastname']
      );
      wp_update_user($arr);

    } elseif (!$user_info && count($admin_data) > 0) {// create user

      $username = preg_replace('/[^a-z0-9_-]/', '',  $admin_data['admin_email_address']);

      $user_id = wp_create_user( $username, '', '' );
      $n = 1;
      while (count($user_id->errors) > 0){
        $user_id = wp_create_user( $username . $n, '', '' );
        $n++;
      }
      add_user_meta( $user_id, 'customer_id', $customer_id);

      $arr = array(
        'ID' => $user_id,
        'nickname' => $admin_data['customers_firstname'] . ' ' . $admin_data['customers_lastname'],
        'first_name' => $admin_data['customers_firstname'],
        'last_name' => $admin_data['customers_lastname']
      );
      wp_update_user($arr);


    } elseif ($user_info && count($admin_data) == 0) {// "remove user" change role to subscriber

      $arr = array(
        'ID' => $user_info->ID,
        'role' => 'subscriber'
      );
      wp_update_user($arr);

    }
  }
}