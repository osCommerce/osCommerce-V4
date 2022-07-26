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

namespace common\classes;


class SeoMetaFormatArray implements SeoMetaFormatInterface
{

    protected $keys = [];

    public function __construct()
    {
    }

    public function ownMetaTitle()
    {
        return isset($this->keys['META_TITLE'])?$this->keys['META_TITLE']:'';
    }

    public function ownMetaDescription()
    {
        return $this->getMetaFormatKey('META_DESCRIPTION');
    }

    public function getMetaFormatKey($key)
    {
        return isset($this->keys[$key])?$this->keys[$key]:'';
    }

}