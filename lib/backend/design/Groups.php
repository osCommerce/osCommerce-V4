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

use common\models\DesignBoxesGroups;
use Yii;
use yii\helpers\FileHelper;

class Groups
{
    public static function synchronize()
    {
        $path = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']);

        $files = [];
        $filesPath = FileHelper::findFiles($path);
        if (is_array($filesPath)) {
            foreach ($filesPath as $filePath) {
                $filePathArr = explode(DIRECTORY_SEPARATOR, $filePath);
                $files[end($filePathArr)] = end($filePathArr);
            }
        }

        $groups = DesignBoxesGroups::find()->asArray()->all();
        foreach ($groups as $group) {
            if ($files[$group['file']]) {
                unset($files[$group['file']]);
            } else {
                DesignBoxesGroups::deleteAll(['file' => $group['file']]);
            }
        }
        foreach ($files as $file) {
            $name = explode('.', $file);
            $designBoxesGroups = new DesignBoxesGroups();
            $designBoxesGroups->file = $file;
            $designBoxesGroups->name = $name[0];
            $designBoxesGroups->date_added = new \yii\db\Expression('now()');
            $designBoxesGroups->save();
        }
    }

    public static function status()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');

        $designBoxesGroup = DesignBoxesGroups::findOne($id);
        $designBoxesGroup->status = (int)$status;
        $designBoxesGroup->save();

        if ($designBoxesGroup->errors) {
            return var_dump($designBoxesGroup->errors);
        }
        return 'ok';
    }

    public static function save()
    {
        $id = Yii::$app->request->post('id');
        $name = Yii::$app->request->post('name');
        $page_type = Yii::$app->request->post('page_type');

        $designBoxesGroup = DesignBoxesGroups::findOne($id);
        $designBoxesGroup->name = $name;
        $designBoxesGroup->page_type = $page_type;
        $designBoxesGroup->save();

        if ($designBoxesGroup->errors) {
            return var_dump($designBoxesGroup->errors);
        }
        return 'ok';
    }

    public static function delete()
    {
        $id = Yii::$app->request->post('id');

        $path = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']);
        $designBoxesGroup = DesignBoxesGroups::findOne($id);
        $designBoxesGroup->file;
        unlink($path . DIRECTORY_SEPARATOR . $designBoxesGroup->file);

        DesignBoxesGroups::deleteAll(['id' => $id]);

        if ($designBoxesGroup->errors) {
            return var_dump($designBoxesGroup->errors);
        }
        return 'ok';
    }

    public static function basename($param, $suffix=null,$charset = 'utf-8')
    {
        if ( $suffix ) {
            $tmpstr = ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, null, $charset), null, $charset), DIRECTORY_SEPARATOR);
            if ( (mb_strpos($param, $suffix, null, $charset)+mb_strlen($suffix, $charset) )  ==  mb_strlen($param, $charset) ) {
                return str_ireplace( $suffix, '', $tmpstr);
            } else {
                return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, null, $charset), null, $charset), DIRECTORY_SEPARATOR);
            }
        } else {
            return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, null, $charset), null, $charset), DIRECTORY_SEPARATOR);
        }
    }

    public static function getWidgetGroups($type)
    {
        $widgets = [];
        $widgets[] = [
            'name' => "title",
            'title' => TEXT_WIDGET_GROUPS,
            'type' => "groups"
        ];

        $designBoxesGroups = DesignBoxesGroups::find()
            ->where(['page_type' => $type, 'status' => 1])
            ->orWhere(['page_type' => '', 'status' => 1])
            ->asArray()->all();

        if (is_array($designBoxesGroups))
        foreach ($designBoxesGroups as $group) {
            $widgets[] = [
                'name' => 'group-' . $group['id'],
                'title' => $group['name'],
                'type' => "groups",
                'description' => $group['comment']
            ];
        }

        return $widgets;
    }
}
