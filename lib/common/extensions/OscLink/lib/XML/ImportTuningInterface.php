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

interface ImportTuningInterface
{
    public function beforeImportSave($updateObject, $data);

    public function afterImport($updateObject, $data, $isNewRecord);

    public function afterImportEntity($updateObject, $data, $res);

    public function afterClean($model, $id, $res);

    public function afterCleanEntity($model, $id, $res);
} 