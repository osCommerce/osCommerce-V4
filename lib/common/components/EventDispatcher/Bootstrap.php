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
declare(strict_types=1);


namespace common\components\EventDispatcher;


use common\components\EventDispatcher\Provider\Provider;
use common\components\EventDispatcher\Provider\ProvidersAggregate;
use common\services\CategoriesService;
use common\services\ProductService;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $container = \Yii::$container;
        try {
            $container->setSingleton('eventProvider', static function () {
                return new Provider();
            });
            $container->setSingleton('eventDispatcher', static function () use ($container) {
                $providersAggregate = new ProvidersAggregate();
                $providersAggregate->attach($container->get('eventProvider'));
                return new EventDispatcher($providersAggregate);
            });
        } catch (\Exception $e) {
            // throw new \RuntimeException($e->getMessage(), 0, $e);
            \Yii::error($e->getMessage());
        }
    }
}
