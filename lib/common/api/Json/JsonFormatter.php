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

namespace common\api\Json;

use Yii;
use yii\base\BaseObject;
use yii\httpclient\FormatterInterface;

class JsonFormatter extends BaseObject implements FormatterInterface
{
    public $charset = null;
    public $contentType = 'application/json';

    public function __construct($config = [])
    {
        $this->charset = trim((!isset($config['charset']) OR !is_scalar($config['charset']) OR (trim($config['charset']) == ''))
            ? Yii::$app->charset : $config['charset']
        );
        parent::__construct($config);
    }
    /**
     * @inheritdoc
     */
    public function format($jsonArray, $options = 0)
    {
        return json_encode($jsonArray, $options);
    }
}