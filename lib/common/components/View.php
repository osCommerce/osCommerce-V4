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

namespace common\components;

use yii\helpers\Html;

/**
 * configured in lib\frontend\config\main.php
 */
class View extends \yii\web\View
{
    /**
     * Renders the content to be inserted at the end of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @param bool $ajaxMode whether the view is rendering in AJAX mode.
     * If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     * will be rendered at the end of the view like normal scripts.
     * @return string the rendered content
     */
    protected function renderBodyEndHtml($ajaxMode)
    {
        $lines = [];
        $files = '';
        $jsFiles = '';

        if (!empty($this->jsFiles[self::POS_END])) {
            $jsFilesEnd = $this->jsFiles[self::POS_END];
            $conditionalFiles = preg_grep('#^<!--\[if#i',$this->jsFiles[self::POS_END]);
            if ( count($conditionalFiles)>0 ) {
                foreach (array_keys($conditionalFiles) as $conditionalKey){
                    unset($jsFilesEnd[$conditionalKey]);
                }
                $jsFiles = implode('',$conditionalFiles)."\n";
            }
            if ( count($jsFilesEnd)>0 ) {
                $files = "['" . implode("', '", array_keys($jsFilesEnd)) . "'], ";
            }
        }

        if ($ajaxMode) {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = implode("\n", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $lines[] = implode("\n", $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $lines[] = implode("\n", $this->js[self::POS_LOAD]);
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = implode("\n", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $lines[] = implode("\n", $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $lines[] = implode("\n", $this->js[self::POS_LOAD]);
            }
        }

        if (!$files && empty($lines)) {
            return $jsFiles;
        }

        return $jsFiles.Html::script("tl(" . $files . "function(){\n" . implode("\n", $lines) . "\n})");
    }
}
