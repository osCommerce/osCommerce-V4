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

namespace common\helpers;

use common\classes\modules\ModuleVer;

class ModulesMigrations {

    public static function up($code, $sinceVer = null, $type = 'extension', $toVer = null)
    {
        if (empty($sinceVer)) {
            $sinceVer = \common\helpers\Modules::getModuleVerDbInstalled($code, $type);
            if (empty($sinceVer)) {
                $sinceVer = new ModuleVer();
            }
        }
        if (empty($toVer)) {
            $toVer = \common\helpers\Modules::getModuleVerFile($code, $type);
            \common\helpers\Assert::isNotEmpty($toVer, "Cannot get current version for $type: $code");
        }
        self::up_down(true, $code, $type, $sinceVer, $toVer);
    }

    public static function down($code, $downtoVer, $type = 'extension', $sinceVer = null)
    {
        if (empty($sinceVer)) {
            $sinceVer = \common\helpers\Modules::getModuleVerFile($code, $type);
            \common\helpers\Assert::isNotEmpty($sinceVer, "Cannot get current version for $type: $code");
        }
        self::up_down(false, $code, $type, $downtoVer, $sinceVer);
    }


    private static function up_down($up, $code, $type, $sinceVer, $toVer)
    {
        $sinceVer = ModuleVer::parse($sinceVer);
        $toVer = ModuleVer::parse($toVer);
        $module = \common\classes\modules\Module::getModule($code, $type);
        $migrations = $module::getMigrationsSince($code, $sinceVer, $up, $toVer);
        self::do($code, $type, $sinceVer, $toVer, $migrations, $up);
    }

    private static function do($code, $type, ModuleVer $sinceVer, ModuleVer $toVer, $migrations, $up)
    {
        if (!is_array($migrations)) {
            return;
        }
        $func = $up? 'safeUp' : 'safeDown';
        foreach ($migrations as $class) {
            if (!class_exists($class)) {
                \Yii::warning("Can't apply migration: $class does not exist");
                continue;
            }
            $m = \common\models\ModulesMigrations::find()->where(['classname' => $class])->one();
            if ($up && !empty($m)) {
                \Yii::warning("Can't apply migration: $class was already applied");
                continue;
            }
            if (!$up && empty($m)) {
                \Yii::warning("Can't revert migration: $class was not applied");
                continue;
            }

            $migrate = new $class();
            $migrate->compact = true;
            $migrate->$func();

            if ($up) {
                $m = new \common\models\ModulesMigrations();
                $m->code = $code;
                $m->type = $type;
                $m->ver_from = $sinceVer->toNumber();
                $m->ver_to = $toVer->toNumber();
                $m->classname = $class;
                $m->save(false);
            } else {
                $m->delete();
            }
        }
    }

    public static function clear($code, $type = 'extension')
    {
        \common\models\ModulesMigrations::deleteAll(['code' => $code, 'type' => $type]);
    }

}
