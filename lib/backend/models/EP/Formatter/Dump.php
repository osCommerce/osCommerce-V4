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

namespace backend\models\EP\Formatter;

use backend\models\EP;

class Dump implements FormatterInterface
{

  function __construct($config, $filename)
  {

  }

  public function getHeaders()
  {
    // TODO: Implement getHeaders() method.
  }

  public function write_array($data_array)
  {
    echo '<pre>'; var_export($data_array); echo '</pre>';
  }

  public function setReadRemapArray($data_array)
  {
    // TODO: Implement setReadRemapArray() method.
  }

  public function read_array()
  {
    // TODO: Implement read_array() method.
  }

}