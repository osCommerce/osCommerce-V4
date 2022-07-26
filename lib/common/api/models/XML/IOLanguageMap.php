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
        if (IOCore::get()->isLocalProject()) {
            // force lang mapping for local projects
            if (!empty($this->language)) {
                $arr = \common\helpers\Language::get_language_id($this->language);
                $newId = $arr['languages_id'] ?? null;
                if ( $newId ) {
                    $this->value = $newId;
                    IOCore::get()->getAttributeMapper()->mapIds($this, $newId, $this->internalId);
                } else {
                    $this->value = null;
                }
            }
            return $this->value;
        }

        $parentResult = parent::toImportModel();

        if ( !$parentResult && !empty($this->language) && !IOCore::get()->isLocalProject() ) {
            $newId = \common\classes\language::get_id($this->language);
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