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

namespace backend\widgets;

use Yii;
use yii\base\Widget;
use common\helpers\Html;

class News extends Widget {
    
    public $url = '';
    
    public function run()
    {
        if (!$this->url) return '';

        $xml = simplexml_load_file($this->url);
        $news = [];
        foreach ($xml->channel->item as $item) {
            $img = '';
            if ($item->enclosure['url']) {
                $img = Html::img($item->enclosure['url'], ['alt' => $item->title]);
            }
            $news[] = [
                'title' => $item->title,
                'link' => $item->link,
                'image' => $img,
                'description' => str_replace('&nbsp;', ' ', strip_tags($item->description)),
                'pubDate' => $item->pubDate,
                'date' => date( \common\helpers\Date::DATE_FORMAT, strtotime($item->pubDate)),
                'dateTime' => date( \common\helpers\Date::DATE_TIME_FORMAT, strtotime($item->pubDate)),
            ];
        }

        $max = defined('RSS_FEED_MAX_ITEMS') ? RSS_FEED_MAX_ITEMS : 10;
        /*usort($news, function($a, $b){
            return (strtotime($a['pubDate']) > strtotime($b['pubDate'])) ? -1 : 1;
        });*/
        $news = array_splice($news, 0, $max);

        return $this->render('News.tpl', [
            'news' => $news,
        ]);
    }
}