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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Rating extends Widget
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
        $params = Yii::$app->request->get();

        if (!$params['products_id']) {
            return '';
        }

        $rating = tep_db_fetch_array(tep_db_query("
                select count(*) as count, 
                AVG(reviews_rating) as average 
                from " . TABLE_REVIEWS . " 
                where products_id = '" . (int)$params['products_id'] . "' and status
            "));

        if ($rating['count']) {
            \frontend\design\JsonLd::addData(['Product' => [
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => round($rating['average']??0),
                    'ratingCount' => $rating['count'],
                ]
            ]], ['Product', 'aggregateRating']);
        }

        return IncludeTpl::widget(['file' => 'boxes/product/rating.tpl', 'params' => [
            'rating' => round($rating['average']??0),
            'count' => $rating['count'],
            'settings' => $this->settings
        ]]);
    }
}