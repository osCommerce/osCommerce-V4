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


namespace frontend\design\boxes;

interface ButtonListingInterface
{
    /** @return bool */
    public function isAllowed(): bool;
    /**
     * ascending
     * @return int
     */
    public function getPriority(): int;
    /**
     * @param int $priority
     * @return ButtonListingInterface
     */
    public function setPriority(int $priority): ButtonListingInterface;
}
