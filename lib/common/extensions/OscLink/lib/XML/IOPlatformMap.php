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

namespace OscLink\XML;


class IOPlatformMap extends Complex
{
    protected $name = '@platform';

    public function serializeTo(\SimpleXMLElement $parent)
    {
        if ( !empty($this->value) ) {
            $parent->addAttribute('internalId', $this->value);
            $externalId = IOCore::get()->getAttributeMapper()->externalId($this);
            if ( is_numeric($externalId) ) {
                $parent->addAttribute('externalId', $externalId);
            }
        }
    }
}