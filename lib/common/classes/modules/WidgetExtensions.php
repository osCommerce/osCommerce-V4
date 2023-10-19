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

#[\AllowDynamicProperties]

class WidgetExtensions extends \yii\base\Widget
{
    public $name;
    public $params;
    public $settings;
    public $id;

    public $extName = null;
    public $extDir = null;
    /**
     * @var \common\classes\modules\ModuleExtensions
     */
    public $extClass = null;

    private static $ext = [];

    public function __construct($config = [])
    {
        parent::__construct($config);
        $ext = self::initExtensionClass();
        $this->extName = $ext['name'];
        $this->extDir = $ext['dir'];
        $this->extClass = $ext['class'];
        self::allowed(); //init translations for settings
    }

    public function getTranslation($key, $entity = '')
    {
        if (self::allowed()) {
            return $this->extClass::getTranslationValue($key, $entity);
        }
    }

    /**
     * For auto check into Acl::runExtensionWidget()
     * @return bool
     */
    public static function allowed()
    {
        return \common\helpers\Extensions::isAllowed(self::initExtensionClass()['name']);
    }

    public function beforeRun()
    {
        return static::allowed() && parent::beforeRun();
    }

    private static function initExtensionClass()
    {
        $called = get_called_class();
        if (!isset(self::$ext[$called])) {
            $ref = new \ReflectionClass($called);
            // find extension class
            $dir = dirname($ref->getFileName());
            $count = 0;
            while (basename(dirname($dir)) != 'extensions') {
                $dir = dirname($dir);
                \common\helpers\Assert::assert(!empty($dir) && $dir != '/' && $count <= 4, "Can't find extension class. Current dir: $dir. Count: $count");
                \common\helpers\Assert::assert($count <= 4, "Can't find extension class. Directory nesting is too deeply. Current dir: $dir. Count: $count");
                $count++;
            }

            $extName = basename($dir);
            self::$ext[$called]['name'] =  $extName;
            self::$ext[$called]['dir'] = $dir;
            self::$ext[$called]['class'] = "\\common\\extensions\\$extName\\$extName";
        }
        return self::$ext[$called];
    }

}