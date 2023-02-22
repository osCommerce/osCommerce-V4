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

 class osC_CategoryTree {
   var $root_category_id = 0,
       $max_level = 0,
       $data = array(),
       $root_start_string = '',
       $root_end_string = '',
       $parent_start_string = '<span class="parrent_cat">',
       $parent_end_string = '</span>',
       $parent_group_start_string = '<ul>',
       $parent_group_end_string = '</ul>',
       $child_start_string = '<li>',
       $child_end_string = '</li>',
       $spacer_string = '',
       $spacer_multiplier = 1;
     private $show_products = false;
     protected $categories_products = [];

     function __construct($load_from_database = true) {
       global $languages_id, $current_category_id;

       $categories_join = '';
       if ( \common\classes\platform::activeId() ) {
           $categories_join .=
               " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
       }

       $categories_query = tep_db_query(
             "select c.categories_id, cd.categories_name, c.parent_id ".
             "from " . TABLE_CATEGORIES . " c {$categories_join}, " . TABLE_CATEGORIES_DESCRIPTION . " cd ".
             "where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and cd.affiliate_id=0 ".
             " and c.categories_status=1 ".
             "order by c.parent_id, c.sort_order, cd.categories_name"
         );
         
         $this->data = array();

         while ($categories = tep_db_fetch_array($categories_query)) {
			// Ultimate SEO URLs compatibility - Chemo
            # initialize array container for parent_id 
			$p = array();
			\common\helpers\Categories::get_parent_categories($p, $categories['parent_id']);
			# For some reason it seems to return in reverse order so reverse the array 
			$p = array_reverse($p);
			# Implode the array to get the parent category path
			$cID = (implode('_', $p) ? implode('_', $p) . '_' . $categories['parent_id'] :
			$categories['parent_id']);
            # initialize array container for category_id 
			$c = array();
			\common\helpers\Categories::get_parent_categories($c, $categories['categories_id']);
			# For some reason it seems to return in reverse order so reverse the array 
			$c = array_reverse($c);
			# Implode the array to get the full category path
			$id = (implode('_', $c) ? implode('_', $c) . '_' . $categories['categories_id'] :
			$categories['categories_id']);

            if (\common\helpers\Categories::count_products_in_category($categories['categories_id'])>0) {
                $this->data[$cID][$id] = array('name' => $categories['categories_name'], 'count' => 0, 'c_id_s' => $categories['categories_id']);
            }
          
         } // eof While loop
        //echo '<pre>'; print_r($this->data);
    } //eof Function

    function buildBranch($parent_id, $level = 0, $cid = 0, $cid2 = 0) {
      $result = $this->parent_group_start_string;
      if (isset($this->data[$parent_id])) {
         //echo'<pre>';print_r($this->data[$parent_id]);
        foreach ($this->data[$parent_id] as $category_id => $category) {
          if ($cid != $category_id && $level == 0) continue;
          if ($cid != $cid2 && $cid2 != $category['c_id_s'] && $level == 1) continue;
          $category_link = $category_id;
          $result .= $this->child_start_string;
          if (isset($this->data[$category_id])) $result .= $this->parent_start_string;

          if ($level == 0) $result .= $this->root_start_string;

          if (is_array($_GET['c'])) {
            $categories_array = $_GET['c'];
          } else {
            $categories_array = array();
          }
          if (in_array($category['c_id_s'], $categories_array)) {
            unset($categories_array[array_search($category['c_id_s'], $categories_array)]);
            $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . '<a href="' . tep_href_link('catalog', 'cPath=' . $_GET['cPath'] . (count($categories_array) > 0 ? '&c[]=' . implode('&c[]=', $categories_array) : '')) . '" class="c_heck selected_check"></a>';
          } else {
            $categories_array[] = $category['c_id_s'];
            $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . '<a href="' . tep_href_link('catalog', 'cPath=' . $_GET['cPath'] . (count($categories_array) > 0 ? '&c[]=' . implode('&c[]=', $categories_array) : '')) . '" class="c_heck"></a>';
          }

          $result .= $category['name'];

          if ($level == 0) $result .= $this->root_end_string;

          if (isset($this->data[$category_id])) $result .= $this->parent_end_string;

          $result .= $this->child_end_string;

          if (isset($this->data[$category_id]) && (($this->max_level == '0') || ($this->max_level > $level)))
            $result .= $this->buildBranch($category_id, $level+1, $cid, $cid2);
        }
      }

      $result .= $this->parent_group_end_string;
      return $result;
    }
    
    function buildBranch2($parent_id, $level = 0) {
        
        if ($level >= 2) {
            return '';
        }
     $result = $this->parent_group_start_string;

     if (isset($this->data[$parent_id])) {
       foreach ($this->data[$parent_id] as $category_id => $category) {
         $category_link = $category_id;
         $result .= $this->child_start_string;
         if (isset($this->data[$category_id]) || isset($this->categories_products[$category['c_id_s']])) {
             $result .= $this->parent_start_string;
         }

         if ($level == 0) $result .= $this->root_start_string;
		 
         $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . 
		 '<a href="' . tep_href_link('catalog', 'cPath=' . $category_link) . '">';
         $result .= $category['name'];
         $result .= '</a>';

         if ($level == 0) $result .= $this->root_end_string;

         if (isset($this->data[$category_id]) || isset($this->categories_products[$category['c_id_s']])) {
             $result .= $this->parent_end_string;
         }

         $result .= $this->child_end_string;

         if ( isset($this->categories_products[$category['c_id_s']]) ){
             $result .= $this->buildProductsBranch($category['c_id_s'], $level+1);
         }
         if (isset($this->data[$category_id]) && (($this->max_level == '0') ||
		 ($this->max_level > $level+1))) $result .= $this->buildBranch2($category_id, $level+1);
       }
     }

     $result .= $this->parent_group_end_string;
     return $result;
   }

    function buildTree() {
      global $current_category_id;
      if ( $this->show_products ){
          $this->loadProducts();
      }
      return $this->buildBranch2($this->root_category_id);
    }

     public function withProducts(bool $status)
     {
         $this->show_products = $status;
     }

     protected function loadProducts()
     {

         $q = new \common\components\ProductsQuery([
             'orderBy' => ['products_date_added'=>SORT_DESC],
         ]);
         $ac = $q->buildQuery()->getQuery()
            ->join('inner join', 'products_to_categories p2c_assign', 'p2c_assign.products_id=p.products_id')
            ->addSelect('p2c_assign.categories_id');
         $ac->select(['p.products_id', 'p2c_assign.categories_id']);

         $assign_to = $ac->createCommand()->queryAll();
         foreach ($assign_to as $assign){
             if (!isset($this->categories_products[$assign['categories_id']])){
                 $this->categories_products[$assign['categories_id']] = [];
             }
             $this->categories_products[$assign['categories_id']][(int)$assign['products_id']] = (int)$assign['products_id'];
         }
     }

     protected function buildProductsBranch($category_id, $level)
     {

         $result = '';

         if (isset($this->categories_products[$category_id]) && count($this->categories_products[$category_id])>0) {
             $result .= $this->parent_group_start_string;
             $product_names = [];
             foreach ($this->categories_products[$category_id] as $pid){
                 $product_names[$pid] = \common\helpers\Product::get_products_name($pid, '', \common\classes\platform::activeId());
             }

             foreach ($product_names as $products_id=>$products_name) {
                 $result .= $this->child_start_string;

                 $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * $level) .
                     '<a href="' . tep_href_link('catalog/product', 'products_id=' . $products_id) . '">';
                 $result .= $products_name;
                 $result .= '</a>';

                 $result .= $this->child_end_string;
             }
             $result .= $this->parent_group_end_string;
         }

         return $result;
     }

 }
?>