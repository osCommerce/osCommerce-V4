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

namespace OscLink;

use \common\helpers\Assert;
use common\extensions\OscLink\models\Configuration;

class Importer implements \OscLink\XML\ImportTuningInterface 
{
    private $downloader;

    private $platform_id = null;
    private $platform_id_def = null;

    private $feed_cur;

    private $batch_count;
    private $batch_offset;
    private $count_in_batch;

    // fat entities have small batch count
    const BATCH_COUNT = ['categories' => 20, 'products' => 10, 'orders' => 10, 'products_options' => 10];

    public function __construct($conf)
    {
        $this->downloader = new \OscLink\Downloader($conf);

        $this->platform_id_def = \common\classes\platform::defaultId();
        $this->platform_id = $conf['api_platform']['cmc_value'];
        if (empty($this->platform_id)) {
            $this->platform_id = $this->platform_id_def;
        }
    }

    public function beforeImportSave($updateObject, $data)
    {
        if ($updateObject instanceof \yii\db\ActiveRecord) {

            // platform_id
            if ($updateObject instanceof \common\models\ProductsDescription) {
                \OscLink\Logger::get()->log_record($updateObject, "platform_id $updateObject->platform_id changed to $this->platform_id_def" );
                $updateObject->platform_id = $this->platform_id_def;
            }
            if ($updateObject instanceof \common\models\Products) {

                // only for import from TL
                $updateObject->products_id_price = 0;
                $updateObject->products_id_stock = 0;
            }
            if ($updateObject instanceof \common\models\Categories) {
                $pc = \common\models\PlatformsCategories::findOne(['categories_id' => $updateObject->categories_id, 'platform_id' => $this->platform_id]);
                if (empty($pc)) {
                    $pc = new \common\models\PlatformsCategories();
                    $pc->categories_id = $updateObject->categories_id;
                    $pc->platform_id = $this->platform_id;
                    $pc->save(false);
                }
            }
        }
    }

    public function afterImport($updateObject, $data, $isNewRecord)
    {
        \OscLink\Logger::get()->log(\OscLink\Helper::getIdentAR($updateObject). (!$isNewRecord? ' was added' : ' was updated'));

        if ($updateObject instanceof \common\models\Products) {
            \Yii::$app->getDb()->createCommand()->upsert(\common\models\PlatformsProducts::tablename(), ['platform_id' => $this->platform_id, 'products_id' => $updateObject->products_id], false)->execute();
        }
    }

    public function afterImportEntity($updateObject, $data, $res)
    {
        $this->count_in_batch++;
        $p = (int) (100 * ($this->batch_offset + $this->count_in_batch) / $this->count_all);
        $p = $p > 100 ? 100 : $p;
        \OscLink\Progress::Percent($p, "$p%");
        if (Configuration::isCancelSign()) {
            throw new \yii\base\UserException('Process was canceled by user.');
        }
    }

    public function finishedImport()
    {
        switch($this->feed_cur) {
            case 'categories':
                \Yii::$app->getDb()->createCommand('UPDATE menus SET last_modified = (SELECT MIN(date_added) - INTERVAL 1 DAY FROM categories)')->execute();
                \common\helpers\Categories::update_categories();
                break;
        }
    }

    public function afterClean($model, $id, $res)
    {
        if ($model instanceof \common\models\Categories) {
            $pc = \common\models\PlatformsCategories::deleteAll(['categories_id' => $id]);
        }
    }

    public function afterCleanEntity($model, $id, $res)
    {
        if (Configuration::isCancelSign()) {
            throw new \yii\base\UserException('Process was canceled by user.');
        }
    }


    public function Import($feeds)
    {
        set_time_limit(0);
        if (!is_array($feeds)) {
            $feeds = [$feeds];
        }
        $this->downloader->checkVersion();

        foreach($feeds as $feed) {
            $this->feed_cur = $feed;
            $feed_name = \OscLink\Helper::getFeedName($feed);

            $this->count_all = $this->downloader->getCount($feed, $errorMsg);
            if ($this->count_all < 0) {
                \OscLink\Progress::Log("Can't import for $feed_name: $errorMsg");
                continue;
            } elseif ($this->count_all == 0) {
                \OscLink\Progress::Log("Nothing import for $feed_name: records not found");
                continue;
            }
            $offset_start = 0;

            $this->batch_count = self::BATCH_COUNT[$feed] ?? 50;

            \OscLink\XML\IOCore::get(); // init Yii::$container

            $imported_sum = [];
            \OscLink\Progress::Percent(0);
            \OscLink\Progress::Log("Start import for $feed_name... Expecting: $this->count_all");
            for ($this->batch_offset=$offset_start; $this->batch_offset < $this->count_all; $this->batch_offset += $this->batch_count) {
                $this->count_in_batch = 0;
                $fn = $this->downloader->getFeed($feed, $this->batch_offset, $this->batch_count);

                $project = new \OscLink\XML\Project($fn);

                $structure = \OscLink\XML\IOCore::getExportStructure($feed);
                $project->setStructure($structure, $this);
                $imported = $project->import();
                \OscLink\Helper::sumCols($imported_sum, $imported);
            }
            $this->finishedImport();
            \OscLink\Progress::Log("Finished import for $feed_name! ". \OscLink\Helper::formatArr("Entities downloaded: $this->count_all, added: {new}, updated: {updated}, skipped: {skipped}, error: {error}", $imported_sum) );
            if ($imported_sum['skipped'] > 0 || $imported_sum['error'] > 0) {
                \OscLink\Progress::showLogFile();
            }
        }
        \OscLink\Progress::Done(true);
    }

    public function Clean(array $feeds)
    {
        set_time_limit(0);
        \OscLink\XML\IOCore::get(); // init Yii::$container
        
        $imported_sum = [];
        $error_sum = 0;
        \OscLink\Progress::$percent_prev_stage = 0;
        \OscLink\Progress::$percent_in_cur_stage = intval(1/count($feeds) * 100);
        foreach($feeds as $feed) {
            $this->feed_cur = $feed;
            $feed_name = \OscLink\Helper::getFeedName($feed);
            \OscLink\Progress::Log("Start cleaning for $feed_name..." );

            $project = new \OscLink\XML\Project();
            $structure = \OscLink\XML\IOCore::getExportStructure($feed);
            $project->setStructure($structure, $this);
            $imported = $project->clean();
            \OscLink\Progress::$percent_prev_stage += \OscLink\Progress::$percent_in_cur_stage;
            \OscLink\Progress::Log("Finished cleaning for $feed_name! " . \OscLink\Helper::formatArr('Entities found: {mapped}, deleted now: {deleted}, deleted before: {not_found}, deleted related: {deleted_related}, error: {error}', $imported));
            if ($imported['error'] > 0) {
                \OscLink\Progress::showLogFile();
                $error_sum += $imported['error'];
            }
        }
        \OscLink\Progress::Done(true);
        return $error_sum;
    }

}