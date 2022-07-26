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


class IOCountryZoneMap extends IOMap
{

    protected $name = '@country_zone';

    public function serializeTo(\SimpleXMLElement $parent)
    {
        parent::serializeTo($parent);

        static $zoneInfo = [];
        if ( $this->value && !isset($zoneInfo[$this->value]) ) {
            $zoneInfo[$this->value] = false;
            $zone_query = tep_db_query(
                "select zone_code as code, zone_name as name ".
                "from " . TABLE_ZONES . " ".
                "where zone_id = '" . (int)$this->value . "'"
            );
            if ( tep_db_num_rows($zone_query)>0 ) {
                $zoneInfo[$this->value] = tep_db_fetch_array($zone_query);
            }
        }
        if ( $zoneInfo[$this->value] ) {
            if ( $zoneInfo[$this->value]['code'] ) {
                $parent->addAttribute('code', $zoneInfo[$this->value]['code']);
            }
            if ( $zoneInfo[$this->value]['name'] ) {
                $parent->addAttribute('name', $zoneInfo[$this->value]['name']);
            }
        }
    }

}