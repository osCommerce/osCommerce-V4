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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class WordPressPost extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
      if (!\frontend\design\Info::hasBlog()){
          return '';
      }
    global $Blog;
    $languages_id = \Yii::$app->settings->get('languages_id');
    
    if (!is_object($Blog)) return;
    
    if (!isset($_GET['products_id']) || empty($_GET['products_id'])) return;
    
    //check wp installed
    $check1 = tep_db_query("show tables like 'wp_posts'");
    $check2 = tep_db_query("show tables like 'products_to_posts'");
    
    if (!tep_db_num_rows($check1) || !tep_db_num_rows($check2)) return;
            
    $query = tep_db_query("select * from " . TABLE_PRODUCTS_TO_POSTS . " p2p inner join wp_posts p on p.ID = p2p.post_id and p.post_parent = 0 where products_id = '" . (int)$_GET['products_id'] . "'");
    if (!tep_db_num_rows($query)) return;
    
    $posts = [];
    
    while($post = tep_db_fetch_array($query)){
		$image = tep_db_fetch_array(tep_db_query("select p.guid from wp_postmeta pm, wp_posts p where pm.post_id = {$post['ID']} and pm.meta_key='_thumbnail_id' and pm.meta_value = p.ID"));
		
		if ($image){
			$post['image'] = $image['guid'];
		}
		
		$category = tep_db_fetch_array(tep_db_query("select t.name from wp_terms t inner join wp_term_taxonomy tx on tx.term_taxonomy_id = t.term_id and tx.taxonomy = 'category', wp_term_relationships tr where tr.object_id = {$post['ID']} and tr.term_taxonomy_id = t.term_id"));
		
		if ($category){
			$post['category'] = $category['name'];
		}
        $posts[] = $post;
    }  
    //echo '<pre>';
	//print_r($posts);
	//echo '<pre>';
    return IncludeTpl::widget([
      'file' => 'boxes/wordpress-post.tpl',
      'params' => [
        'languages_id' => $languages_id,
        'settings' => $this->settings,
        'posts' => $posts,
      ],
    ]);
  }
}