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


use backend\models\EP\Tools;

class IOCountryMap extends IOMap
{
    protected $named = '@country';

    public function serializeTo(\SimpleXMLElement $parent)
    {
        parent::serializeTo($parent);

        static $isoCodes = [];
        if ( $this->value && !isset($isoCodes[$this->value]) ) {
            $isoCodes[$this->value] = false;
            $country_info = \common\helpers\Country::get_country_info_by_id($this->value);
            $isoCodes[$this->value] = $country_info['countries_iso_code_2'];
        }
        if ( isset($isoCodes[$this->value]) ) {
            $parent->addAttribute('iso2', $isoCodes[$this->value]);
        }
    }

    static public function restoreFrom(\SimpleXMLElement $node, $obj)
    {
        $parentResult = parent::restoreFrom($node, $obj);

        if ( empty($parentResult->value) && isset($node['iso2']) && (string)$node['iso2']!='' ){
            $fromIso2 = (string)$node['iso2'];
            $tools = new Tools();
            $internalId = $tools->getCountryId($fromIso2);
            if ( $internalId ) {
                $parentResult->internalId = $internalId;
                $parentResult->value = $internalId;
                IOCore::get()->getAttributeMapper()->mapIds($parentResult, $parentResult->internalId, $parentResult->externalId );
            } else {
                \OscLink\Logger::printf("Country not found: ISO2=$fromIso2 externalID=%s", $node['internalId'] ?? null);
            }
        }
        return $parentResult;
    }


}