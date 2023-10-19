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

namespace backend\design\boxes\account;

use Yii;
use yii\base\Widget;

class AddressesList extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('CustomerAdditionalFields')) return '';
        global $languages_id;

        $groups = \common\extensions\CustomerAdditionalFields\models\AdditionalFieldsGroupDescription::find()
            ->select(['id' => 'additional_fields_group_id', 'title'])
            ->where(['language_id' => $languages_id])
            ->asArray()->all();

        return $this->render('../../views/account/addresses-list.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'groups' => $groups,
        ]);
    }
}