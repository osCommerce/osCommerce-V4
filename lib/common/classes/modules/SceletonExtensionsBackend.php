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

namespace common\classes\modules;

use yii;

abstract class SceletonExtensionsBackend extends \backend\controllers\Sceleton {

    use SceletonExtensionsTrait;

    public function __construct($id, $module = null, $config = [])
    {
        $this->initConstruct();
        parent::__construct($id, $module, $config);
    }

    public function beforeAction($action) {
        if ($action instanceof \yii\base\Action) {
            $actionAcl = self::getAcl($action->id);
            if (!empty($actionAcl)) {
                $this->acl = $actionAcl;
                \common\helpers\Acl::checkAccess($this->acl);
            }
        }
        \common\helpers\Assert::isNotEmpty($this->acl, 'Backend controller without acl');
        return parent::beforeAction($action);
    }

}
