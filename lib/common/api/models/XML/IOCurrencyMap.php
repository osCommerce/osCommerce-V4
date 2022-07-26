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

namespace common\api\models\XML;


class IOCurrencyMap extends Complex
{
    protected $name = '@currency';
    public $currency;

    public function serializeTo(\SimpleXMLElement $parent)
    {
        if ( !empty($this->value) ) {
            static $currencyCodes;
            if ( !is_array($currencyCodes) ) {
                $currencyCodes = array();
                $curr = new \common\classes\currencies();
                foreach ($curr->currencies as $currInfo) {
                    $currencyCodes[ $currInfo['id'] ] = $currInfo['code'];
                }
            }
            $parent->addAttribute('currency', $currencyCodes[$this->value]);
            $parent->addAttribute('internalId', $this->value);
            $externalId = IOCore::get()->getAttributeMapper()->externalId($this);
            if ( is_numeric($externalId) ) {
                $parent->addAttribute('externalId', $externalId);
            }
        }
    }

    public function toImportModel()
    {
        echo '<pre>'; var_dump($this); echo '</pre>'; die;
        $parentResult = parent::toImportModel();

        if ( !$parentResult && !empty($this->currency) && !IOCore::get()->isLocalProject() ) {
            $newId = 0;
            $curr = new \common\classes\currencies();
            foreach ($curr->currencies as $currInfo) {
                if ( $this->currency==$currInfo['code'] ) {
                    $newId = $currInfo['id'];
                    break;
                }
            }
            echo '<pre>'; var_dump($this->currency, $newId, $curr->currencies ); echo '</pre>';

            if ( $newId ) {
                $this->internalId = $newId;
                $this->value = $newId;
                $parentResult = $newId;
                IOCore::get()->getAttributeMapper()->mapIds($this, $this->internalId, $this->externalId);
            }
        }

        return $parentResult;
    }

}