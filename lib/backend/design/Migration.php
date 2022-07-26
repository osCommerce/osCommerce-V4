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

namespace backend\design;

use yii\helpers\FileHelper;
use common\classes\Images;
use common\models\DesignBoxesTmp;

use Yii;

class Migration
{
    protected $themeName;
    protected $migration;

    function __construct($themeName)
    {
        $this->themeName = $themeName;
    }

    public function create($stepsIDs)
    {
        if (!isset($this->themeName) || !isset($stepsIDs) || !is_array($stepsIDs)) {
            return 'error';
        }

        $steps = \common\models\ThemesSteps::find()
            ->where(['IN', 'steps_id', $stepsIDs])
            ->andWhere(['theme_name' => $this->themeName])
            ->asArray()->all();

        $migration = [];
        foreach ($steps as $step) {
            $step['data'] = json_decode($step['data'], true);
            $migration[] = $step;
        }

        return $migration;
    }

    public function apply($migration)
    {
    }

    protected function cssSave($step)
    {
    }

    protected function boxSave($step)
    {
    }

    protected function boxAdd($step)
    {
    }

    protected function blocksMove($step)
    {
    }

    protected function boxDelete($step)
    {
    }

    protected function importBlock($step)
    {
    }

    protected function styleSave($step)
    {
    }

    protected function settings($step)
    {
    }

    protected function extendRemove($step)
    {
    }

    protected function extendAdd($step)
    {
    }

    protected function javascriptSave($step)
    {
    }

    protected function addPage($step)
    {
    }

    protected function addPageSettings($step)
    {
    }

    protected function stylesChange($step)
    {
    }

    protected function removeClass($step)
    {
    }
}
