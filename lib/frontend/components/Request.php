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

namespace frontend\components;


class Request extends \yii\web\Request {

    public function getIsAjax()
    {
        $origin = $this->headers->get('Origin');

        return
            ($this->headers->get('X-Requested-With') === 'XMLHttpRequest') ||
            ($this->headers->get('Sec-Fetch-Mode') === 'cors') ||
            //($this->headers->get('Sec-Fetch-Site') === 'cross-site') ||
            ($origin !== null && $origin !== $this->getHostInfo());
    }

}
