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

namespace common\components;


use yii\base\BootstrapInterface;

class SessionFlowConsole implements BootstrapInterface
{
    public function preload(){
        \Yii::$container->setSingleton('products', '\common\components\ProductsContainer');
        \Yii::$container->setSingleton('currencies', '\common\classes\Currencies');
        //\Yii::$container->setSingleton('message_stack', '\common\classes\MessageStack');
    }

    public function bootstrap($app)
    {
        $this->preload();
    }

}