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

chdir('../');
include('includes/application_top.php');

\common\helpers\Translation::init('admin/easypopulate');
\common\helpers\Translation::init('admin/main');
try {
    $messages = new \backend\models\EP\Messages([
        'output' => 'console',
    ]);

    $importJob = new \backend\models\EP\JobFile([
        'directory_id' => 2, //manual import
        'file_name' => 'customers_samples.csv',
        'direction' => 'import',
        'job_provider' => 'orders\\customers',
    ]);
    $importJob->run($messages);
    
    $importJob = new \backend\models\EP\JobFile([
        'directory_id' => 2, //manual import
        'file_name' => 'order_samples.csv',
        'direction' => 'import',
        'job_provider' => 'orders\\order',
    ]);
    $importJob->run($messages);
    
}catch (\Exception $ex){
    echo "err:".$ex->getMessage()."\n".$ex->getTraceAsString()."\n";
}
