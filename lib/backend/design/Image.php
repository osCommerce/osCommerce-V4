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

use Yii;
use yii\base\Widget;

class Image extends Widget
{
    public $name; //field name to save image
    public $value; // saved image name with path from "image" directory
    public $upload; //field name to upload new image
    public $delete = ''; //field name to delete old image
    public $type = 'image';
    public $acceptedFiles = 'image/*';
    public $unlink = false; // show unlink button
    public $width = 0; // image width, not fixed, if it used with "height" image aspect ratio will be fixed
    public $height = 0; // image height, not fixed, if it used with "width" image aspect ratio will be fixed
    public $positionName = ''; // position field name to save, only if you set width and height
    public $positionValue = ''; // value of position field
    public $fitName = ''; // fit field name to save, only if you set width and height
    public $fitValue = ''; // value of fit field

    public function init(){
        parent::init();
    }

    public function run()
    {
        \backend\design\Data::addJsData(['tr' => \common\helpers\Translation::translationsForJs([
            'IMAGE_FIT', 'IMAGE_FIT_COVER', 'IMAGE_FIT_FILL', 'IMAGE_FIT_CONTAIN', 'IMAGE_FIT_NONE', 'IMAGE_FIT_SCALE_DOWN',
            'IMAGE_POSITION', 'TEXT_MIDDLE_CENTER', 'TEXT_TOP_LEFT', 'TEXT_TOP_CENTER', 'TEXT_TOP_RIGHT', 'TEXT_MIDDLE_LEFT',
            'TEXT_MIDDLE_RIGHT', 'TEXT_BOTTOM_LEFT', 'TEXT_BOTTOM_CENTER', 'TEXT_BOTTOM_RIGHT'
        ], false)]);

        static $id = 0;
        if ($id == 0) {
            $id = rand(1, 10000);
        }
        $id++;

        $data = [
            'name' => $this->name,
            'value' => $this->value,
            'upload' => $this->upload,
            'delete' => $this->delete,
            'acceptedFiles' => $this->acceptedFiles,
            'type' => $this->type,
            'unlink' => $this->unlink,
        ];
        if ($this->width) {
            $data['width'] = $this->width;
        }
        if ($this->height) {
            $data['height'] = $this->height;
        }
        if ($this->positionName) {
            $data['positionName'] = $this->positionName;
        }
        if ($this->positionValue) {
            $data['positionValue'] = $this->positionValue;
        }
        if ($this->fitName) {
            $data['fitName'] = $this->fitName;
        }
        if ($this->fitValue) {
            $data['fitValue'] = $this->fitValue;
        }

        return $this->render('image.tpl', [
            'data' => addslashes(json_encode($data)),
            'id' => $id
        ]);
    }
}