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

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class InvoiceNote extends Widget
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
    if ($this->params["order"]->order_id){
        $order_id = $this->params["order"]->parent_id ? $this->params["order"]->parent_id : $this->params["order"]->order_id;
        $comment = \common\models\OrdersComments::find()->where(['orders_id' => $order_id, 'for_invoice' => 1])->one();
        if ($comment && !empty($comment->comments)){
            return $comment->comments;
        }
    }
  }
}