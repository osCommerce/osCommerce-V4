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

namespace backend\components;

use yii\base\Widget;

class MessagePopup extends Widget
{
    const MESSAGE_TYPE_SUCCESS = 'success';
    const MESSAGE_TYPE_WARNING = 'warning';

    public $messageType = 'success';
    public $heading = '';
    public $message = '';
    public $clickJs = '';

    public function run()
    {
        return $this->render('MessagePopup', [
            'messageType' => $this->messageType,
            'message' => $this->message,
            'heading' => $this->heading,
            'clickJs' => $this->clickJs,
        ]);
    }
}