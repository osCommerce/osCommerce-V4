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

namespace common\classes\modules;

class ModuleStatus{
/**
 * @prop string
 */
  public $key;
/**
 * @prop string
 */
  public $value_enabled;
/**
 * @prop string
 */
  public $value_disabled;

  function __construct($key, $value_enabled, $value_disabled)
  {
    $this->key = $key;
    $this->value_enabled = $value_enabled;
    $this->value_disabled = $value_disabled;
  }
}
