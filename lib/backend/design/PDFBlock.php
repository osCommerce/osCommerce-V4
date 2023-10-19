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
use \yii\base\Widget;
use yii\helpers\ArrayHelper;
use frontend\design\Info;

class PDFBox extends \TCPDF {

    public $sizes;
    public $ds;
    public $globalTop = 0;
    public $widthSheet;
    public $blockName;
    public static $fontFamily;

    public function fontFamily($fontFamily)
    {
        switch ($fontFamily) {
            case 'Varela Round': return 'VarelaRound-Regular';
        }

        return $fontFamily;
    }

    public function fontStyles($settings, $content)
    {
        $htm = '<span style="';
        if ($settings[0]['text-align']){
            $htm .= 'text-align:' . $settings[0]['text-align'] . ';';
        }
        if ($settings[0]['color']){
            $htm .= 'color:' . $settings[0]['color'] . ';';
        }
        if ($settings[0]['font-style']){
            $htm .= 'font-style:' . $settings[0]['font-style'] . ';';
        }
        if ($settings[0]['font-family'] == 'Tahoma' || self::$fontFamily == 'Tahoma') {
            if ($settings[0]['font-weight'] == 'bold'){
                $htm .= 'font-family:Tahomabd;';
            } else {
                $htm .= 'font-family:Tahoma;';
            }
        } else {
            if ($settings[0]['font-weight']){
                $htm .= 'font-weight:' . $settings[0]['font-weight'] . ';';
            }
            if ($settings[0]['font-family']){
                $htm .= 'font-family:' . $this->fontFamily($settings[0]['font-family']) . ';';
            }
        }
        if ($settings[0]['text-decoration']){
            $htm .= 'text-decoration:' . $settings[0]['text-decoration'] . ';';
        }
        if ($settings[0]['text-transform']){
            $htm .= 'text-transform:' . $settings[0]['text-transform'] . ';';
        }
        $htm .= '">' . $content . '<span>';

        return $htm;
    }

    private function nullSettings(&$settings) {
        \common\helpers\Php8::nullArrProps($settings[0], ['position',
            'padding-left', 'padding-right', 'padding-top', 'padding-bottom',
            'border-left-width', 'border-right-width', 'border-top-width', 'border-bottom-width',
            'block_type', 'logo_from', 'params', 'pdf',
            'text-align', 'color', 'font-style', 'font-weight', 'font-family', 'text-decoration', 'text-transform', 'font-size', 'style_class',
            'show_text', 'show_number'
        ]);
    }

