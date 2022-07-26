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


namespace common\components\EventDispatcher\Provider;


use common\components\EventDispatcher\ListenerProviderInterface;

class ProvidersAggregate implements ListenerProviderInterface
{
    /**
     * @var ListenerProviderInterface[]
     */
    private $providers;

    public function getListenersForEvent($event)
    {
        foreach ($this->providers as $provider) {
            yield from $provider->getListenersForEvent($event);
        }
    }

    public function attach(ListenerProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
