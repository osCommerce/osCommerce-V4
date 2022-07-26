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
if (getenv("HTTP_HOST")){
    $bootstrap = ['log', 'common\components\SessionFlow'];
} else {
    $bootstrap = [];
}
try {
    $paths = [
        dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR,
        dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR,
        dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
    ];
    foreach (getBootstrapIterator($paths) as $class) {
        $bootstrap[] = $class;
    }
} catch (\Exception $e) {
    \Yii::error($e->getMessage());
}
return $bootstrap;
/**
 * @param string $className
 * @return bool
 * @throws ReflectionException
 */
function isYiiBootstrap(string $className)
{
    $cl = new \ReflectionClass($className);
    return in_array('yii\base\BootstrapInterface', $cl->getInterfaceNames(), true);
}

/**
 * @param array $paths
 * @return Generator
 * @throws ReflectionException
 */
function getBootstrapIterator(array $paths)
{
    foreach ($paths as $path) {
        foreach (getFilesIterator($path, '/Bootstrap\.php$/', 2) as $file) {
            $className = getClassFromPath($file->getPathName());
            if (isYiiBootstrap($className)) {
                yield $className;
            }
        }

    }
}

/**
 * @param string $path
 * @param string $maskRegExp
 * @param int $depth
 * @return RegexIterator
 */
function getFilesIterator(string $path, string $maskRegExp = '/.*/', int $depth = -1)
{
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $dir->setMaxDepth($depth);
    return new RegexIterator($dir, $maskRegExp);
}

/**
 * @param string $path
 * @return string
 */
function getClassFromPath(string $path): string
{
    return str_replace([dirname(__FILE__, 3) . DIRECTORY_SEPARATOR, '/', '.php'], ['', '\\', ''], $path);
}


