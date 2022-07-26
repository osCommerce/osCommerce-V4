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


abstract class Complex{

    protected $named;
    public $table;
    public $attribute;
    public $value;

    public function getMapName()
    {
        return !empty($this->named)?$this->named:($this->table.'.'.$this->attribute);
    }

    public function setMap($table, $attribute)
    {
        $this->table = $table;
        $this->attribute = $attribute;
    }

    public function serializeTo(\SimpleXMLElement $parent)
    {

    }

    static public function restoreFrom(\SimpleXMLElement $node, $obj)
    {
        return strval($node);
    }

    public function toImportModel()
    {
        return $this->value;
    }

}