    public function BlockSizes($name, $page_params, $width, $pdf_params)
    {
        $ds = $pdf_params['dimension_scale'];

        $items_query = tep_db_query("select id, widget_name, widget_params from " . TABLE_DESIGN_BOXES . " where block_name = '" . $name . "' and theme_name = '" . $page_params['theme_name'] . "' order by sort_order");

        while ($item = tep_db_fetch_array($items_query)){

            $this->sizes[$item['id']]['width'] = $width;

            $settings = array();
            $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "' and visibility = ''");
            while ($set = tep_db_fetch_array($settings_query)) {
                $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
            }
            $settings[0]['pdf'] = 1;
            $this->nullSettings($settings);
            if ($settings[0]['position'] == 'absolute') {
                continue;
            }

            $width2 = $width - $settings[0]['padding-left'] * $ds -
                $settings[0]['padding-right'] * $ds -
                $settings[0]['border-left-width'] * $ds -
                $settings[0]['border-right-width'] * $ds;

            if ($item['widget_name'] == 'BlockBox' || $item['widget_name'] == 'email\BlockBox' || $item['widget_name'] == 'cart\CartTabs'){

                $w = $this->widthByType($settings[0]['block_type'], $width2);
                if ($w['1']) $this->BlockSizes('block-' . $item['id'], $page_params, $w['1'], $pdf_params);
                if ($w['2']) $this->BlockSizes('block-' . $item['id'] . '-2', $page_params, $w['2'], $pdf_params);
                if ($w['3']) $this->BlockSizes('block-' . $item['id'] . '-3', $page_params, $w['3'], $pdf_params);
                if ($w['4']) $this->BlockSizes('block-' . $item['id'] . '-4', $page_params, $w['4'], $pdf_params);
                if ($w['5']) $this->BlockSizes('block-' . $item['id'] . '-5', $page_params, $w['5'], $pdf_params);

            } elseif($item['widget_name'] == 'invoice\Container'){

                $this->BlockSizes('block-' . $item['id'], $page_params, $width2, $pdf_params);

            } elseif ($item['widget_name'] == 'Tabs'){

                for($i = 1; $i < 11; $i++) {
                    $this->BlockSizes('block-' . $item['id'] . '-' . $i, $page_params, $width2, $pdf_params);
                }

            } else {


                $widget_array['settings'] = $settings;
                $widget_array['params'] = $page_params;

                if ($item['widget_name'] == 'pdf\PageNumber') {
                    $widget = $this->getAliasNumPage();
                } elseif (($ext_widget = \common\helpers\Acl::runExtensionWidget($item['widget_name'], $widget_array)) !== false){
                    $widget = $ext_widget;
                } else {
                    $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];
                    $widget = '';
                    if (class_exists($widget_name)) {
                        $widget = $widget_name::widget($widget_array);
                    }

                    $widget = preg_replace('/[ ]+/', ' ', $widget);
                    $widget = str_replace('<br> ', '<br>', $widget);
                }

                $pdf2 = clone $this;
                $pdf2->AddPage();
                $pdf2->Set_FontSize($settings[0]['font-size'], $name);
                //$pdf2->Set_FontBold($settings[0]['font-weight'], $name);

                $htm = $this->fontStyles($settings, $widget);

                $pdf2->writeHTMLCell($width2, 0, 0, 0, $htm, 1, 1);

                $height = $pdf2->GetY() + $settings[0]['padding-top'] * $ds +
                    $settings[0]['padding-bottom'] * $ds +
                    $settings[0]['border-top-width'] * $ds +
                    $settings[0]['border-bottom-width'] * $ds;
                $pdf2->deletePage($pdf2->getPage());
                /*if ($item['widget_name'] == 'invoice\Products') {
                    echo '<pre>';
                    var_dump($pdf_params['height'] - $pdf_params['pdf_margin_bottom'] * $ds);
                    var_dump($height);
                    echo '</pre>';die;
                }*/

                $this->sizes[$item['id']]['height'] = $height;
                /*if ($item['widget_name'] == 'Text'){
                  echo '<pre>';
                  var_dump($settings);
                  echo '</pre>';
                }*/

                $this->setTopHeight($height, $name);
            }
        }

    }

    public function getItemHeight($html, $settings, $width, $pdf_params)
    {
        $ds = $pdf_params['dimension_scale'];

        $pdf3 = clone $this;
        $pdf3->AddPage();
        $pdf3->setFontSize($settings[0]['font-size'] * 0.8);

        $htm = $this->fontStyles($settings, $html);

        $pdf3->writeHTMLCell($width, 0, 0, 0, $htm, 1, 1);
        $height = $pdf3->GetY() + $settings[0]['padding-top'] * $ds +
            $settings[0]['padding-bottom'] * $ds +
            $settings[0]['border-top-width'] * $ds +
            $settings[0]['border-bottom-width'] * $ds;
        $pdf3->deletePage($pdf3->getPage());

        return $height;
    }

    public function widthByType($block_type, $width)
    {
        $w['1'] = $w['2'] = $w['3'] = $w['4'] = $w['5'] = 0;

        switch ($block_type){
            case '1':  $w['1'] = $width;                                 break;
            case '2':  $w['1'] = $w['2'] = $width/2;                     break;
            case '3':  $w['1'] = $w['2'] = $w['3'] = round($width/3, 4); break;
            case '4':  $w['1'] = round(($width/3)*2, 4);
                $w['2'] = round($width/3, 4);                     break;
            case '5':  $w['1'] = round($width/3, 4);
                $w['2'] = round(($width/3)*2, 4);                 break;
            case '6':  $w['1'] = $width/4;
                $w['2'] = ($width/4)*3;                           break;
            case '7':  $w['1'] = ($width/4)*3;
                $w['2'] = $width/4;                               break;
            case '8':  $w['1'] = $w['3'] = $width/4;
                $w['2'] = $width/2;                               break;
            case '9':  $w['1'] = $width/5;
                $w['2'] = ($width/5)*4;                           break;
            case '10': $w['1'] = ($width/5)*4;
                $w['2'] = $width/5;                               break;
            case '11': $w['1'] = ($width/5)*2;
                $w['2'] = ($width/5)*3;                           break;
            case '12': $w['1'] = ($width/5)*3;
                $w['2'] = ($width/5)*2;                           break;
            case '13': $w['1'] = $w['3'] = $width/5;
                $w['2'] = ($width/5)*3;                           break;
            case '14': $w['1'] = $w['2'] = $w['3'] = $w['4'] = $width/4; break;
            case '15': $w['1'] =  $w['2'] = $w['3'] = $w['4'] = $w['5'] = $width/5; break;
        }
        return $w;
    }

    public function setTopHeight($height, $name, $n=0)
    {

        if ((!$n && substr($name, 0, 6) == 'block-') || ($n && strpos($name, $n . '1block') === 0)){

            $e = explode('-', $name);
            $id = $e[1];
            if ($this->sizes[$name]['height'] ?? null) {
                $this->sizes[$name]['height'] += $height;
            } else {
                $padding_top = tep_db_fetch_array(tep_db_query("
            select setting_value 
            from " . TABLE_DESIGN_BOXES_SETTINGS . " 
            where box_id = '" . $id . "' and setting_name='padding-top' and visibility = ''"));
                $padding_bottom = tep_db_fetch_array(tep_db_query("
            select setting_value 
            from " . TABLE_DESIGN_BOXES_SETTINGS . " 
            where box_id = '" . $id . "' and setting_name='padding-bottom' and visibility = ''"));
                $border_top_width = tep_db_fetch_array(tep_db_query("
            select setting_value 
            from " . TABLE_DESIGN_BOXES_SETTINGS . " 
            where box_id = '" . $id . "' and setting_name='border-top-width' and visibility = ''"));
                $border_bottom_width = tep_db_fetch_array(tep_db_query("
            select setting_value 
            from " . TABLE_DESIGN_BOXES_SETTINGS . " 
            where box_id = '" . $id . "' and setting_name='border-bottom-width' and visibility = ''"));

                $p = ArrayHelper::getValue($padding_top, 'setting_value') +
                     ArrayHelper::getValue($padding_bottom, 'setting_value') +
                     ArrayHelper::getValue($border_top_width, 'setting_value') +
                     ArrayHelper::getValue($border_bottom_width, 'setting_value');
                $p = $this->ds * $p;

                $height += $p;
                $this->sizes[$name]['height'] = $height;
            }


            //$items_query = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
            //$this->setTopHeight($height, $items_query['block_name']);



            //$items_query = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
            //$this->setTopHeight($height, $items_query['block_name']);
        }

    }

    public function setChooseHeight($n=0)
    {
        $continue = false;
        foreach ($this->sizes as $key => $item) {
            if ((!$n && substr($key, 0, 6) == 'block-') || ($n && strpos($key, $n . '1block') === 0)){
                $e = explode('-', $key);
                $id = $e[1];

                $this->sizes[$id]['height'] = $this->sizes[$id]['height'] ?? null;
                if (!$this->sizes[$id]['height'] || $item['height'] > $this->sizes[$id]['height']) {
                    $this->sizes[$id]['height'] = $item['height'];

                    $this->sizes[($n+1) . '2block-' . $id]['height'] = $item['height'];


                    $continue = true;
                }
            }
        }
        if ($continue){
            $this->setPlusHeight($n+1);
        }
    }
    public function setPlusHeight($n=1)
    {
        $continue = false;
        foreach ($this->sizes as $key => $item) {
            if (strpos($key, $n . '2block') === 0){
                $e = explode('-', $key);
                $id = $e[1];

                $items_query = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));

                if (substr($items_query['block_name'], 0, 6) == 'block-') {
                    $e2 = explode('-', $items_query['block_name']);
                    $id2 = $e2[1];
                    $e2[2] = $e2[2] ?? null;
                    $name = ($n + 1) . '1block-' . $id2 . ($e2[2] ? '-' . $e2[2] : '');
                    $this->setTopHeight($item['height'], $name, $n + 1);
                    $continue = true;
                }
            }
        }
        if ($continue){
            $n++;
            $this->setChooseHeight($n);
        }
    }

    public function setAllHeight_Bak()
    {
        foreach ($this->sizes as $key => $item) {
            if (substr($key, 0, 6) == 'block-'){
                $e = explode('-', $key);
                $id = $e[1];

                if (!$this->sizes[$id]['height'] || $item['height'] > $this->sizes[$id]['height']) {
                    $this->sizes[$id]['height'] = $item['height'];
                }
            }
        }
    }

    public function Set_FontSize($setting, $name)
    {
        $style = 'font_size';
        if ($setting){
            $this->setFontSize($setting * 0.8);
        } else {
            if (substr($name, 0, 6) == 'block-'){
                $e = explode('-', $name);
                $id = $e[1];
                $items_query = tep_db_fetch_array(tep_db_query("select b.block_name, bs.setting_value from " . TABLE_DESIGN_BOXES . " b, " . TABLE_DESIGN_BOXES_SETTINGS . " bs where b.id = '" . $id . "' and b.id = bs.box_id and bs.setting_name='" . $style . "' and bs.visibility = ''"));
                if ($items_query['setting_value'] ?? null) {
                    $this->Set_FontSize($items_query['setting_value'], $items_query['block_name']);
                } else {
                    $block = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
                    $this->Set_FontSize(0, $block['block_name']);
                }
            }
        }
    }

    public function Set_FontBold($setting, $name)
    {
        $style = 'font_weight';
        if ($setting == 'bold'){
            $this->SetFont('', 'B');
        } elseif ($setting == 'normal'){
            $this->SetFont('', '');
        } else {
            $this->SetFont('', '');
            if (substr($name, 0, 6) == 'block-'){
                $e = explode('-', $name);
                $id = $e[1];
                $items_query = tep_db_fetch_array(tep_db_query("select b.block_name, bs.setting_value from " . TABLE_DESIGN_BOXES . " b, " . TABLE_DESIGN_BOXES_SETTINGS . " bs where b.id = '" . $id . "' and b.id = bs.box_id and bs.setting_name='" . $style . "' and bs.visibility = ''"));
                if ($items_query['setting_value']) {
                    $this->Set_FontBold($items_query['setting_value'], $items_query['block_name']);
                } else {
                    $block = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
                    $this->Set_FontBold(0, $block['block_name']);
                }
            }
        }
    }

    public function Set_FontColor($setting, $name)
    {
        $style = 'color';
        if ($setting){
            list($r, $g, $b) = sscanf($setting, "#%02x%02x%02x");
            $this->SetTextColor($r, $g, $b);
        } else {
            if (substr($name, 0, 6) == 'block-'){
                $e = explode('-', $name);
                $id = $e[1];
                $items_query = tep_db_fetch_array(tep_db_query("select b.block_name, bs.setting_value from " . TABLE_DESIGN_BOXES . " b, " . TABLE_DESIGN_BOXES_SETTINGS . " bs where b.id = '" . $id . "' and b.id = bs.box_id and bs.setting_name='" . $style . "' and bs.visibility = ''"));
                if ($items_query['setting_value']) {
                    $this->Set_FontColor($items_query['setting_value'], $items_query['block_name']);
                } else {
                    $block = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
                    $this->Set_FontColor(0, $block['block_name']);
                }
            }
        }
    }
    
    /**
     * extract R,g,b from either #NNnnNN or rgba() string
     * @param string $strColor
     * @return array( R, G, B)
     */

    public static function extractColor($strColor='') {
      $rgb = [];
      if (strpos($strColor, 'rgba')!==false ) {
        preg_match_all('/\d+/', $strColor, $rgb);
        if ($rgb[0] > 3) {
          $rgb = array_slice($rgb[0], 0, 3);
        }
      } else {
        $rgb = sscanf($strColor, "#%02x%02x%02x");
      }
      return $rgb;
    }

    public function createItem($html, $item, $settings, $height, $position, $pdf_params)
    {
        $ds = $pdf_params['dimension_scale'];
        $this->setFontSize($settings[0]['font-size'] * 0.8);

        $width = $this->sizes[$item['id']]['width'];
        $width = $width - $settings[0]['padding-left'] * $ds - $settings[0]['padding-right'] * $ds -
            $settings[0]['border-left-width'] * $ds - $settings[0]['border-right-width'] * $ds;

        $htm = $this->fontStyles($settings, $html);
        $this->writeHTMLCell($width, $height, $position['left'], $position['top'], $htm, 0, 1);

    }

    public function createProductsBlock($item, $page_params, $position, $pdf_params)
    {
        $ds = $pdf_params['dimension_scale'];
        $width = $this->sizes[$item['id']]['width'];

        $settings = [];
        $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "' and visibility = ''");
        while ($set = tep_db_fetch_array($settings_query)) {
            $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
        }

        $widget_array['settings'] = $settings;
        $widget_array['settings'][0]['pdf'] = $settings;
        $widget_array['params'] = $page_params;
        $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];

        $totalProducts = 0;
        if (is_array($page_params['order']->products)) {
            $totalProducts = count($page_params['order']->products);
        }

        $fuse = 0;
        $lastProduct = 0;
        while ($lastProduct < $totalProducts && $fuse < $totalProducts) {
            $fuse++;

            if ($lastProduct == 0) {
                $position['top'] = $position['top'] + $settings[0]['padding-top'] * $ds;
            } else {
                $position['top'] = $pdf_params['pdf_margin_top'] * $ds;
            }

            $height = 0;
            $fitItems = 0;
            for ($i = 30; $i > 2; $i--) {
                $widget_array['params']['from'] = $lastProduct;
                $widget_array['params']['to'] = $lastProduct + $i;
                $widget = '';
                if (class_exists($widget_name)) {
                    $widget = $widget_name::widget($widget_array);
                }
                $widget = preg_replace('/[ ]+/', ' ', $widget);
                $widget = str_replace('<br> ', '<br>', $widget);

                $height = $this->getItemHeight($widget, $settings, $width, $pdf_params);

                if (($position['top'] + $height < $pdf_params['height'] - $pdf_params['pdf_margin_bottom'] * $ds)) {
                    $fitItems = $i;
                    break;
                }
            }

            $widget_array['params']['from'] = $lastProduct;
            $widget_array['params']['to'] = $lastProduct + $fitItems;
            $widget = '';
            if (class_exists($widget_name)) {
                $widget = $widget_name::widget($widget_array);
            }
            $widget = preg_replace('/[ ]+/', ' ', $widget);
            $widget = str_replace('<br> ', '<br>', $widget);

            if ($lastProduct != 0) {
                $page_params['page_number'] = $page_params['page_number'] + 1;
                $this->AddPage();
                $this->pageHeader($page_params, $pdf_params);
                $this->pageFooter($page_params, $pdf_params);
            }
            $this->createItem($widget, $item, $settings, $height, $position, $pdf_params);


            $lastProduct += $fitItems;
        }


        $position['top'] = $position['top'] + $height;

        return $position;
    }

    public function BlockCreate($name, $page_params, $position, $pdf_params)
    {
        $mainStyles = \backend\design\Style::mainStyles($page_params['theme_name']);

        $ds = $pdf_params['dimension_scale'];

        $items_query = tep_db_query("select id, widget_name, widget_params from " . TABLE_DESIGN_BOXES . " where block_name = '" . $name . "' and theme_name = '" . $page_params['theme_name'] . "' order by sort_order");

        while ($item = tep_db_fetch_array($items_query)) {

            $width = $this->sizes[$item['id']]['width'];
            $height = $this->sizes[$item['id']]['height'];
            $heightBox = $this->sizes[$item['id']]['height'];
            if (($position['top'] + $height > $pdf_params['height'] - $pdf_params['pdf_margin_bottom'] * $ds) && !preg_match('/_footer$/', $this->blockName)){

                if ($item['widget_name'] == 'invoice\Products' || $item['widget_name'] == 'packingslip\Products') {
                    $position = $this->createProductsBlock($item, $page_params, $position, $pdf_params);
                    continue;
                }

                if ($height < $pdf_params['height'] - $pdf_params['pdf_margin_top'] - $pdf_params['pdf_margin_bottom']) {
                    $position['top'] = $pdf_params['pdf_margin_top'] * $ds;
                    $page_params['page_number'] = $page_params['page_number'] + 1;
                    $this->AddPage();
                    $this->pageHeader($page_params, $pdf_params);
                    $this->pageFooter($page_params, $pdf_params);
                }

            }

            $settings = array();
            $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "' and visibility = ''");
            while ($set = tep_db_fetch_array($settings_query)) {
                $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
            }
            $settings[0]['pdf'] = 1;
            $this->nullSettings($settings);
            
            $topBox = $position['top'];
            $leftBox = $position['left'];
            if ($settings[0]['position'] == 'absolute') {
                if (isset($settings[0]['width'])) {
                    if ($settings[0]['width_measure'] == '%') {
                        $width = $this->widthSheet * ($settings[0]['width'] / 100);
                    } else {
                        $width = $settings[0]['width'] * $ds;
                    }
                }
                if (isset($settings[0]['height'])) {
                    if ($settings[0]['height_measure'] == '%') {
                        $heightBox = $pdf_params['height'] * ($settings[0]['height'] / 100);
                    } else {
                        $heightBox = $settings[0]['height'] * $ds;
                    }
                }
                if (isset($settings[0]['top'])) {
                    if ($settings[0]['top_measure'] == '%') {
                        $topBox = $pdf_params['top'] * ($settings[0]['top'] / 100);
                    } else {
                        $topBox = $settings[0]['top'] * $ds;
                    }
                }
                if (isset($settings[0]['left'])) {
                    if ($settings[0]['left_measure'] == '%') {
                        $leftBox = $pdf_params['left'] * ($settings[0]['left'] / 100);
                    } else {
                        $leftBox = $settings[0]['left'] * $ds;
                    }
                }
            }


            if (!empty($settings[0]['background-color'])) {
                if ($mainStyles[$settings[0]['background-color']] ?? false) {
                    $settings[0]['background-color'] = $mainStyles[$settings[0]['background-color']];
                }
              // rgba(250,244,151,0.54) or #NNnnNN
                $rgb = self::extractColor($settings[0]['background-color']);
                if (is_array($rgb) && count($rgb)==3) {
                  $this->SetFillColorArray($rgb);
                }
                $this->writeHTMLCell($width, $heightBox, $leftBox, $topBox, ' ', 0, 1, 1);
            }

            if (!empty($settings[0]['background_image'])) {
    /* 2do
              [background-position] => top left
              [background-repeat] => no-repeat
              [background-size] => contain
     */
              $fs_root = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR;
              if (Yii::$app->id != 'app-frontend') {
                $fs_root = $fs_root . '..' . DIRECTORY_SEPARATOR;
              }
              $image = $fs_root .  Info::themeImage($settings[0]['background_image']);

                $fitbox = 'LT';
              if (is_file($image)) {
                  switch ($settings[0]['background-position']) {
                      case 'top left':      $fitbox = 'LT'; break;
                      case 'top center':    $fitbox = 'CT'; break;
                      case 'top right':     $fitbox = 'RT'; break;
                      case 'left':          $fitbox = 'LM'; break;
                      case 'center':        $fitbox = 'CM'; break;
                      case 'right':         $fitbox = 'RM'; break;
                      case 'bottom left':   $fitbox = 'LB'; break;
                      case 'bottom center': $fitbox = 'CB'; break;
                      case 'bottom right':  $fitbox = 'RB'; break;
                  }

                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->getAutoPageBreak();
                $this->SetAutoPageBreak(false, 0);
                $this->Image($image, $leftBox, $topBox, $width, $heightBox, '', '', '', false, 300, '', false, false, 0, $fitbox, false, false);
                // restore auto-page-break status
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
              }
            }

            if ($settings[0]['border-top-width'] ?? false){
                if ($mainStyles[$settings[0]['border-top-color']] ?? false) {
                    $settings[0]['border-top-color'] = $mainStyles[$settings[0]['border-top-color']];
                }
                list($r, $g, $b) = sscanf($settings[0]['border-top-color'], "#%02x%02x%02x");
                $this->Line(
                    $position['left'] - $ds/10 - $ds/10,
                    $position['top'] + ($settings[0]['border-top-width'] * $ds)/2 - $ds/10,
                    $position['left'] + $width + $ds/10,
                    $position['top'] + ($settings[0]['border-top-width'] * $ds)/2 - $ds/10,
                    array('width' => $settings[0]['border-top-width'] * $ds, 'color' => array($r, $g, $b))
                );
            }
            if ($settings[0]['border-left-width'] ?? false){
                if ($mainStyles[$settings[0]['border-left-color']] ?? false) {
                    $settings[0]['border-left-color'] = $mainStyles[$settings[0]['border-left-color']];
                }
                list($r, $g, $b) = sscanf($settings[0]['border-left-color'], "#%02x%02x%02x");
                $this->Line(
                    $position['left'] + ($settings[0]['border-left-width'] * $ds)/2 - $ds/10,
                    $position['top'] - $ds/10,
                    $position['left'] + ($settings[0]['border-left-width'] * $ds)/2 - $ds/10,
                    $position['top'] + $height + $ds/10,
                    array('width' => $settings[0]['border-left-width'] * $ds, 'color' => array($r, $g, $b))
                );
            }
            if ($settings[0]['border-right-width'] ?? false){
                if ($mainStyles[$settings[0]['border-right-color']] ?? false) {
                    $settings[0]['border-right-color'] = $mainStyles[$settings[0]['border-right-color']];
                }
                list($r, $g, $b) = sscanf($settings[0]['border-right-color'], "#%02x%02x%02x");
                $this->Line(
                    $position['left'] + $width - ($settings[0]['border-right-width'] * $ds)/2 + $ds/10,
                    $position['top'] - $ds/10,
                    $position['left'] + $width - ($settings[0]['border-right-width'] * $ds)/2 + $ds/10,
                    $position['top'] + $height + $ds/10,
                    array('width' => $settings[0]['border-right-width'] * $ds, 'color' => array($r, $g, $b))
                );
            }
            if ($settings[0]['border-bottom-width'] ?? false){
                if (($settings[0]['border-bottom-color'] ?? false) && ($mainStyles[$settings[0]['border-bottom-color']] ?? false)) {
                    $settings[0]['border-bottom-color'] = $mainStyles[$settings[0]['border-bottom-color']];
                }
                if ($settings[0]['border-bottom-color'] ?? false) {
                    list($r, $g, $b) = sscanf($settings[0]['border-bottom-color'], "#%02x%02x%02x");
                } else {
                    list($r, $g, $b) = [0, 0, 0];
                }
                $this->Line(
                    $position['left'] - $ds/10,
                    $position['top'] + $height - ($settings[0]['border-bottom-width'] * $ds)/2 + $ds/10,
                    $position['left'] + $width + $ds/10,
                    $position['top'] + $height -($settings[0]['border-bottom-width'] * $ds)/2 + $ds/10,
                    array('width' => $settings[0]['border-bottom-width'] * $ds, 'color' => array($r, $g, $b))
                );
            }

            $width = $width - $settings[0]['padding-left'] * $ds - $settings[0]['padding-right'] * $ds -
                $settings[0]['border-left-width'] * $ds - $settings[0]['border-right-width'] * $ds;

            $p['left'] = $p2['left'] = $position['left'] + $settings[0]['padding-left'] * $ds + $settings[0]['border-left-width'] * $ds;
            $p['top'] = $p2['top'] = $position['top'] + $settings[0]['padding-top'] * $ds + $settings[0]['border-top-width'] * $ds;

            if ($item['widget_name'] == 'BlockBox' || $item['widget_name'] == 'email\BlockBox' || $item['widget_name'] == 'cart\CartTabs'){
                
                if ($settings[0]['style_class'] == 'product' && \frontend\design\Info::$pdfProductsEnd) {
                } else {

                    $w = $this->widthByType($settings[0]['block_type'], $width);
                    if ($w['1']) $this->BlockCreate('block-' . $item['id'], $page_params, $p, $pdf_params);
                    $p['left'] = $p['left'] + $w['1'];
                    if ($w['2']) $this->BlockCreate('block-' . $item['id'] . '-2', $page_params, $p, $pdf_params);
                    $p['left'] = $p['left'] + $w['2'];
                    if ($w['3']) $this->BlockCreate('block-' . $item['id'] . '-3', $page_params, $p, $pdf_params);
                    $p['left'] = $p['left'] + $w['3'];
                    if ($w['4']) $this->BlockCreate('block-' . $item['id'] . '-4', $page_params, $p, $pdf_params);
                    $p['left'] = $p['left'] + $w['4'];
                    if ($w['5']) $this->BlockCreate('block-' . $item['id'] . '-5', $page_params, $p, $pdf_params);
                }

            } elseif($item['widget_name'] == 'invoice\Container'){

                $this->BlockCreate('block-' . $item['id'], $page_params, $p, $pdf_params);

            } elseif ($item['widget_name'] == 'Tabs'){

                for($i = 1; $i < 11; $i++) {

                }

            } else {


                $widget_array['settings'] = $settings;
                $widget_array['settings']['out'] = 1;
                $widget_array['params'] = $page_params;

                if ($item['widget_name'] == 'pdf\PageNumber') {
                    $widget = $this->getAliasNumPage();
                } elseif (($ext_widget = \common\helpers\Acl::runExtensionWidget($item['widget_name'], $widget_array)) !== false){
                    $widget = $ext_widget;
                } else {
                    $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];
                    $widget = '';
                    if (class_exists($widget_name)) {
                        $widget = $widget_name::widget($widget_array);
                    }

                    $widget = preg_replace('/[ ]+/', ' ', $widget);
                    $widget = str_replace('<br> ', '<br>', $widget);
                }


                //$this->Line($this->GetX(), $this->GetY(), $this->GetX() + $width, $this->GetY(),  array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 255)));
                $this->Set_FontSize($settings[0]['font-size'], $name);
                //$this->Set_FontBold($settings[0]['font-weight'], $name);
                //$this->Set_FontColor($settings[0]['color'], $name);

                if (($settings[0]['color'] ?? false) && ($mainStyles[$settings[0]['color']] ?? false)) {
                    $settings[0]['color'] = $mainStyles[$settings[0]['color']];
                }
                $htm = $this->fontStyles($settings, $widget);
                if ($settings[0]['position'] == 'absolute'){
                    $this->writeHTMLCell($width, $heightBox, $leftBox, $topBox, $htm, 0, 1);
                } else {
                    $this->writeHTMLCell($width, $heightBox, $p2['left'], $p2['top'], $htm, 0, 1);
                }

                $this->sizes[$item['id']]['height'] = $height;

                $this->setTopHeight($height, $name);
            }



            $position['top'] = $position['top'] + $height;

            if ($name == 'pdf' || $name == 'pdf_category') {
                $this->globalTop = $position['top'];
            }
        }
    }

    public function Block($name, $page_params, $pdf_params)
    {
        $ds = $pdf_params['dimension_scale'];
        $this->ds = $ds;

        $width = '210';
        $height = '297';
        if (is_array($pdf_params['sheet_format'])){
            $width = $pdf_params['sheet_format'][0];
            $height = $pdf_params['sheet_format'][1];
        } elseif ($pdf_params['sheet_format'] == 'A0' && $pdf_params['orientation'] == 'P') {
            $width = '841';
            $height = '1189';
        }
        elseif ($pdf_params['sheet_format'] == 'A0' && $pdf_params['orientation'] == 'L') {
            $width = '1189';
            $height = '841';
        }
        elseif ($pdf_params['sheet_format'] == 'A1' && $pdf_params['orientation'] == 'P') {
            $width = '594';
            $height = '841';
        }
        elseif ($pdf_params['sheet_format'] == 'A1' && $pdf_params['orientation'] == 'L') {
            $width = '841';
            $height = '594';
        }
        elseif ($pdf_params['sheet_format'] == 'A2' && $pdf_params['orientation'] == 'P') {
            $width = '420';
            $height = '594';
        }
        elseif ($pdf_params['sheet_format'] == 'A2' && $pdf_params['orientation'] == 'L') {
            $width = '594';
            $height = '420';
        }
        elseif ($pdf_params['sheet_format'] == 'A3' && $pdf_params['orientation'] == 'P') {
            $width = '297';
            $height = '420';
        }
        elseif ($pdf_params['sheet_format'] == 'A3' && $pdf_params['orientation'] == 'L') {
            $width = '420';
            $height = '297';
        }
        elseif ($pdf_params['sheet_format'] == 'A4' && $pdf_params['orientation'] == 'P') {
            $width = '210';
            $height = '297';
        }
        elseif ($pdf_params['sheet_format'] == 'A4' && $pdf_params['orientation'] == 'L') {
            $width = '297';
            $height = '210';
        }
        elseif ($pdf_params['sheet_format'] == 'A5' && $pdf_params['orientation'] == 'P') {
            $width = '148';
            $height = '210';
        }
        elseif ($pdf_params['sheet_format'] == 'A5' && $pdf_params['orientation'] == 'L') {
            $width = '210';
            $height = '148';
        }
        elseif ($pdf_params['sheet_format'] == 'A6' && $pdf_params['orientation'] == 'P') {
            $width = '105';
            $height = '148';
        }
        elseif ($pdf_params['sheet_format'] == 'A6' && $pdf_params['orientation'] == 'L') {
            $width = '148';
            $height = '105';
        }

        $this->sizes = array();
        $pdf_params['height'] = $height;
        $this->widthSheet = $width;
        $width = $width - $pdf_params['pdf_margin_left'] * $ds - $pdf_params['pdf_margin_right'] * $ds;




        $position = [
            'top' => $pdf_params['pdf_margin_top'] * $ds,
            'left' => $pdf_params['pdf_margin_left'] * $ds
        ];
        //$this->BlockCreate($name, $page_params, $position, $pdf_params);
        $page_params['page_number'] = 1;
        $this->pageHeader($page_params, $pdf_params);
        $this->pageFooter($page_params, $pdf_params);

        if ($name == 'pdf') {
            if (is_array($page_params['products'])) {


                $this->globalTop = $position['top'];
                if ($page_params['categoryName']) {
                    $this->Bookmark($page_params['categoryName'], 0, 1);

                    $this->sizes = array();
                    $this->BlockSizes('pdf_category', $page_params, $width, $pdf_params);
                    $this->setChooseHeight();
                    $position['top'] = $this->globalTop;
                    $this->BlockCreate('pdf_category', $page_params, $position, $pdf_params);
                }
                \frontend\design\boxes\pdf\ProductElement::widget(['settings' => ['item_clear' => true]]);
                for ($i = 0; $i < count($page_params['products']) && !\frontend\design\Info::$pdfProductsEnd; $i++){
                    $this->sizes = array();
                    $this->BlockSizes($name, $page_params, $width, $pdf_params);
                    $this->setChooseHeight();
                    $position['top'] = $this->globalTop;
                    $this->BlockCreate($name, $page_params, $position, $pdf_params);
                }
                \frontend\design\Info::$pdfProductsEnd = false;
            }
        } else {
            $this->BlockSizes($name, $page_params, $width, $pdf_params);
            $this->setChooseHeight();
            $this->BlockCreate($name, $page_params, $position, $pdf_params);
        }
    }

    public function pageHeader($page_params, $pdf_params)
    {
        if (!$pdf_params['showHeader']) {
            return null;
        }
        static $sizes = [];
        $sizesTmp = $this->sizes;
        if (count($sizes) > 0) {
            $this->sizes = $sizes;
        } else {
            $this->sizes = array();
            $this->BlockSizes($page_params['page_name'] . '_header', $page_params, $this->widthSheet, $pdf_params);
            $this->setChooseHeight();
        }

        $this->BlockCreate($page_params['page_name'] . '_header', $page_params, ['top' => 0, 'left' => 0], $pdf_params);

        $this->sizes = $sizesTmp;
    }

    public function pageFooter($page_params, $pdf_params)
    {
        if (!$pdf_params['showFooter']) {
            return null;
        }
        $this->blockName = $page_params['page_name'] . '_footer';
        $blocks = \common\models\DesignBoxes::find()->where(['block_name' => $this->blockName])->asArray()->all();
        if (!is_array($blocks) || count($blocks) == 0){
            $this->blockName = '';
            return null;
        }

        static $sizes = [];
        $sizesTmp = $this->sizes;
        if (count($sizes) > 0) {
            $this->sizes = $sizes;
        } else {
            $this->sizes = array();
            $this->BlockSizes($this->blockName, $page_params, $this->widthSheet, $pdf_params);
            $this->setChooseHeight();
        }

        $height = 0;
        foreach ($blocks as $block) {
            $height += $this->sizes[$block['id']]['height'] ?? null;
        }

        $this->BlockCreate($this->blockName, $page_params, [
            'top' => $pdf_params['height'] - $height,
            'left' => 0
        ], $pdf_params);
        $this->blockName = '';

        $this->sizes = $sizesTmp;
    }

}



class PDFBlock extends Widget
{

    public $pages;
    public $params;

    public function init()
    {
        parent::init();
        \common\helpers\Translation::init('admin/orders');
    }


    public function run()
    {
        $theme = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES));

        $default = [
            'theme_name' => $theme['theme_name'],
            'document_name' => 'document',
            'sheet_format' => 'A4',
            'orientation' => 'P',
            'destination' => 'I',/*Destination where to send the document. It can take one of the following values:
I: send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the “Save as” option on the link generating the PDF.
D: send to the browser and force a file download with the name given by name.
F: save to a local server file with the name given by name.
S: return the document as a string. name is ignored.
FI: equivalent to F + I option
FD: equivalent to F + D option*/
            'title' => 'document',
            'subject' => 'document',
            'keywords' => '',
            'pdf_margin_top' => 25,
            'pdf_margin_left' => 20,
            'pdf_margin_right' => 20,
            'pdf_margin_bottom' => 25,
            'dimension_scale' => 0.3,
            'showTOC' => false,
            'pageNumberTOC' => '',//page number where this TOC should be inserted (leave empty for current page)
            'showHeader' => true,//header created in designer
            'showFooter' => true,//footer created in designer
        ];

        $params = array_merge($default, $this->params);


        $params = $this->setPageSettings($params);


        $ds = $params['dimension_scale'];
        stream_context_set_default(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        
        $pdf = new PDFBox($params['orientation'], 'mm', $params['sheet_format'], true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(defined('STORE_NAME')?STORE_NAME:'Holbi');
        $pdf->SetTitle($params['title']);
        $pdf->SetSubject($params['subject']);
        $pdf->SetKeywords($params['keywords']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins($params['pdf_margin_left'] * $ds, $params['pdf_margin_top'] * $ds, $params['pdf_margin_right'] * $ds);
        //$pdf->SetAutoPageBreak(TRUE, $params['pdf_margin_bottom'] * $ds);
        $pdf->SetAutoPageBreak(false);

        //\TCPDF_FONTS::addTTFfont(DIR_FS_CATALOG . 'themes/basic/fonts/Hind-Bold.ttf');
        $params['pdf_font_family'] = $params['pdf_font_family'] ?? null;
        $pdf->SetFont($params['pdf_font_family'] ? $params['pdf_font_family'] : 'Helvetica', '', 12);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf::$fontFamily = $params['pdf_font_family'];

        if (is_array($this->pages ?? null)){
            foreach ($this->pages as $page){

                if ($page['params']['theme_name'] ?? null){
                    $theme_name =  $page['params']['theme_name'];
                } elseif ($params['theme_name'] ?? null){
                    $theme_name =  $params['theme_name'];
                } else {
                    $theme = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES));
                    $theme_name =  $theme['theme_name'];
                }

                $items_query = tep_db_query("select id, widget_name, widget_params from " . (\frontend\design\Info::isAdmin() ? TABLE_DESIGN_BOXES_TMP : TABLE_DESIGN_BOXES) . " where block_name = '" . $page['name'] . "' and theme_name = '" . $theme_name . "' order by sort_order");

                $count = tep_db_num_rows($items_query);
                if ($count > 0){

                    $pdf->AddPage();



                    $pdf->Block($page['name'], array_merge($page['params'], [
                        'theme_name' => $theme_name,
                        'page_name' => $page['name']
                    ]), $params);


                }
            }
        }

        if ($params['showTOC'] ?? null) {
            // add a new page for Table of Content
            $pdf->addTOCPage();
            // write the TOC title
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->MultiCell(0, 0, PDF_CONTENT, 0, 'C', 0, 1, '', '', true, 0);
            $pdf->Ln();
            $pdf->SetFont('dejavusans', '', 12);
            // add a simple Table Of Content at first page
            // (check the example n. 59 for the HTML version)
            $pdf->addTOC($params['pageNumberTOC'], 'courier', '.', '');
            // end of TOC page
            $pdf->endTOCPage();
        }

        $params['destination'] = $params['destination'] ?? null;
        if ($params['destination'] == 'S') {
          return $pdf->Output($params['document_name'], $params['destination']);
        } else {
          $pdf->Output($params['document_name'], $params['destination']);
        }
        if (!in_array($params['destination'],['S', 'F'])) {
            die;
        }
    }


    public function setPageSettings($params)
    {
        if ($this->pages[0]['params']['theme_name'] ?? null){
            $theme_name =  $this->pages[0]['params']['theme_name'];
        } elseif ($params['theme_name'] ?? null){
            $theme_name =  $params['theme_name'];
        } else {
            $theme = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES));
            $theme_name =  $theme['theme_name'];
        }

        $pageSettingsQuery = \common\models\ThemesSettings::find()->where([
            'theme_name' => $theme_name,
            'setting_group' => 'added_page_settings',
            'setting_name' => $this->pages[0]['name'] ?? null,
        ])->asArray()->all();

        $pageSettings = [];
        foreach ($pageSettingsQuery as $sett) {
            $setArr = explode(':', $sett['setting_value']);
            $pageSettings[$setArr[0]] = $setArr[1];
        }
        \common\helpers\Php8::nullArrProps($pageSettings, ['sheet_format', 'orientation', 'page_width', 'page_height', 'page_height', 'page_width', 'pdf_font_family',
            'pdf_margin_top', 'pdf_margin_left', 'pdf_margin_right', 'pdf_margin_bottom', 'dimension_scale']);
        if ($pageSettings['sheet_format'] && $pageSettings['sheet_format'] != 'size'){
            $params['sheet_format'] = $pageSettings['sheet_format'];
            if ($pageSettings['orientation']) {
                $params['orientation'] = $pageSettings['orientation'];
            }
        } elseif ($pageSettings['page_width'] && $pageSettings['page_height']) {
            $params['sheet_format'] = [
                $pageSettings['page_width'],
                $pageSettings['page_height']
            ];
            $params['orientation'] = ($pageSettings['page_height'] > $pageSettings['page_width']) ? 'P' : 'L';
        }
        if ($pageSettings['pdf_margin_top']) {
            $params['pdf_margin_top'] = $pageSettings['pdf_margin_top'] / $params['dimension_scale'];
        }
        if ($pageSettings['pdf_margin_left']) {
            $params['pdf_margin_left'] = $pageSettings['pdf_margin_left'] / $params['dimension_scale'];
        }
        if ($pageSettings['pdf_margin_right']) {
            $params['pdf_margin_right'] = $pageSettings['pdf_margin_right'] / $params['dimension_scale'];
        }
        if ($pageSettings['pdf_margin_bottom']) {
            $params['pdf_margin_bottom'] = $pageSettings['pdf_margin_bottom'] / $params['dimension_scale'];
        }
        if ($pageSettings['pdf_font_family']) {
            $params['pdf_font_family'] = $pageSettings['pdf_font_family'];
        }

        return $params;
    }

 
}