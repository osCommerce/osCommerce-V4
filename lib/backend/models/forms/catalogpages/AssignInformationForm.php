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

namespace backend\models\forms\catalogpages;

use yii\base\Model;

class AssignInformationForm extends Model {

	public $information_id;
	public $page_title;
	public $hide;

    public function rules()
    {
        return [
            [['information_id'], 'required'],
            ['information_id', 'integer', 'min' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'information_id' => TEXT_INFORMATION,
        ];
    }

}

