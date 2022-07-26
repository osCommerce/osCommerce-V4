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

trait SceletonExtensionsTrait {

    private $extensionDir;
    private $extensionClass;
    private $viewDir;
    private $controllerShortClass;
    protected $ext;

    protected function initExtensionClass()
    {
        $ref = new \ReflectionClass(get_class($this));
        $this->controllerShortClass = $ref->getShortName();
        $baseDir = dirname($ref->getFileName(), 2); // backend or frontend
        $this->extensionDir = dirname($baseDir);
        \common\helpers\Assert::assert( basename(dirname($this->extensionDir)) == 'extensions', "Unexpected controller path");

        $this->viewDir = $baseDir . '/views/';
        $this->extensionClass = basename($this->extensionDir);
    }

    public function getViewPath()
    {
        return $this->viewDir . DIRECTORY_SEPARATOR . $this->id;
    }

    private function _getAcl(string $actionName, string $controller, bool $default )
    {
        $res = $this->ext::getAcl($actionName, $controller, false);
        if (empty($res)) {
            if ($pos=strpos($actionName, '-')) {
                $res = $this->ext::getAcl(substr($actionName, 0, $pos), $controller, $default);
            } elseif ($default) {
                $res = $this->ext::getAcl($actionName, $controller, true);
            }
        }
        return $res;
    }

    protected function getAcl(string $actionName = '' )
    {
        $res = $this->_getAcl($actionName, $this->id, false);
        if (empty($res)) {
            $res = $this->_getAcl($actionName, $this->controllerShortClass, true); //alternative controller name
        }
        return $res;
    }

    private function initConstruct()
    {
        $this->initExtensionClass();
        $this->ext = \common\helpers\Acl::checkExtensionAllowed($this->extensionClass);
        if (!$this->ext) {
            throw new \yii\web\NotFoundHttpException("$this->extensionClass extension is not allowed.");
        }
        $this->ext::initTranslation('init_controller');
    }
   
}
