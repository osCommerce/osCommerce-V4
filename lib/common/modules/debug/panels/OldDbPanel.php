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

namespace common\modules\debug\panels;

use yii;
use yii\debug\Panel;

class OldDbPanel extends yii\debug\panels\DbPanel
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'TEP';
    }

    /**
     * @return string short name of the panel, which will be use in summary.
     */
    public function getSummaryName()
    {
        return 'TEP_DB';
    }

    /**
     * Returns all profile logs of the current request for this panel. It includes categories such as:
     * 'yii\db\Command::query', 'yii\db\Command::execute'.
     * @return array
     */
    public function getProfileLogs()
    {
        $target = $this->module->logTarget;

        return $target->filterMessages($target->messages, yii\log\Logger::LEVEL_PROFILE, ['tep::db_query']);
    }

    protected function hasExplain()
    {
        return false;
    }
}