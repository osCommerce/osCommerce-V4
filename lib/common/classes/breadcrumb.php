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

  class breadcrumb {
    var $_trail;

    function __construct() {
      $this->reset();
    }

    function reset() {
      $this->_trail = array();
    }

    function add($title, $link = '') {
      $this->_trail[] = array('title' => $title, 'link' => $link);
    }

    function trail() {
      $trail_string = array();
      for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
        if (isset($this->_trail[$i]['link']) && tep_not_null($this->_trail[$i]['link']) && $i < $n - 1) {
          $trail_string[] = array(
            'name' =>  \common\helpers\Html::fixHtmlTags($this->_trail[$i]['title']),
            'link' => preg_match('/^(http|\/)/',$this->_trail[$i]['link'])?$this->_trail[$i]['link']:tep_href_link($this->_trail[$i]['link'])
          );
        } else {
          $trail_string[] = array(
            'name' =>  \common\helpers\Html::fixHtmlTags($this->_trail[$i]['title']),
            //'link' => '#'
              //'link' => preg_match('/^(http|\/)/',$this->_trail[$i]['link'])?$this->_trail[$i]['link']:tep_href_link($this->_trail[$i]['link'])
          );
        }
      }

      return $trail_string;
    }

    function seo_trail() {
      $trail_string = $this->_trail[sizeof($this->_trail)-2]['title'];
      return $trail_string;
    }
    
    function size() {
	return sizeof($this->_trail);
    }
  }
?>
