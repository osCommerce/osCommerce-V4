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

namespace frontend\design;

use Yii;
use yii\base\Widget;

class IncludeTpl extends Widget
{

    public $file;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (empty(Yii::$app->view->theme->pathMap['@app/views'])) return '';

        if ( substr($this->file, 0, 1)==='@' && is_file(Yii::getAlias($this->file)) ) {
            return $this->render($this->file, $this->params);
        }

        for ($i = 0; $i < count(Yii::$app->view->theme->pathMap['@app/views']); $i++) {
            if (file_exists(Yii::getAlias(Yii::$app->view->theme->pathMap['@app/views'][$i]) . '/' . $this->file)) {
                return $this->render(Yii::$app->view->theme->pathMap['@app/views'][$i] . '/' . $this->file, $this->params);
            }
        }

        // if file does not found in frontend, search it in backend
        for ($i = 0; $i < count(Yii::$app->view->theme->pathMap['@app/views']); $i++) {
            $path = Yii::getAlias(Yii::$app->view->theme->pathMap['@app/views'][$i]);
            $path = str_replace('lib/backend', 'lib/frontend', $path);
            $path = str_replace('lib\backend', 'lib/frontend', $path);
            if (file_exists($path . '/' . $this->file)) {
                $path2 = str_replace('@app', '@app/../frontend', Yii::$app->view->theme->pathMap['@app/views'][$i]);
                return $this->render($path2 . '/' . $this->file, $this->params);
            }
        }
        return '';
    }
}