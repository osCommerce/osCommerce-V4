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

namespace frontend\design;

#[\AllowDynamicProperties]
  class splitPageResults {
    var $sql_query, $number_of_rows, $current_page_number, $number_of_pages, $page_name;
    public $number_of_rows_per_page = 0;
    public $max_display_search_results = 0;

    protected $seoRelLink = false;

/* class constructor */
    function __construct($query, $max_rows, $count_key = '*', $page_holder = 'page', $skipCount = -1) {
      $this->sql_query = $query;
      $this->page_name = $page_holder;

      if (isset($_GET[$page_holder])) {
        $page = $_GET[$page_holder];
      } elseif (isset($_POST[$page_holder])) {
        $page = $_POST[$page_holder];
      } else {
        $page = '';
      }

      if (empty($page) || !is_numeric($page)) $page = 1;
      $this->current_page_number = $page;

      $this->number_of_rows_per_page = max(1, (int)$max_rows);

      if (is_string($this->sql_query)) {

        $pos_to = strlen($this->sql_query);
        $pos_from = strpos($this->sql_query, ' from', 0);

        $pos_group_by = strpos($this->sql_query, ' group by', $pos_from);
        if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

        $pos_having = strpos($this->sql_query, ' having', $pos_from);
        if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

        $pos_order_by = strpos($this->sql_query, ' order by', $pos_from);
        if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

        if (strpos($this->sql_query, 'distinct') || strpos($this->sql_query, 'group by')) {
          $count_string = ($count_key == '*' ? tep_db_input($count_key) : 'distinct ' . tep_db_input($count_key));
        } else {
          $count_string = tep_db_input($count_key);
        }

        $count_query = tep_db_query("select count(" . $count_string . ") as total " . substr($this->sql_query, $pos_from, ($pos_to - $pos_from)));
        $count = tep_db_fetch_array($count_query);

        $this->number_of_rows = $count['total'];

      } elseif (method_exists($this->sql_query, 'count')) {

        if ($skipCount>=0) {
          $this->number_of_rows = $skipCount;
        } else {
          $this->number_of_rows = $this->sql_query->count();
        }
        if(!isset($this->current_page_number)) {
          $this->current_page_number = 1;
        }

      } else {
//debug something wrong
        //echo " ??? ". get_class($this->sql_query);
        \Yii::warning('wrong class passed to split page result:' . get_class($this->sql_query), 'splitPageResults');
      }
      
      $this->number_of_pages = ceil((int)$this->number_of_rows / $this->number_of_rows_per_page);
      $this->number_of_pages_fake = ceil((int)$this->number_of_rows / ($this->max_display_search_results>0?$this->max_display_search_results:1));

      if ($this->current_page_number > $this->number_of_pages ) {
        $this->current_page_number = $this->number_of_pages;
      }

      $offset = intval($this->number_of_rows_per_page * ($this->current_page_number - 1));


      if ($offset < 0) $offset =0;
      if (is_string($this->sql_query)) {
        $this->sql_query .= " limit " . max($offset, 0) . ", " . max(0,(int)$this->number_of_rows_per_page);
      } elseif (method_exists ($this->sql_query, 'offset')) {
// not used now
        $this->sql_query->offset(max($offset, 0))->limit(max(0,(int)$this->number_of_rows_per_page));
      } elseif (method_exists ($this->sql_query, 'buildQuery')) {
        $this->sql_query->offset(max($offset, 0))->limit(max(0,(int)$this->number_of_rows_per_page));
      }



    }

/* class functions */

    public static function make($query, $max_rows, $count_key = '*', $page_holder = 'page', $skipCount = false)
    {
        $obj = new self($query, $max_rows, $count_key, $page_holder, $skipCount);
        return $obj;
    }

    public function withSeoRelLink($state=true)
    {
        $this->seoRelLink = !!$state;
        return $this;
    }

// display split-page-number-links
    function display_links($max_page_links, $parameters = '', $action = '') {
      global $PHP_SELF, $request_type;

      $links = array();

      if ($action == '') $action = basename($PHP_SELF);

      if (tep_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

// previous button - not displayed on first page
      if ($this->current_page_number > 1) {
        $links['prev_page']['link'] = tep_href_link($action, $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type);
        if ( $this->seoRelLink ) {
           \Yii::$app->view->registerLinkTag(['rel' => 'prev', 'href' => $links['prev_page']['link']],'listing_prev');
        }
      }

// check if number_of_pages > $max_page_links
      $cur_window_num = intval($this->current_page_number / $max_page_links);
      if ($this->current_page_number % $max_page_links) $cur_window_num++;

      $max_window_num = intval($this->number_of_pages / $max_page_links);
      if ($this->number_of_pages % $max_page_links) $max_window_num++;

// previous window of pages
      if ($cur_window_num > 1){
        $links['prev_pages']['link'] = tep_href_link($action, $parameters . $this->page_name . '=' . (($cur_window_num - 1) * $max_page_links), $request_type);
        $links['prev_pages']['title'] = sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links);
      }

// page nn button
      for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
        if ($jump_to_page != $this->current_page_number) {
          $links['page_number'][] = array(
            'link' => tep_href_link($action, $parameters . $this->page_name . '=' . $jump_to_page, $request_type),
            'title' => $jump_to_page
          );
        } else {
          $links['page_number'][] = array(
            'title' => $jump_to_page
          );
        }
      }

// next window of pages
      if ($cur_window_num < $max_window_num){
        $links['next_pages']['link'] = tep_href_link($action, $parameters . $this->page_name . '=' . (($cur_window_num) * $max_page_links + 1), $request_type);
        $links['next_pages']['title'] = sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links);
      }

// next button
      if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) {
        $links['next_page']['link'] = tep_href_link($action, $parameters . 'page=' . ($this->current_page_number + 1), $request_type);
        if ( $this->seoRelLink ) {
            \Yii::$app->view->registerLinkTag(['rel' => 'next', 'href' => $links['next_page']['link']],'listing_next');
        }
      }

      return $links;
    }

// display number of total products found
    function display_count($text_output) {
      $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
      if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

      $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      if ($to_num == 0) {
        $from_num = 0;
      } else {
        $from_num++;
      }

      return sprintf($text_output, '<span class="from-num">' . $from_num . '</span>', '<span class="to-num">' . $to_num . '</span>', '<span class="number-of-rows">' . $this->number_of_rows . '</span>');
    }
    function display_count_mobile($text_output) {
      $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
      if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

      $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

      if ($to_num == 0) {
        $from_num = 0;
      } else {
        $from_num++;
      }

      return sprintf($text_output, $this->number_of_rows);
    }
  }
