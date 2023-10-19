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


class IOLanguageMap extends IOMap
{
    protected $named = '@language';
    public $language;

    public function serializeTo(\SimpleXMLElement $parent)
    {
        if ( !empty($this->value) ) {
            $parent->addAttribute('language', \common\classes\language::get_code($this->value));
            $parent->addAttribute('internalId', $this->value);
            $externalId = IOCore::get()->getAttributeMapper()->externalId($this);
            if ( is_numeric($externalId) ) {
                $parent->addAttribute('externalId', $externalId);
            }
        }
    }

    public function toImportModel()
    {
        // don't import this language
        $this->value = null;
        $this->internalId = null;

        $result = parent::toImportModel(); // 0 if skip

        if (is_null($result)) {

            if (!empty($this->language)) {

                // force lang mapping
                $newId = \common\helpers\Language::get_language_id($this->language);
                if ( $newId ) {
                    $this->value = $newId;
                    $this->internalId = $newId['languages_id'];
                    IOCore::get()->getAttributeMapper()->mapIds($this, $this->internalId, $this->externalId);
                }
            }

        }
        return $this->value;
    }

    public function afterImportModel($value)
    {
        // do nothing
    }
}