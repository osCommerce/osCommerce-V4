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

/**
 * Class EventDispatcher
 * @see Personal Catalog Extension
 * use
 * // also provided invoke, static classes and objects
 * \Yii::$container->get('eventProvider')->attach(static function (OrderCreated $event) {
 *     sendConfirmationEmail($event->getOrder);
 * });
 *
 * \Yii::$container->get('eventDispatcher')->dispatch(new OrderCreated($this));
 */
class EventDispatcher
{

    /** @var ListenerProviderInterface */
    private $listenerProvider;

    public function __construct(ListenerProviderInterface $listenerProvider)
    {
        $this->listenerProvider = $listenerProvider;
    }

    public function dispatch($event)
    {
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }
            $listener($event);
        }
        return $event;
    }
}
