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

class Provider implements ListenerProviderInterface
{
    private $listeners = [];

    public function getListenersForEvent($event)
    {
        $className = \get_class($event);
        if (isset($this->listeners[$className])) {
            yield from $this->listeners[$className];
        }

        foreach (class_parents($event) as $parent) {
            if (isset($this->listeners[$parent])) {
                yield from $this->listeners[$parent];
            }
        }

        foreach (class_implements($event) as $interface) {
            if (isset($this->listeners[$interface])) {
                yield from $this->listeners[$interface];
            }
        }
    }

    public function attach(callable $listener)
    {
        $this->listeners[$this->getParameterType($listener)][] = $listener;
    }

    public function detach(string $interface)
    {
        unset($this->listeners[$interface]);
    }

    private function getParameterType(callable $callable): string
    {
        try {
            switch (true) {
                case $this->isClassCallable($callable):
                    $reflect = new \ReflectionClass($callable[0]);
                    $params = $reflect->getMethod($callable[1])->getParameters();
                    break;
                case $this->isFunctionCallable($callable):
                case $this->isClosureCallable($callable):
                    $reflect = new \ReflectionFunction($callable);
                    $params = $reflect->getParameters();
                    break;
                case $this->isObjectCallable($callable):
                    $reflect = new \ReflectionObject($callable[0]);
                    $params = $reflect->getMethod($callable[1])->getParameters();
                    break;
                case $this->isInvokable($callable):
                    $params = (new \ReflectionMethod($callable, '__invoke'))->getParameters();
                    break;
                default:
                    throw new \InvalidArgumentException('Not a recognized type of callable');
            }
            $reflectionType = $params[0]->getType();
            if ($reflectionType === null) {
                throw new \InvalidArgumentException('Listeners must be declare an object type they can accept.');
            }
            if (method_exists($reflectionType, 'getName')) {
                $type = $reflectionType->getName();
            } else {
                $type = (string)$reflectionType;
            }

        } catch (\ReflectionException $e) {
            throw new \RuntimeException('Type error registering listener.', 0, $e);
        }
        return $type;
    }

    private function isFunctionCallable(callable $callable): bool
    {
        // function_exists() not suitable because many functions includes later
        return is_string($callable);
    }

    private function isClosureCallable(callable $callable): bool
    {
        return $callable instanceof \Closure;
    }

    private function isInvokable(callable $callable): bool
    {
        return is_object($callable);
    }

    private function isObjectCallable(callable $callable): bool
    {
        return is_array($callable) && is_object($callable[0]);
    }

    /**
     * For StaticMethods
     * @param $callable
     * @return bool
     */
    private function isClassCallable($callable): bool
    {
        return is_array($callable) && is_string($callable[0]) && class_exists($callable[0]);
    }
}
