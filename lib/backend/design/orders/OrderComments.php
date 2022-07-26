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

namespace backend\design\orders;


use Yii;
use yii\base\Widget;
use common\models\Admin;

class OrderComments extends Widget {

    public $manager;
    public $order;

    public function init(){
        parent::init();
    }

    public function run(){
        global $login_id;

        $admin = Admin::findOne(['admin_id' => $login_id]);
        $groups = \common\models\AccessLevels::find()->select(['access_levels_id', 'access_levels_name'])->with(['admins' => function(\yii\db\ActiveQuery $query){ return $query->orderBy('admin_firstname, admin_lastname'); }])->asArray()->all();

        $comments = \common\models\OrdersComments::find()->where(['orders_id' => $this->order->order_id, 'for_invoice' => 0])->orderBy('date_added desc')->with('admin')->all();
        if ($comments){
            foreach ($comments as $_key => $comment){
                if ((int)$comment->admin_id == (int)$login_id) {
                    continue;
                }
                $data = json_decode($comment->visible, true);
                if ($data){
                    if (key($data) == 'm'){
                        if ($data['m'] != $login_id){
                            unset($comments[$_key]);
                        }
                    } else if (key($data) == 'g'){
                        if ($admin){
                            $al = $admin->getAccesslevel()->one();
                            if ($al && $al->access_levels_id != $data['g']){
                                unset($comments[$_key]);
                            }
                        }
                    }
                }
            }
        }
        ksort($comments);

        return $this->render('order-comments', [
            'order' => $this->order,
            'manager' => $this->manager,
            'comments' => $comments,
            'groups' => $groups,
            ]);
    }
}
