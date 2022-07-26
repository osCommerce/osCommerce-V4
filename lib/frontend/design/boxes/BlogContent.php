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
use frontend\design\Info;

class BlogContent extends Widget
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

    if (Yii::$app->controller->id == 'blog') {
      return '<div class="blog-content">' . $Blog->content($this->settings[0]['page']) . '</div>';
    } else {
      $Blog->wpLoad();
      $args = array(
        'posts_per_page'   => 8,
        'post_status'      => 'publish',
      );
      $posts_array = get_posts($args);
      foreach ($posts_array as $post){
		$post_url = get_permalink($post->ID);
		  $image = get_the_post_thumbnail($post->ID);
		  echo '<div class = "post_item"><div class = "post_item_wrap">';
		  if ($image  ) {
	echo '<div class = "post_image"><span><span>';
	echo '<a href="'.$post_url.'">' .$image . '</a>';
	echo '</span></span></div>';
}
        echo '<div class = "post_title"><a href="'.$post_url.'">'. $post->post_title.'</a></div>';
		echo '<div class = "post_date">'.get_the_date( 'd/m/Y', $post->ID ).'</div>';
		
		 echo '<div class = "post_content_strip">';
		 $trimmed_content = wp_trim_words( $post->post_content,40, '');
		 $rmore = '<a class = "read_more" href="'. get_permalink($post->ID) .'">Find out More</a>';
        echo $trimmed_content . $rmore;
		echo '</div>';
		echo '</div></div>';
      }
    }
  }
}