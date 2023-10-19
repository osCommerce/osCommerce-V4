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

use common\models\ThemesSettings;
use common\models\ThemesStylesMain;
use Yii;
use yii\helpers\ArrayHelper;
use common\models\ThemesStyles;

class Style
{
    public const STYLE_CACHE_LIFETIME = 15;
    public static $cssFrontend = false;

    public static function hide($class)
    {
        $arr = array();

        if ($class == 'body') {
            $arr = [
                'hover' => 1,
                'display' => 1,
                'padding' => 1,
                'border' => 1,
                'size' => 1,
            ];
        }

        if ($class == 'a') {
            $arr = [
                'font' => [
                    'font_size' => 1,
                    'line_height' => 1,
                    'text_align' => 1,
                    'vertical_align' => 1,
                ],
                'padding' => 1,
                'border' => 1,
                'size' => 1,
                'display' => 1,
            ];
        }

        if ($class == '.main-width, .type-1 > .block') {
            $arr = [
                'hover' => 1,
                'font' => 1,
                'background' => 1,
                'border' => 1,
                'size' => [
                    'width' => 1,
                    'min_width' => 1,
                    'height' => 1,
                    'min_height' => 1,
                    'max_height' => 1,
                ],
                'display' => 1,
            ];
        }

        if ($class == '.menu-slider .close') {
            $arr = [
                'font' => [
                    'font_family' => 1,
                    'vertical_align' => 1,
                ],
                'size' => [
                    'min_width' => 1,
                    'min_height' => 1,
                    'max_width' => 1,
                    'max_height' => 1,
                ],
                'display' => 1,
            ];
        }

        return $arr;
    }

    public static function show($class)
    {
        $arr = array();

        if (
            $class == '.w-tabs .tab-a' ||
            $class == '.menu-style-1 > ul > li' ||
            $class == '.menu-style-1 > ul > li > ul > li' ||
            $class == '.menu-style-1 > ul > li > ul > li > ul > li' ||
            $class == '.menu-style-1 > ul > li > ul > li > ul > li > ul > li' ||
            $class == '.menu-slider > ul > li' ||
            $class == '.menu-slider > ul > li > ul > li' ||
            $class == '.menu-slider > ul > li > ul > li > ul > li' ||
            $class == '.menu-slider > ul > li > ul > li > ul > li > ul > li' ||
            $class == '.menu-horizontal > ul > li' ||
            $class == '.menu-horizontal > ul > li > ul > li' ||
            $class == '.menu-horizontal > ul > li > ul > li > ul > li' ||
            $class == '.menu-horizontal > ul > li > ul > li > ul > li > ul > li' ||
            $class == 'a.my-acc-link' ||
            $class == '.paging a, .paging span' ||
            $class == '.page-style a.grid' ||
            $class == '.page-style a.list' ||
            $class == '.page-style a.b2b'
        ) {
            $arr = [
                'active' => 1,
            ];
        }

        return $arr;
    }

    public static function cssCompile($css, $theme_name, $accessibility = '')
    {
        $css = preg_replace('/\/\*.+\*\//', ' ', $css);
        $attributes = array();

        //foreach (self::explodeByAccessibility($css) as $accessibility => $styles) {

            $blocks = self::explodeByMediaBlocks($css, $theme_name);

            $attributes = array_merge($attributes, self::parsBlock($blocks['no_media'], '', '', $accessibility, $theme_name));

            foreach ($blocks['visibility'] as $key => $value) {
                $attributes = array_merge($attributes, self::parsBlock($value, $key, '', $accessibility, $theme_name));
            }

            foreach ($blocks['media'] as $key => $value) {
                $attributes = array_merge($attributes, self::parsBlock($value, '', $key, $accessibility, $theme_name));
            }
        //}

        return $attributes;
    }

    public static function getAccessibility($selector, $theme_name)
    {
        $acc = '';
        if (preg_match('/^(\.p-[0-9a-zA-Z\-\_]+)/', $selector, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^(\.b-[0-9a-zA-Z\-\_]+)/', $selector, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^(\.s-[0-9a-zA-Z\-\_]+)/', $selector, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^(\.w-[0-9a-zA-Z\-\_]+)/', $selector, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^\.p-[0-9a-zA-Z\-\_]+[\s]+(\.w-[0-9a-zA-Z\-\_]+)/', $selector, $matches)) {
            return $matches[1];
        }

        static $classes = [];

        if (count($classes) == 0) {
            $query = tep_db_query("
                select distinct setting_value 
                from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " 
                where setting_name = 'style_class'");
            while ($item = tep_db_fetch_array($query)) {
                $styleClass = $item['setting_value'];
                $styleClass = preg_replace('/[\s]+/', ' ', $styleClass);
                $cl = explode(' ', $styleClass);
                foreach ($cl as $cl) {
                    $classes[] = $cl;
                }
            }
        }

        foreach ($classes as $class) {
            if (strpos($selector, '.' . $class . ' ') === 0) {
                return '.' . $class;
            }
        }

        return $acc;
    }

    public static function parsBlock($block, $visibility = '', $media = '', $accessibility = '', $theme_name = '')
    {
        $classArr = explode('}', $block);
        $attributes = array();
        foreach ($classArr as $class) {
            $vClass = $visibility;
            $first = stripos($class, '{');
            $selector = trim(substr($class, 0, $first));
            $selector = preg_replace('/\n/', ' ', $selector);
            $selector = preg_replace('/\/\*[.\n]+\*\//', '', $selector);
            $selector = preg_replace('/[\s]+/', ' ', $selector);

            if ($accessibility) {
                $sl = explode(',', $selector);
                foreach ($sl as $slItem => $slVal) {
                    $slVal = trim($slVal);
                    if (substr($slVal, 0, 1) == '&') {
                        $sl[$slItem] = $accessibility . substr($slVal, 1);
                    } else {
                        $sl[$slItem] = $accessibility . ' ' . $slVal;
                    }
                }
                $selector = implode(', ', $sl);
            }

            $acc = self::getAccessibility($selector, $theme_name);

            $selectorArr = explode(',', $selector);
            $classTmp = '';
            $psClass = '';
            $editor = true;
            foreach ($selectorArr as $item) {
                $pos = stripos($item, ':');
                $posActive = stripos($item, '.active');
                if ($pos || $posActive) {
                    if ($posActive) {
                        $psClass = substr($item, $posActive);
                    }
                    if (!$psClass) {
                        $psClass = substr($item, $pos);
                    }
                    $last = str_replace('.active', '', $psClass);
                    $last = str_replace(':hover', '', $last);
                    $last = str_replace(':before', '', $last);
                    $last = str_replace(':after', '', $last);
                    if ($last) {
                        $editor = false;
                    }
                    if ($psClass && $classTmp && $psClass != $classTmp) {
                        $editor = false;
                    }
                    $classTmp = $psClass;
                }
            }
            if ($editor && $psClass) {
                if (stripos($psClass, '.active') !== false) {
                    $vClass .= ($vClass ? ',' : '') . 2;
                }
                if (stripos($psClass, ':hover') !== false) {
                    $vClass .= ($vClass ? ',' : '') . 1;
                }
                if (stripos($psClass, ':before') !== false) {
                    $vClass .= ($vClass ? ',' : '') . 3;
                }
                if (stripos($psClass, ':after') !== false) {
                    $vClass .= ($vClass ? ',' : '') . 4;
                }
                $arr = array();
                foreach ($selectorArr as $item) {
                    $arr[] = str_replace($psClass, '', $item);
                }
                $selector = implode(',', $arr);
            }

            $content = trim(substr($class, $first+1));

            $rows = explode(';', $content);
            foreach ($rows as $row) {
                $rowExplode = explode(':', $row);
                $attribute = trim($rowExplode[0]);
                $value = (isset($rowExplode[1]) ? trim($rowExplode[1]) : '');
                if ($selector && $attribute && $value !== '') {
                    $attributeTl = self::parsAttributes($attribute, $value);
                    foreach ($attributeTl as $item) {
                        $attributes[] = [
                            'selector' => $selector,
                            'attribute' => $item['attribute'],
                            'value' => $item['value'],
                            'visibility' => $vClass,
                            'media' => $media,
                            'accessibility' => $acc,
                        ];
                    }
                }
            }
        }

        return $attributes;
    }

    public static function parsAttributes($attribute, $value)
    {
        $attr = array();
        $default = false;

        $attributeValueSize = ['top', 'left', 'right', 'bottom', 'width', 'min-width', 'max-width', 'height', 'min-height', 'max-height', 'font-size', 'line-height', 'padding-top', 'padding-left', 'padding-right', 'padding-bottom', 'margin-top', 'margin-left', 'margin-right', 'margin-bottom'];

        $importantAttr = false;
        if (strpos($value, '!important') !== false) {
            $value = str_replace('!important', '', $value);
            $value = trim($value);
            $importantAttr = true;
        }

        if ($attribute == 'padding') {
            if (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'padding-top',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_top_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-right',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_right_measure',
                        'value' => $matches[4]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-bottom',
                    'value' => $matches[5]
                ];
                if ($matches[6] && $matches[6] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_bottom_measure',
                        'value' => $matches[6]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-left',
                    'value' => $matches[7]
                ];
                if ($matches[8] && $matches[8] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_left_measure',
                        'value' => $matches[8]
                    ];
                }
            } elseif (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'padding-top',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_top_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-right',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_right_measure',
                        'value' => $matches[4]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-bottom',
                    'value' => $matches[5]
                ];
                if ($matches[6] && $matches[6] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_bottom_measure',
                        'value' => $matches[6]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-left',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_left_measure',
                        'value' => $matches[4]
                    ];
                }
            } elseif (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'padding-top',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_top_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-right',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_right_measure',
                        'value' => $matches[4]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-bottom',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_bottom_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-left',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_left_measure',
                        'value' => $matches[4]
                    ];
                }
            } elseif (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'padding-top',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_top_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-right',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_right_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-bottom',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_bottom_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'padding-left',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'padding_left_measure',
                        'value' => $matches[2]
                    ];
                }
            } else {
                $default = true;
            }
        } elseif ($attribute == 'margin') {
            if (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'margin-top',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_top_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-right',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_right_measure',
                        'value' => $matches[4]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-bottom',
                    'value' => $matches[5]
                ];
                if ($matches[6] && $matches[6] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_bottom_measure',
                        'value' => $matches[6]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-left',
                    'value' => $matches[7]
                ];
                if ($matches[8] && $matches[8] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_left_measure',
                        'value' => $matches[8]
                    ];
                }
            } elseif (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'margin-top',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_top_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-right',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_right_measure',
                        'value' => $matches[4]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-bottom',
                    'value' => $matches[5]
                ];
                if ($matches[6] && $matches[6] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_bottom_measure',
                        'value' => $matches[6]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-left',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_left_measure',
                        'value' => $matches[4]
                    ];
                }
            } elseif (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'margin-top',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_top_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-right',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_right_measure',
                        'value' => $matches[4]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-bottom',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_bottom_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-left',
                    'value' => $matches[3]
                ];
                if ($matches[4] && $matches[4] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_left_measure',
                        'value' => $matches[4]
                    ];
                }
            } elseif (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'margin-top',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_top_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-right',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_right_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-bottom',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_bottom_measure',
                        'value' => $matches[2]
                    ];
                }
                $attr[] = [
                    'attribute' => 'margin-left',
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'margin_left_measure',
                        'value' => $matches[2]
                    ];
                }
            } else {
                $default = true;
            }
        } elseif ($attribute == 'line-height') {
            if (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'em') {
                    $attr[] = [
                        'attribute' => 'line_height_measure',
                        'value' => $matches[2]
                    ];
                }
            } else {
                $default = true;
            }
        } elseif ($attribute == 'transform') {
            if (preg_match('/^rotate\(([\-0-9\.]+)deg\)$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'rotate',
                    'value' => $matches[1]
                ];
            } else {
                $default = true;
            }
        } elseif ($attribute == 'content') {
            if ($value == "''" || $value == '""') {
                $attr[] = [
                    'attribute' => 'content',
                    'value' => '\\_'
                ];
            } else {
                $attr[] = [
                    'attribute' => 'content',
                    'value' => substr(substr($value, 0, -1), 1)
                ];
            }
        } elseif (in_array($attribute, $attributeValueSize)) {
            if (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => $matches[1]
                ];
                if ($matches[2] && $matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => str_replace('-', '_', $attribute) . '_measure',
                        'value' => $matches[2]
                    ];
                }
            } else {
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => $value
                ];
            }
        } elseif ($attribute == 'border-radius') {
            if (preg_match('/^([0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'border-top-left-radius',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'border_radius_1_measure',
                    'value' => $matches[2]
                ];
                $attr[] = [
                    'attribute' => 'border-top-right-radius',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'border_radius_2_measure',
                    'value' => $matches[2]
                ];
                $attr[] = [
                    'attribute' => 'border-bottom-right-radius',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'border_radius_3_measure',
                    'value' => $matches[2]
                ];
                $attr[] = [
                    'attribute' => 'border-bottom-left-radius',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'border_radius_4_measure',
                    'value' => $matches[2]
                ];
            } elseif (preg_match('/^([0-9\.]+)([a-z\%]{0,})[\s]+([0-9\.]+)([a-z\%]{0,})[\s]+([0-9\.]+)([a-z\%]{0,})[\s]+([0-9\.]+)([a-z\%]{0,})$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'border-top-left-radius',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'border_radius_1_measure',
                    'value' => $matches[2]
                ];
                $attr[] = [
                    'attribute' => 'border-top-right-radius',
                    'value' => $matches[3]
                ];
                $attr[] = [
                    'attribute' => 'border_radius_2_measure',
                    'value' => $matches[4]
                ];
                $attr[] = [
                    'attribute' => 'border-bottom-right-radius',
                    'value' => $matches[5]
                ];
                $attr[] = [
                    'attribute' => 'border_radius_3_measure',
                    'value' => $matches[6]
                ];
                $attr[] = [
                    'attribute' => 'border-bottom-left-radius',
                    'value' => $matches[7]
                ];
                $attr[] = [
                    'attribute' => 'border_radius_4_measure',
                    'value' => $matches[8]
                ];
            } else {
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => $value
                ];
            }
        } elseif ($attribute == 'border') {
            if ($value == 'inherit'){
                $attr[] = [
                    'attribute' => 'border',
                    'value' => 'inherit'
                ];
            } elseif ($value == 'none'){
                $attr[] = [
                    'attribute' => 'border',
                    'value' => 'none'
                ];
            } elseif ($value == 'hidden'){
                $attr[] = [
                    'attribute' => 'border',
                    'value' => 'hidden'
                ];
            } else {
                if (preg_match('/([0-9\.]+)([a-z\%]+)/', $value, $matches)) {
                    $attr[] = [
                        'attribute' => 'border-top-width',
                        'value' => $matches[1]
                    ];
                    $attr[] = [
                        'attribute' => 'border-left-width',
                        'value' => $matches[1]
                    ];
                    $attr[] = [
                        'attribute' => 'border-right-width',
                        'value' => $matches[1]
                    ];
                    $attr[] = [
                        'attribute' => 'border-bottom-width',
                        'value' => $matches[1]
                    ];
                    if ($matches[2] != 'px') {
                        $attr[] = [
                            'attribute' => 'border_top_width_measure',
                            'value' => $matches[2]
                        ];
                        $attr[] = [
                            'attribute' => 'border_left_width_measure',
                            'value' => $matches[2]
                        ];
                        $attr[] = [
                            'attribute' => 'border_right_width_measure',
                            'value' => $matches[2]
                        ];
                        $attr[] = [
                            'attribute' => 'border_bottom_width_measure',
                            'value' => $matches[2]
                        ];
                    }
                }

                $borderStyle = '';
                if (strpos($value, 'solid') !== false) {
                    $borderStyle = '';
                } elseif (strpos($value, 'dotted') !== false) {
                    $borderStyle = 'dotted';
                } elseif (strpos($value, 'dashed') !== false) {
                    $borderStyle = 'dashed';
                } elseif (strpos($value, 'double') !== false) {
                    $borderStyle = 'double';
                } elseif (strpos($value, 'groove') !== false) {
                    $borderStyle = 'groove';
                } elseif (strpos($value, 'ridge') !== false) {
                    $borderStyle = 'ridge';
                } elseif (strpos($value, 'inset') !== false) {
                    $borderStyle = 'inset';
                } elseif (strpos($value, 'outset') !== false) {
                    $borderStyle = 'outset';
                }
                if ($borderStyle) {
                    $attr[] = [
                        'attribute' => 'border-top-style',
                        'value' => $borderStyle
                    ];
                    $attr[] = [
                        'attribute' => 'border-left-style',
                        'value' => $borderStyle
                    ];
                    $attr[] = [
                        'attribute' => 'border-right-style',
                        'value' => $borderStyle
                    ];
                    $attr[] = [
                        'attribute' => 'border-bottom-style',
                        'value' => $borderStyle
                    ];
                }

                $borderColor = '';
                if (preg_match('/(rgb[a]{0,1}\([\-0-9\.\,\s]+\))/', $value, $matches)) {
                    $borderColor = $matches[1];
                } elseif (preg_match('/(\#[\-0-9a-fA-F]{3,6})/', $value, $matches)) {
                    $borderColor = $matches[1];
                } elseif (preg_match('/(\$[\-0-9a-z]+)/', $value, $matches)) {
                    $borderColor = $matches[1];
                }
                if ($borderColor) {
                    $attr[] = [
                        'attribute' => 'border-top-color',
                        'value' => $borderColor
                    ];
                    $attr[] = [
                        'attribute' => 'border-left-color',
                        'value' => $borderColor
                    ];
                    $attr[] = [
                        'attribute' => 'border-right-color',
                        'value' => $borderColor
                    ];
                    $attr[] = [
                        'attribute' => 'border-bottom-color',
                        'value' => $borderColor
                    ];
                }
            }
        } elseif (
            $attribute == 'border-top' ||
            $attribute == 'border-left' ||
            $attribute == 'border-right' ||
            $attribute == 'border-bottom'
        ) {
            if ($value == 'inherit'){
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => 'inherit'
                ];
            } elseif ($value == 'none'){
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => 'none'
                ];
            } elseif ($value == 'hidden'){
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => 'hidden'
                ];
            } else {
                if (preg_match('/([0-9\.]+)([a-z\%]{0,})/', $value, $matches)) {
                    $attr[] = [
                        'attribute' => $attribute . '-width',
                        'value' => $matches[1]
                    ];
                    if ($matches[2] != 'px') {
                        $attr[] = [
                            'attribute' => str_replace('-', '_', $attribute) . '_width_measure',
                            'value' => $matches[2]
                        ];
                    }
                }

                $borderStyle = '';
                if (strpos($value, 'solid') !== false) {
                    $borderStyle = '';
                } elseif (strpos($value, 'dotted') !== false) {
                    $borderStyle = 'dotted';
                } elseif (strpos($value, 'dashed') !== false) {
                    $borderStyle = 'dashed';
                } elseif (strpos($value, 'double') !== false) {
                    $borderStyle = 'double';
                } elseif (strpos($value, 'groove') !== false) {
                    $borderStyle = 'groove';
                } elseif (strpos($value, 'ridge') !== false) {
                    $borderStyle = 'ridge';
                } elseif (strpos($value, 'inset') !== false) {
                    $borderStyle = 'inset';
                } elseif (strpos($value, 'outset') !== false) {
                    $borderStyle = 'outset';
                }
                if ($borderStyle) {
                    $attr[] = [
                        'attribute' => $attribute . '-style',
                        'value' => $borderStyle
                    ];
                }

                $borderColor = '';
                if (preg_match('/(rgb[a]{0,1}\([\-0-9\.\,\s]+\))/', $value, $matches)) {
                    $borderColor = $matches[1];
                } elseif (preg_match('/(\#[\-0-9a-fA-F]{3,6})/', $value, $matches)) {
                    $borderColor = $matches[1];
                } elseif (preg_match('/(\$[\-0-9a-z]+)/', $value, $matches)) {
                    $borderColor = $matches[1];
                } elseif (strpos($value, 'transparent') !== false) {
                    $borderColor = 'transparent';
                }
                if ($borderColor) {
                    $attr[] = [
                        'attribute' => $attribute . '-color',
                        'value' => $borderColor
                    ];
                }
            }
        } elseif (
            $attribute == 'border-top-width' ||
            $attribute == 'border-left-width' ||
            $attribute == 'border-right-width' ||
            $attribute == 'border-bottom-width'
        ) {
            if (preg_match('/([0-9\.]+)([a-z\%]{0,})/', $value, $matches)) {
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => $matches[1]
                ];
                if ($matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => str_replace('-', '_', $attribute) . '_measure',
                        'value' => $matches[2]
                    ];
                }
            }
        } elseif ($attribute == 'border-color') {
            $borderColor = '';
            if (preg_match('/(rgb[a]{0,1}\([\-0-9\.\,\s]+\))/', $value, $matches)) {
                $borderColor = $matches[1];
            } elseif (preg_match('/(\#[\-0-9a-fA-F]{3,6})/', $value, $matches)) {
                $borderColor = $matches[1];
            } elseif (preg_match('/(\$[\-0-9a-z]+)/', $value, $matches)) {
                $borderColor = $matches[1];
            }
            if ($borderColor) {
                $attr[] = [
                    'attribute' => 'border-top-color',
                    'value' => $borderColor
                ];
                $attr[] = [
                    'attribute' => 'border-left-color',
                    'value' => $borderColor
                ];
                $attr[] = [
                    'attribute' => 'border-right-color',
                    'value' => $borderColor
                ];
                $attr[] = [
                    'attribute' => 'border-bottom-color',
                    'value' => $borderColor
                ];
            }
        } elseif ($attribute == 'border-style') {
            $borderStyle = '';
            if (strpos($value, 'solid') !== false) {
                $borderStyle = '';
            } elseif (strpos($value, 'dotted') !== false) {
                $borderStyle = 'dotted';
            } elseif (strpos($value, 'dashed') !== false) {
                $borderStyle = 'dashed';
            } elseif (strpos($value, 'double') !== false) {
                $borderStyle = 'double';
            } elseif (strpos($value, 'groove') !== false) {
                $borderStyle = 'groove';
            } elseif (strpos($value, 'ridge') !== false) {
                $borderStyle = 'ridge';
            } elseif (strpos($value, 'inset') !== false) {
                $borderStyle = 'inset';
            } elseif (strpos($value, 'outset') !== false) {
                $borderStyle = 'outset';
            } elseif (strpos($value, 'none') !== false) {
                $borderStyle = 'none';
            } elseif (strpos($value, 'inherit') !== false) {
                $borderStyle = 'inherit';
            }
            if ($borderStyle) {
                $attr[] = [
                    'attribute' => 'border-top-style',
                    'value' => $borderStyle
                ];
                $attr[] = [
                    'attribute' => 'border-left-style',
                    'value' => $borderStyle
                ];
                $attr[] = [
                    'attribute' => 'border-right-style',
                    'value' => $borderStyle
                ];
                $attr[] = [
                    'attribute' => 'border-bottom-style',
                    'value' => $borderStyle
                ];
            }
        } elseif ($attribute == 'border-width') {
            if (preg_match('/([0-9\.]+)([a-z\%]{0,})/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'border-top-width',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'border-left-width',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'border-right-width',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'border-bottom-width',
                    'value' => $matches[1]
                ];
                if ($matches[2] != 'px') {
                    $attr[] = [
                        'attribute' => 'border_top_width_measure',
                        'value' => $matches[2]
                    ];
                    $attr[] = [
                        'attribute' => 'border_left_width_measure',
                        'value' => $matches[2]
                    ];
                    $attr[] = [
                        'attribute' => 'border_right_width_measure',
                        'value' => $matches[2]
                    ];
                    $attr[] = [
                        'attribute' => 'border_bottom_width_measure',
                        'value' => $matches[2]
                    ];
                }
            }
        } elseif ($attribute == 'text-shadow') {
            if (preg_match('/^([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})[\s]+([0-9\.]+)([a-z\%]{0,})[\s]+([0-9a-zA-Z\(\)\,\s\#]+)$/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'text_shadow_left',
                    'value' => $matches[1]
                ];
                $attr[] = [
                    'attribute' => 'text_shadow_left_measure',
                    'value' => $matches[2]
                ];
                $attr[] = [
                    'attribute' => 'text_shadow_top',
                    'value' => $matches[3]
                ];
                $attr[] = [
                    'attribute' => 'text_shadow_top_measure',
                    'value' => $matches[4]
                ];
                $attr[] = [
                    'attribute' => 'text_shadow_size',
                    'value' => $matches[5]
                ];
                $attr[] = [
                    'attribute' => 'text_shadow_size_measure',
                    'value' => $matches[6]
                ];
                $attr[] = [
                    'attribute' => 'text_shadow_color',
                    'value' => $matches[7]
                ];
            } else {
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => $value
                ];
            }
        } elseif ($attribute == 'box-shadow') {
            if (preg_match('/^([inset]{0,})[\s]{0,}([\-0-9\.]+)([a-z\%]{0,})[\s]+([\-0-9\.]+)([a-z\%]{0,})[\s]+([0-9\.]+)([a-z\%]{0,})[\s]+([0-9\.]{0,})([a-z\%]{0,})[\s]{0,}([0-9a-zA-Z\(\)\,\s\#]+)$/', $value, $matches)) {
                if ($matches[1] == 'inset') {
                    $attr[] = [
                        'attribute' => 'box_shadow_set',
                        'value' => $matches[1]
                    ];
                }
                $attr[] = [
                    'attribute' => 'box_shadow_left',
                    'value' => $matches[2]
                ];
                $attr[] = [
                    'attribute' => 'box_shadow_left_measure',
                    'value' => $matches[3]
                ];
                $attr[] = [
                    'attribute' => 'box_shadow_top',
                    'value' => $matches[4]
                ];
                $attr[] = [
                    'attribute' => 'box_shadow_top_measure',
                    'value' => $matches[5]
                ];
                $attr[] = [
                    'attribute' => 'box_shadow_blur',
                    'value' => $matches[6]
                ];
                $attr[] = [
                    'attribute' => 'box_shadow_blur_measure',
                    'value' => $matches[7]
                ];
                if ($matches[8]) {
                    $attr[] = [
                        'attribute' => 'box_shadow_spread',
                        'value' => $matches[8]
                    ];
                    $attr[] = [
                        'attribute' => 'box_shadow_spread_measure',
                        'value' => $matches[9]
                    ];
                }
                $attr[] = [
                    'attribute' => 'box_shadow_color',
                    'value' => $matches[10]
                ];
            } else {
                $attr[] = [
                    'attribute' => $attribute,
                    'value' => $value
                ];
            }
        } elseif ($attribute == 'background') {
            if ($value == 'inherit'){
                $attr[] = [
                    'attribute' => 'background',
                    'value' => 'inherit'
                ];
            } elseif ($value == 'none'){
                $attr[] = [
                    'attribute' => 'background',
                    'value' => 'none'
                ];
            } elseif ($value == 'transparent'){
                $attr[] = [
                    'attribute' => 'background',
                    'value' => 'transparent'
                ];
            } elseif (strpos($value, 'gradient') !== false){
                $default = true;
            } else {
                if (strpos($value, 'fixed') !== false) {
                    $attr[] = [
                        'attribute' => 'background-attachment',
                        'value' => 'fixed'
                    ];
                } elseif (strpos($value, 'scroll') !== false) {
                    $attr[] = [
                        'attribute' => 'background-attachment',
                        'value' => 'scroll'
                    ];
                } elseif (strpos($value, 'local') !== false) {
                    $attr[] = [
                        'attribute' => 'background-attachment',
                        'value' => 'local'
                    ];
                }

                if (strpos($value, 'no-repeat') !== false) {
                    $attr[] = [
                        'attribute' => 'background-repeat',
                        'value' => 'no-repeat'
                    ];
                } elseif (strpos($value, 'repeat') !== false) {
                    $attr[] = [
                        'attribute' => 'background-repeat',
                        'value' => 'repeat'
                    ];
                } elseif (strpos($value, 'repeat-x') !== false) {
                    $attr[] = [
                        'attribute' => 'background-repeat',
                        'value' => 'repeat-x'
                    ];
                } elseif (strpos($value, 'repeat-y') !== false) {
                    $attr[] = [
                        'attribute' => 'background-repeat',
                        'value' => 'repeat-y'
                    ];
                }

                $horizontal = '';
                $vertical = '';
                if (strpos($value, 'left') !== false) {
                    $horizontal = 'left';
                } elseif (strpos($value, 'center') !== false) {
                    $horizontal = 'center';
                } elseif (strpos($value, 'right') !== false) {
                    $horizontal = 'right';
                }
                if (strpos($value, 'top') !== false) {
                    $vertical = 'top';
                } elseif (strpos($value, 'bottom') !== false) {
                    $vertical = 'bottom';
                }
                if ($horizontal && $vertical) {
                    $attr[] = [
                        'attribute' => 'background-position',
                        'value' => $vertical . ' ' . $horizontal
                    ];
                } elseif ($horizontal || $vertical) {
                    $attr[] = [
                        'attribute' => 'background-position',
                        'value' => $vertical . $horizontal
                    ];
                } elseif (preg_match('/[\s]+([\-0-9\.]+[a-z\%]+[\s]+[\-0-9\.]+[a-z\%]+)/', $value, $matches)) {
                    $attr[] = [
                        'attribute' => 'background-position',
                        'value' => $matches[1]
                    ];
                }

                if (preg_match('/url\([\'\"](.+)[\'\"]\)/', $value, $matches)) {
                    $attr[] = [
                        'attribute' => 'background_image',
                        'value' => $matches[1]
                    ];
                }

                if (preg_match('/(rgb[a]{0,1}\([\-0-9\.\,\s]+\))/', $value, $matches)) {
                    $attr[] = [
                        'attribute' => 'background-color',
                        'value' => $matches[1]
                    ];
                } elseif (preg_match('/(\#[\-0-9a-fA-F]{3,6})/', $value, $matches)) {
                    $attr[] = [
                        'attribute' => 'background-color',
                        'value' => $matches[1]
                    ];
                } elseif (preg_match('/(\$[\-0-9a-z]+)/', $value, $matches)) {
                    $attr[] = [
                        'attribute' => 'background-color',
                        'value' => $matches[1]
                    ];
                }

            }
        } elseif ($attribute == 'background-image') {
            if (preg_match('/url\([\'\"](.+)[\'\"]\)/', $value, $matches)) {
                $attr[] = [
                    'attribute' => 'background_image',
                    'value' => $matches[1]
                ];
            } else {
                $default = true;
            }
        } else {
            $default = true;
        }

        if ($default) {
            $attr[] = [
                'attribute' => $attribute,
                'value' => $value
            ];
        }

        $attrTmp = [];
        if ($importantAttr) {
            foreach ($attr as $attrItem) {
                $attrTmp[] = [
                    'attribute' => $attrItem['attribute'] . '_important',
                    'value' => 'important'
                ];
            }
        }
        $attr = array_merge($attr, $attrTmp);

        return $attr;
    }

    public static function explodeByAccessibility($css)
    {
        $areaExplodeArr = explode('@area', $css);
        $counter = 0;
        $areaBlocks = array();
        foreach ($areaExplodeArr as $areaExplode) {
            if ($counter == 0){
                $areaBlocks[''] = $areaExplode;
            } else {
                $first = stripos($areaExplode, '{');
                $areaName = trim(substr($areaExplode, 0, $first));

                $last = strrpos($areaExplode, '}');

                $areaBlocks[$areaName] = substr($areaExplode, $first+1, $last-$first-1);
            }
            $counter++;

        }

        return $areaBlocks;
    }

    public static function explodeByMediaBlocks($css, $theme_name)
    {
        $noMedia = '';
        $mediaExplodeArr = explode('@media', $css);
        $counter = 0;
        $visibilityBlock = array();
        $mediaBlock = array();
        foreach ($mediaExplodeArr as $mediaExplode) {
            if ($counter == 0){
                $noMedia .= $mediaExplode;
            } else {
                $visibility = 0;
                $first = stripos($mediaExplode, '{');
                $mediaName = trim(substr($mediaExplode, 0, $first));
                if (preg_match('/^\(min\-width\:[\s]{0,}([0-9]+)px\)[\s]{0,}and[\s]{0,}\(max\-width\:[\s]{0,}([0-9]+)px\)$/', $mediaName, $matches)) {
                    $visibility = $matches[1] . 'w' . $matches[2];
                } elseif (preg_match('/^\(max\-width\:[\s]{0,}([0-9]+)px\)$/', $mediaName, $matches)) {
                    $visibility = 'w' . $matches[1];
                } elseif (preg_match('/^\(min\-width\:[\s]{0,}([0-9]+)px\)$/', $mediaName, $matches)) {
                    $visibility = $matches[1] . 'w';
                }
                if ($visibility) {
                    $vidAr = tep_db_fetch_array(tep_db_query("select id from " . TABLE_THEMES_SETTINGS . " where
                    theme_name = '" . tep_db_input($theme_name) . "' and
                    setting_group = 'extend' and
                    setting_name = 'media_query' and
                    setting_value = '" . tep_db_input($visibility) . "'
                    "));
                    if (!$vidAr) {
                        tep_db_perform(TABLE_THEMES_SETTINGS, array(
                            'theme_name' => $theme_name,
                            'setting_group' => 'extend',
                            'setting_name' => 'media_query',
                            'setting_value' => $visibility
                        ));
                        $vid = tep_db_insert_id();
                    } else {
                        $vid = $vidAr['id'];
                    }
                }

                $mediaExplodeTmp = preg_split('/\}[\s\n]+\}/', $mediaExplode);
                $noMedia .= $mediaExplodeTmp[1];

                $block = trim(substr($mediaExplodeTmp[0], $first+1)) . '}';
                
                if ($visibility) {
                    $visibilityBlock[$vid] = $block;
                } else {
                    $mediaBlock[$mediaName] = $block;
                }
            }
            $counter++;

        }

        return [
            'visibility' => $visibilityBlock,
            'media' => $mediaBlock,
            'no_media' => $noMedia,
        ];
    }

    public static function getCss($theme_name, $widgets = array(), $page = '', $all = true, $cachedAccessibility = null )
    {
        $css = '';
        $tab = '  ';
        $displacement = '';
        $byMedia = array();
        $areaArr = array();
        if (!is_array($widgets) && !$widgets) {
            $widgets = array();
        } elseif (is_string($widgets) && $widgets) {
            $widgets = array($widgets);
        }

        if ($all && $page) {
            $areaArr[] = '';
            $areaArr[] = $page;
            foreach ($widgets as $widget) {
                $areaArr[] = $widget;
            }
            foreach ($widgets as $widget) {
                $areaArr[] = $page . ' ' . $widget;
            }
        } elseif (count($widgets) > 0 && $page) {
            foreach ($widgets as $widget) {
                $areaArr[] = $page . ' ' . $widget;
            }
        } elseif ($page) {
            $areaArr[] = $page;
        } elseif (count($widgets) > 0) {
            foreach ($widgets as $widget) {
                $areaArr[] = $widget;
            }
        }

        $mainStyles = [];
        if (Yii::$app->controller->action->id != 'get-css') {
            $mainStyles = self::mainStyles($theme_name);
        }
        if (count($areaArr) == 1) {

            if ($cachedAccessibility) {
                $reader = $cachedAccessibility;
            } else {
                static $cmd = null; // unfortunately prepared queries has not enought effect
                if (is_null($cmd)) {
                    $cmd = \Yii::$app->db->createCommand("select * from " . TABLE_THEMES_STYLES . " where theme_name = :theme and accessibility = :area order by accessibility, media, selector, attribute, visibility");
                }
                $reader = $cmd->bindValues([':theme' => tep_db_input($theme_name), ':area' => reset($areaArr)])->query();
            }
        } else { // it should not happen, but just in case
            $reader = \common\models\ThemesStyles::find()->where(['theme_name' => $theme_name])->orderBy('accessibility, media, selector, attribute, visibility')->asArray();
            if (count($areaArr) > 0) {
                $reader = $reader->andWhere(['accessibility' => $areaArr]);
            }
            $reader = $reader->each();
        }

        foreach($reader as $item) {
            $vArr = self::vArr($item['visibility']);
            $visibility = '';
            foreach ($vArr as $vKey => $vItem) {
                if ($vItem > 10) {
                    $visibility = $vItem;
                    unset($vArr[$vKey]);
                }
            }
            if (
                self::$cssFrontend &&
                $item['accessibility'] &&
                (strpos($item['accessibility'], '.b-') === 0 || strpos($item['accessibility'], '.s-') === 0 ) &&
                strpos($item['selector'], $item['accessibility']) !== false
            ) {
                $item['selector'] = trim(str_replace($item['accessibility'], '', $item['selector']));
            }
            if (count($vArr) > 0) {
                $selectorArr = explode(',', $item['selector']);
                foreach ($selectorArr as $sItem => $class) {
                    if (in_array(2, $vArr)){
                        $selectorArr[$sItem] .= '.active';
                    }
                    if (in_array(3, $vArr)){
                        $selectorArr[$sItem] .= ':before';
                    }
                    if (in_array(4, $vArr)){
                        $selectorArr[$sItem] .= ':after';
                    }
                    if (in_array(1, $vArr)){
                        $selectorArr[$sItem] .= ':hover';
                    }
                }
                $item['selector'] = implode(', ', $selectorArr);
            }

            if (isset($item['value']) && isset($mainStyles[$item['value']])) {
                $item['value'] = $mainStyles[$item['value']];
            }

            if ($visibility) {
                $byMedia['visibility'][$visibility][$item['selector']][$item['attribute']] = $item['value'];
            } elseif ($item['media']) {
                $byMedia['media'][$item['media']][$item['selector']][$item['attribute']] = $item['value'];
            } else {
                $byMedia['general'][$item['selector']][$item['attribute']] = $item['value'];
            }
        }


        if (!self::$cssFrontend && (count($widgets) == 0 || $widgets[0] == 'block_box')) {
            $boxes = tep_db_query("
            select bs.box_id, bs.setting_value, bs.visibility, bs.setting_name
            from " . TABLE_DESIGN_BOXES_TMP . " b, " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " bs 
            where 
                b.theme_name = '" . tep_db_input($theme_name) . "' and
                b.id = bs.box_id
            ");
            while ($item = tep_db_fetch_array($boxes)) {
                if (
                    !in_array($item['setting_name'], self::$attributesHaveRules) &&
                    !in_array($item['setting_name'], self::$attributesNoRules) &&
                    !in_array($item['setting_name'], self::$attributesHasMeasure)
                ) {
                    continue;
                }
                $vArr = self::vArr($item['visibility']);
                $visibility = '';
                foreach ($vArr as $vKey => $vItem) {
                    if ($vItem > 10) {
                        $visibility = $vItem;
                        unset($vArr[$vKey]);
                    }
                }
                $selector = '#box-' . $item['box_id'];
                if (count($vArr) > 0) {
                    $selectorArr = explode(',', $selector);
                    foreach ($selectorArr as $sItem => $class) {
                        if (in_array(2, $vArr)) {
                            $selectorArr[$sItem] .= '.active';
                        }
                        if (in_array(3, $vArr)) {
                            $selectorArr[$sItem] .= ':before';
                        }
                        if (in_array(4, $vArr)) {
                            $selectorArr[$sItem] .= ':after';
                        }
                        if (in_array(1, $vArr)) {
                            $selectorArr[$sItem] .= ':hover';
                        }
                    }
                    $selector = implode(', ', $selectorArr);
                }

                if ($visibility) {
                    $byMedia['visibility'][$visibility][$selector][$item['setting_name']] = $item['setting_value'];
                } else {
                    $byMedia['general'][$selector][$item['setting_name']] = $item['setting_value'];
                }
            }
        }


        $cssArr = [
            'general' => '',
            'visibility' => '',
            'media' => ''
        ];

        foreach ($byMedia as $key => $item) {
            if ($key == 'general') {
                $cssArr['general'] = $cssArr['general'] . self::getCssMedia($item, '', $tab, $displacement);
            } elseif ($key == 'visibility') {

                static $cachedMediaSizes = [];
                if (!is_array($mediaSizes[tep_db_input($theme_name)]??null)) {
                    $mediaSizes = [];
                    $mediaSizesQuery = tep_db_query("select id, setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'extend' and setting_name = 'media_query'");

                    while ($mediaSize = tep_db_fetch_array($mediaSizesQuery)) {
                        $arr2 = explode('w', $mediaSize['setting_value']);
                        if (isset($arr2[0]) && $arr2[0]) {
                            $mediaSizes[(int)($arr2[0] . '0')] = $mediaSize['id'];
                        }
                        if (isset($arr2[1]) && $arr2[1]) {
                            $mediaSizes[(int)$arr2[1]] = $mediaSize['id'];
                        }
                    }
                    krsort($mediaSizes);
                    $cachedMediaSizes[tep_db_input($theme_name)] = $mediaSizes;
                } else {
                    $mediaSizes = $cachedMediaSizes[tep_db_input($theme_name)];
                }

                foreach ($mediaSizes as $media) {
                    $arr = $item[$media] ?? null;
                    $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where id = '" . $media . "'"));
                    $arr2 = explode('w', $query['setting_value']);
                    $media = '';
                    if (isset($arr2[0]) && $arr2[0]){
                        $media .= '(min-width:' . $arr2[0] . 'px)';
                    }
                    if (isset($arr2[0]) && $arr2[0] && isset($arr2[1]) && $arr2[1]){
                        $media .= ' and ';
                    }
                    if (isset($arr2[1]) && $arr2[1]){
                        $media .= '(max-width:' . $arr2[1] . 'px)';
                    }
                    $cssArr['visibility'] = $cssArr['visibility'] . self::getCssMedia($arr, $media, $tab, $displacement);
                }


            } elseif ($key == 'media') {
                foreach ($item as $media => $arr) {
                    $cssArr['media'] = $cssArr['media'] . self::getCssMedia($arr, $media, $tab, $displacement);
                }
            }
        }

        $css .= $cssArr['general'];
        $css .= $cssArr['visibility'];
        $css .= $cssArr['media'];

        return $css;
    }

    public static function getCssMedia($arr, $media, $tab, $displacement)
    {
        if (!is_array($arr)) {
            return '';
        }
        $br = "\n";
        if (self::$cssFrontend) {
            //$displacement = '';
            //$br = '';
        }
        $css = '';
        if ($media) {
            $css .= $displacement . '@media ' . $media . ' {' . $br;
            $displacement = $displacement . $tab;
        }

        foreach ($arr as $key => $item) {
            $css .= $displacement . $key . ' {' . $br;
            $displacement = $displacement . $tab;
            $css .= self::getAttributes($item, $displacement, $br, true);
            $displacement = substr ($displacement, strlen($tab));
            $css .= $displacement . '}' . $br;
        }

        if ($media) {
            $displacement = substr ($displacement, strlen($tab));
            $css .= $displacement . '}' . $br;
        }

        return $css;
    }

    public static $attributesHaveRules = ['rotate', 'content', 'display', 'left_measure', 'right_measure', 'width_measure', 'min_width_measure', 'max_width_measure', 'height_measure', 'min_height_measure', 'max_height_measure', 'p_width', 'font-family', 'font_size_measure', 'line_height_measure', 'text_shadow_left', 'text_shadow_left_measure', 'text_shadow_top', 'text_shadow_top_measure', 'text_shadow_size', 'text_shadow_size_measure', 'text_shadow_color', 'box_shadow_blur', 'box_shadow_blur_measure', 'box_shadow_spread', 'box_shadow_spread_measure', 'box_shadow_color', 'box_shadow_left', 'box_shadow_left_measure', 'box_shadow_top', 'box_shadow_top_measure', 'box_shadow_set', 'background_image', 'padding-top', 'padding-left', 'padding-right', 'padding-bottom', 'padding_top_measure',  'padding_left_measure', 'padding_right_measure', 'padding_bottom_measure', 'margin-top', 'margin-left', 'margin-right', 'margin-bottom', 'margin_top_measure', 'margin_left_measure', 'margin_right_measure', 'margin_bottom_measure', 'border-top-width', 'border_top_width_measure', 'border-top-color', 'border-top-style', 'border-left-width', 'border_left_width_measure', 'border-left-color', 'border-left-style', 'border-right-width', 'border_right_width_measure', 'border-right-style', 'border-right-color', 'border-bottom-width', 'border_bottom_width_measure', 'border-bottom-style', 'border-bottom-color', 'border-top-left-radius', 'border-top-right-radius', 'border-bottom-right-radius', 'border-bottom-left-radius', 'border_radius_1_measure', 'border_radius_2_measure', 'border_radius_3_measure', 'border_radius_4_measure', 'display_none', 'box_align', 'line-height'
];//these attributes have rules

    public static $attributesNoRules = ['animation-delay', 'background', 'background-attachment', 'background-clip', 'background-color',  'background-origin', 'background-position', 'background-position-x', 'background-position-y', 'background-repeat', 'background-size', 'border', 'border-bottom', 'border-collapse', 'border-color', 'border-image', 'border-left', 'border-radius', 'border-right', 'border-spacing', 'border-style', 'border-top', 'border-width', 'box-shadow', 'box-sizing', 'caption-side', 'clear', 'clip', 'color', 'column-count', 'column-gap', 'column-rule', 'column-width', 'columns', 'counter-increment', 'counter-reset', 'cursor', 'direction', 'empty-cells', 'filter', 'float', 'font', 'font-stretch', 'font-style', 'font-variant', 'font-weight', 'hasLayout', 'hyphens', 'image-rendering', 'letter-spacing', 'list-style', 'list-style-image', 'list-style-position', 'list-style-type', 'opacity', 'orphans', 'outline', 'outline-color', 'outline-offset', 'outline-style', 'outline-width', 'overflow', 'overflow-x', 'overflow-y', 'page-break-after', 'page-break-before', 'page-break-inside', 'position', 'quotes', 'resize', 'scrollbar-3dlight-color', 'scrollbar-arrow-color', 'scrollbar-base-color', 'scrollbar-darkshadow-color', 'scrollbar-face-color', 'scrollbar-highlight-color', 'scrollbar-shadow-color', 'scrollbar-track-color', 'tab-size', 'table-layout', 'text-align', 'text-align-last', 'text-decoration', 'text-decoration-color', 'text-decoration-line', 'text-decoration-style', 'text-indent', 'text-overflow', 'text-shadow', 'text-transform', 'transform', 'transform-origin', 'transform-style', 'transition', 'transition-delay', 'transition-property', 'transition-timing-function', 'unicode-bidi', 'vertical-align', 'visibility', 'white-space', 'widows', 'word-break', 'word-spacing', 'word-wrap', 'writing-mode', 'z-index', 'zoom', 'flex-direction', 'flex-wrap', 'flex-flow', 'justify-content', 'align-items', 'align-content', 'margin', 'padding'];

    public static $attributesHasMeasure = ['top', 'left', 'right', 'bottom', 'width', 'min-width', 'max-width', 'height', 'min-height', 'max-height', 'font-size'];

    public static function getAttributes($attributes, $displacement = '', $br = '', $anyAttributes = false)
    {
        if (self::$cssFrontend) {
            //$displacement = '';
            //$br = '';
        }
        $style = '';
        $attributesHaveRules = self::$attributesHaveRules;

        $attributesNoRules = self::$attributesNoRules;

        $attributesHasMeasure = self::$attributesHasMeasure;

        $importantArr = [];
        if (is_array($attributes)) {
            foreach ($attributes as $attr => $val) {
                if (isset($val)) {
                    if ($val == 'important') {
                        $importantArr[str_replace('_important', '', $attr)] = '!important';
                        unset($attributes[$attr]);
                    }
                }
            }
            foreach ($attributes as $attr => $val) {
                if (isset($val)) {
                    if (in_array($attr, $attributesNoRules)) {

                        $style .= $displacement . $attr . ': ' . $val . (isset($importantArr[$attr]) ? $importantArr[$attr] : '') . ';' . $br;

                    } elseif (in_array($attr, $attributesHasMeasure)) {

                        $style .= $displacement . $attr . ':' . $val . ($val == '0' ? '' : self::dimension(@$attributes[str_replace('-', '_', $attr) . '_measure'], '', $val)) . (isset($importantArr[$attr]) ? $importantArr[$attr] : '') . ';' . $br;

                    } elseif (
                        $anyAttributes &&
                        !in_array($attr, $attributesHaveRules) &&
                        !in_array($attr, $attributesNoRules) &&
                        !in_array($attr, $attributesHasMeasure) &&
                        !strpos($attr, '_measure') !== false
                    ) {

                        $style .= $displacement . $attr . ': ' . $val . ArrayHelper::getValue($importantArr, $attr) . ';' . $br;

                    }
                }
            }
        }

        if (isset($attributes['display']) && $attributes['display'] == 'flex' && !$displacement) {
            $style .= 'display:-ms-flexbox;';
        }
        if (isset($attributes['flex-grow']) && !$displacement) {
            $style .= '-ms-flex:' . $attributes['flex-grow'] . ';';
        }
        if (isset($attributes['align-items']) && !$displacement) {
            if ($attributes['align-items'] == 'flex-start') $flexAttr = 'start';
            elseif ($attributes['align-items'] == 'flex-end') $flexAttr = 'end';
            else $flexAttr = $attributes['align-items'];
            $style .= '-ms-flex-align:' . $flexAttr . ';';
        }
        if (isset($attributes['line-height'])) {
            $style .= $displacement . 'line-height:' . $attributes['line-height'] . (isset($attributes['line_height_measure']) ? self::dimension($attributes['line_height_measure']) : '') . ';' . $br;
        }
        if (isset($attributes['rotate'])) {
            $style .= $displacement . '-ms-transform:rotate(' . $attributes['rotate'] . 'deg);-webkit-transform:rotate(' . $attributes['rotate'] . 'deg);transform:rotate(' . $attributes['rotate'] . 'deg)' . ArrayHelper::getValue($importantArr, 'rotate') . ';' . $br;
        }
        if (isset($attributes['content']) && $attributes['content']) {
            $style .= $displacement . 'content:\'' . ($attributes['content'] == '\_' ? '' : $attributes['content']) . '\'' . (isset($importantArr['content']) ? $importantArr['content'] : '') . ';' . $br;
        }
        if (isset($attributes['display']) && $attributes['display']) {
            $style .= $displacement . 'display:' . $attributes['display'] . (isset($importantArr['display']) ? $importantArr['display'] : '') . ';' . $br;
            /*if ($attributes['display'] == 'none' && \frontend\design\Info::isAdmin()) {
                $style .= $displacement . 'opacity: 0.2;' . $br;
            } else {
                $style .= $displacement . 'display:' . $attributes['display'] . ';' . $br;
            }*/
        }

        $importantArr['font-family'] = $importantArr['font-family'] ?? null;
        if (isset($attributes['p_width'])) {
            $style .= $displacement . 'width:' . ($attributes['p_width'] - $attributes['padding-left'] - $attributes['padding-right'] - $attributes['border-left-width'] - $attributes['border-right-width']) . 'px;' . $br;
        }
        $to_pdf = 0;
        if (method_exists(Yii::$app->request, 'get')) {
            $to_pdf = (int)Yii::$app->request->get('to_pdf', 0);
        }
        if (isset($attributes['font-family']) && !$to_pdf){
            if (Yii::$app->controller->action->id == 'get-css' || stripos($attributes['font-family'], "'") !== false || stripos($attributes['font-family'], '"') !== false) {
                $style .= $displacement . 'font-family:' . $attributes['font-family'] . '' . $importantArr['font-family'] . ';' . $br;
            } else {
                if ($attributes['font-family'] == 'inherit') {
                    $style .= $displacement . 'font-family:inherit' . $importantArr['font-family'] . ';' . $br;
                } else {
                    $style .= $displacement . 'font-family:\'' . $attributes['font-family'] . '\', Verdana, Arial, sans-serif' . (isset($importantArr['font-family']) ? $importantArr['font-family'] : '') . ';' . $br;
                }
            }
        }
        if (
            isset($attributes['text_shadow_left']) ||
            isset($attributes['text_shadow_top']) ||
            isset($attributes['text_shadow_size']) ||
            (isset($attributes['text_shadow_color']) && $attributes['text_shadow_color'])
        ){
            $text_shadow_left = $attributes['text_shadow_left'];
            $text_shadow_top = $attributes['text_shadow_top'];
            $text_shadow_size = $attributes['text_shadow_size'];
            $text_shadow_color = $attributes['text_shadow_color'];
            if ($text_shadow_left) $text_shadow_left .= 'px';
            else $text_shadow_left = '0';
            if ($text_shadow_top) $text_shadow_top .= 'px';
            else $text_shadow_top = '0';
            if ($text_shadow_size) $text_shadow_size .= 'px';
            else $text_shadow_size = '0';
            if ($text_shadow_size && $text_shadow_color){
                $style .= $displacement . 'text-shadow:' . $text_shadow_left.' '.$text_shadow_top.' '.$text_shadow_size.' '.$text_shadow_color . ArrayHelper::getValue($importantArr, 'text-shadow') . ';' . $br;
            }
        }
        if (
            (isset($attributes['box_shadow_blur']) ||
                isset($attributes['box_shadow_spread'])) &&
            $attributes['box_shadow_color']
        ){
            $box_shadow_left = $attributes['box_shadow_left'] ?? null;
            $box_shadow_top = $attributes['box_shadow_top'] ?? null;
            $box_shadow_blur = $attributes['box_shadow_blur'] ?? null;
            $box_shadow_spread = $attributes['box_shadow_spread'] ?? null;
            if ($box_shadow_left) $box_shadow_left .= 'px';
            else $box_shadow_left = '0';
            if ($box_shadow_top) $box_shadow_top .= 'px';
            else $box_shadow_top = '0';
            if ($box_shadow_blur) $box_shadow_blur .= 'px';
            else $box_shadow_blur = '0';
            if ($box_shadow_spread) $box_shadow_spread .= 'px';
            else $box_shadow_spread = '0';
            $style .= $displacement . 'box-shadow:' . ArrayHelper::getValue($attributes, 'box_shadow_set').' ' . $box_shadow_left.' '.
                $box_shadow_top.' '.$box_shadow_blur.' '.$box_shadow_spread.' '.$attributes['box_shadow_color'] . ArrayHelper::getValue($importantArr, 'box-shadow') . ';' . $br;
        }
        if (isset($attributes['background_image']) && $attributes['background_image']){
            $style .= $displacement . 'background-image:url(\'' . \frontend\design\Info::themeImage($attributes['background_image']) . '\')' . ArrayHelper::getValue($importantArr, 'background-image') . ';' . $br;
        }

        $borderTop = '';
        $borderLeft = '';
        $borderRight = '';
        $borderBottom = '';
        $attributes['border_left_width_measure'] = $attributes['border_left_width_measure'] ?? null;
        $attributes['border_top_width_measure'] = $attributes['border_top_width_measure'] ?? null;
        $importantArr['border-top-width'] = $importantArr['border-top-width'] ?? null;
        if (isset($attributes['border-top-width']) && ArrayHelper::getValue($attributes, 'border-top-color')) {
            $borderTop =
                $attributes['border-top-width'] .
                self::dimension(@$attributes['border_top_width_measure']) .
                (isset($attributes['border-top-style']) && !empty($attributes['border-top-style']) ? ' ' . $attributes['border-top-style'] . ' ' : ' solid ') .
                $attributes['border-top-color'];
        } else {
            if (isset($attributes['border-top-width'])) {
                $style .= $displacement . 'border-top-width:' . $attributes['border-top-width'] . self::dimension($attributes['border_top_width_measure']) . $importantArr['border-top-width'] . ';' . $br;
            }
            if (isset($attributes['border-top-color']) && $attributes['border-top-color']) {
                $style .= $displacement . 'border-top-color:' . $attributes['border-top-color'] . ArrayHelper::getValue($importantArr, 'border-top-color') . ';' . $br;
            }
            if (isset($attributes['border-top-style']) && $attributes['border-top-style']) {
                $style .= $displacement . 'border-top-style:' . $attributes['border-top-style'] . $importantArr['border-top-style'] . ';' . $br;
            }
        }
        if (isset($attributes['border-left-width']) && isset($attributes['border-left-color']) && $attributes['border-left-color']) {
            $borderLeft =
                $attributes['border-left-width'] .
                self::dimension(@$attributes['border_left_width_measure']) .
                (isset($attributes['border-left-style']) && !empty($attributes['border-left-style']) ? ' ' . $attributes['border-left-style'] . ' ' : ' solid ') .
                $attributes['border-left-color'];
        } else {
            if (isset($attributes['border-left-width'])) {
                $style .= $displacement . 'border-left-width:' . $attributes['border-left-width'] . self::dimension(@$attributes['border_left_width_measure']) . ($importantArr['border-left-width'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-left-color']) && $attributes['border-left-color']) {
                $style .= $displacement . 'border-left-color:' . $attributes['border-left-color'] . ($importantArr['border-left-color'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-left-style']) && $attributes['border-left-style']) {
                $style .= $displacement . 'border-left-style:' . $attributes['border-left-style'] . ($importantArr['border-left-style'] ?? '') . ';' . $br;
            }
        }
        if (isset($attributes['border-right-width']) && isset($attributes['border-right-color'])) {
            $borderRight =
                $attributes['border-right-width'] .
                self::dimension(@$attributes['border_right_width_measure']) .
                (isset($attributes['border-right-style']) && !empty($attributes['border-right-style']) ? ' ' . $attributes['border-right-style'] . ' ' : ' solid ') .
                $attributes['border-right-color'];
        } else {
            if (isset($attributes['border-right-width'])) {
                $style .= $displacement . 'border-right-width:' . $attributes['border-right-width'] . self::dimension(@$attributes['border_right_width_measure']) . ($importantArr['border-right-width'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-right-color']) && $attributes['border-right-color']) {
                $style .= $displacement . 'border-right-color:' . $attributes['border-right-color'] . ($importantArr['border-right-color'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-right-style']) && $attributes['border-right-style']) {
                $style .= $displacement . 'border-right-style:' . $attributes['border-right-style'] . ($importantArr['border-right-style'] ?? '') . ';' . $br;
            }
        }
        $attributes['border-bottom-color'] = $attributes['border-bottom-color'] ?? null;
        if (isset($attributes['border-bottom-width']) && $attributes['border-bottom-color']) {
            $borderBottom =
                $attributes['border-bottom-width'] .
                self::dimension(@$attributes['border_bottom_width_measure']) .
                (isset($attributes['border-bottom-style']) && !empty($attributes['border-bottom-style']) ? ' ' . $attributes['border-bottom-style'] . ' ' : ' solid ') .
                $attributes['border-bottom-color'];
        } else {
            if (isset($attributes['border-bottom-width'])) {
                $style .= $displacement . 'border-bottom-width:' . $attributes['border-bottom-width'] . self::dimension(@$attributes['border_bottom_width_measure']) . ($importantArr['border-bottom-width'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-bottom-color']) && $attributes['border-bottom-color']) {
                $style .= $displacement . 'border-bottom-color:' . $attributes['border-bottom-color'] . ($importantArr['border-bottom-color'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-bottom-style']) && $attributes['border-bottom-style']) {
                $style .= $displacement . 'border-bottom-style:' . $attributes['border-bottom-style'] . ($importantArr['border-bottom-style'] ?? '') . ';' . $br;
            }
        }
        if (
            $borderTop &&
            $borderTop == $borderLeft &&
            $borderTop == $borderRight &&
            $borderTop == $borderBottom
        ) {
            $style .= $displacement . 'border:' . $borderTop . self::cssImportant($importantArr, 'border') . ';' . $br;
        } else {
            if ($borderTop) {
                $style .= $displacement . 'border-top:' . $borderTop . ($importantArr['border-top'] ?? '') . ';' . $br;
            }
            if ($borderLeft) {
                $style .= $displacement . 'border-left:' . $borderLeft . ($importantArr['border-left'] ?? '') . ';' . $br;
            }
            if ($borderRight) {
                $style .= $displacement . 'border-right:' . $borderRight . ($importantArr['border-right'] ?? '') . ';' . $br;
            }
            if ($borderBottom) {
                $style .= $displacement . 'border-bottom:' . $borderBottom . ($importantArr['border-bottom'] ?? '') . ';' . $br;
            }
        }


        $attributes['border_radius_1_measure'] = $attributes['border_radius_1_measure'] ?? null;
        $attributes['border_radius_2_measure'] = $attributes['border_radius_2_measure'] ?? null;
        $attributes['border_radius_3_measure'] = $attributes['border_radius_3_measure'] ?? null;
        $attributes['border_radius_4_measure'] = $attributes['border_radius_4_measure'] ?? null;
        $importantArr['border-radius'] = $importantArr['border-radius'] ?? null;
        if (
            isset($attributes['border-top-left-radius']) &&
            isset($attributes['border-top-right-radius']) &&
            isset($attributes['border-bottom-right-radius']) &&
            isset($attributes['border-bottom-left-radius'])
        ) {
            $style .= $displacement . 'border-radius:' .
                $attributes['border-top-left-radius'] . self::dimension($attributes['border_radius_1_measure']) . ' ' .
                $attributes['border-top-right-radius'] . self::dimension($attributes['border_radius_2_measure']) . ' ' .
                $attributes['border-bottom-right-radius'] . self::dimension($attributes['border_radius_3_measure']) . ' ' .
                $attributes['border-bottom-left-radius'] . self::dimension($attributes['border_radius_4_measure']) . $importantArr['border-radius'] . ';' . $br;
        } else {
            if (isset($attributes['border-top-left-radius'])) {
                $style .= $displacement . 'border-top-left-radius:' . $attributes['border-top-left-radius'] . self::dimension($attributes['border_radius_1_measure']) . ($importantArr['border-top-left-radius'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-top-right-radius'])) {
                $style .= $displacement . 'border-top-right-radius:' . $attributes['border-top-right-radius'] . self::dimension($attributes['border_radius_2_measure']) . ($importantArr['border-top-right-radius'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-bottom-right-radius'])) {
                $style .= $displacement . 'border-bottom-right-radius:' . $attributes['border-bottom-right-radius'] . self::dimension($attributes['border_radius_3_measure']) . ($importantArr['border-bottom-right-radius'] ?? '') . ';' . $br;
            }
            if (isset($attributes['border-bottom-left-radius'])) {
                $style .= $displacement . 'border-bottom-left-radius:' . $attributes['border-bottom-left-radius'] . self::dimension($attributes['border_radius_4_measure']) . ($importantArr['border-bottom-left-radius'] ?? '') . ';' . $br;
            }
        }

        if (
            isset($attributes['padding-top']) &&
            isset($attributes['padding-left']) &&
            isset($attributes['padding-right']) &&
            isset($attributes['padding-bottom'])
        ) {
            if (
                $attributes['padding-top'] == $attributes['padding-bottom'] &&
                $attributes['padding-left'] == $attributes['padding-right']
            ) {
                $style .= $displacement . 'padding:' .
                    $attributes['padding-top'] . self::dimension(@$attributes['padding_top_measure']) . ' ' .
                    $attributes['padding-right'] . self::dimension(@$attributes['padding_right_measure']) . self::cssImportant($importantArr, 'padding') . ';' . $br;
            } elseif ($attributes['padding-left'] == $attributes['padding-right']) {
                $style .= $displacement . 'padding:' .
                    $attributes['padding-top'] . self::dimension(@$attributes['padding_top_measure']) . ' ' .
                    $attributes['padding-right'] . self::dimension(@$attributes['padding_right_measure']) . ' ' .
                    $attributes['padding-bottom'] . self::dimension(@$attributes['padding_bottom_measure']) . self::cssImportant($importantArr, 'padding') . ';' . $br;
            } else {
                $style .= $displacement . 'padding:' .
                    $attributes['padding-top'] . self::dimension(@$attributes['padding_top_measure']) . ' ' .
                    $attributes['padding-right'] . self::dimension(@$attributes['padding_right_measure']) . ' ' .
                    $attributes['padding-bottom'] . self::dimension(@$attributes['padding_bottom_measure']) . ' ' .
                    $attributes['padding-left'] . self::dimension(@$attributes['padding_left_measure']) . self::cssImportant($importantArr, 'padding') . ';' . $br;
            }
        } else {
            if (isset($attributes['padding-top'])) {
                $style .= $displacement . 'padding-top:' . $attributes['padding-top'] . self::dimension(@$attributes['padding_top_measure']) . (isset($importantArr['padding-top']) ? $importantArr['padding-top'] : '') . ';' . $br;
            }
            if (isset($attributes['padding-right'])) {
                $style .= $displacement . 'padding-right:' . $attributes['padding-right'] . self::dimension(@$attributes['padding_right_measure']) . (isset($importantArr['padding-right']) ? $importantArr['padding-right'] : '') . ';' . $br;
            }
            if (isset($attributes['padding-bottom'])) {
                $style .= $displacement . 'padding-bottom:' . $attributes['padding-bottom'] . self::dimension(@$attributes['padding_bottom_measure']) . (isset($importantArr['padding-bottom']) ? $importantArr['padding-bottom'] : '') . ';' . $br;
            }
            if (isset($attributes['padding-left'])) {
                $style .= $displacement . 'padding-left:' . $attributes['padding-left'] . self::dimension(@$attributes['padding_left_measure']) . (isset($importantArr['padding-left']) ? $importantArr['padding-left'] : '') . ';' . $br;
            }
        }

        if (
            isset($attributes['margin-top']) &&
            isset($attributes['margin-left']) &&
            isset($attributes['margin-right']) &&
            isset($attributes['margin-bottom'])
        ) {
            $attributes['margin_top_measure'] = $attributes['margin_top_measure'] ?? null;
            $attributes['margin_right_measure'] = $attributes['margin_right_measure'] ?? null;
            $attributes['margin_bottom_measure'] = $attributes['margin_bottom_measure'] ?? null;
            $attributes['margin_left_measure'] = $attributes['margin_left_measure'] ?? null;
            if (
                $attributes['margin-top'] == $attributes['margin-bottom'] &&
                $attributes['margin-left'] == $attributes['margin-right']
            ) {
                $style .= $displacement . 'margin:' .
                    $attributes['margin-top'] . self::dimension($attributes['margin_top_measure']) . ' ' .
                    $attributes['margin-right'] . self::dimension($attributes['margin_right_measure'], '', $attributes['margin-right']) . self::cssImportant($importantArr, 'margin') . ';' . $br;
            } elseif ($attributes['margin-left'] == $attributes['margin-right']) {
                $style .= $displacement . 'margin:' .
                    $attributes['margin-top'] . self::dimension($attributes['margin_top_measure']) . ' ' .
                    (
                    $attributes['margin_right_measure'] == 'auto' ?
                        'auto' :
                        $attributes['margin-right'] . self::dimension($attributes['margin_right_measure'], '', $attributes['margin-right'])
                    ) . ' ' .
                    $attributes['margin-bottom'] . self::dimension($attributes['margin_bottom_measure']) . self::cssImportant($importantArr, 'margin') . ';' . $br;
            } elseif ($attributes['margin_right_measure'] == 'auto') {
                $style .= $displacement . 'margin:' .
                    $attributes['margin-top'] . self::dimension($attributes['margin_top_measure']) . ' auto ' .
                    $attributes['margin-bottom'] . self::dimension($attributes['margin_bottom_measure']) . self::cssImportant($importantArr, 'margin') . ';' . $br;
            } else {
                $style .= $displacement . 'margin:' .
                    $attributes['margin-top'] . self::dimension($attributes['margin_top_measure']) . ' ' .
                    (
                        $attributes['margin_right_measure'] == 'auto' ?
                            'auto' :
                            $attributes['margin-right'] . self::dimension($attributes['margin_right_measure'], '', $attributes['margin-right'])
                    ) . ' ' .
                    $attributes['margin-bottom'] . self::dimension($attributes['margin_bottom_measure']) . ' ' .
                    (
                    $attributes['margin_left_measure'] == 'auto' ?
                        'auto' :
                        $attributes['margin-left'] . self::dimension($attributes['margin_left_measure'], '', $attributes['margin-left'])
                    ) . self::cssImportant($importantArr, 'margin') . ';' . $br;
            }
        } else {
            if (isset($attributes['margin-top'])) {
                $style .= $displacement . 'margin-top:' . $attributes['margin-top'] . self::dimension(@$attributes['margin_top_measure']) . ($importantArr['margin-top'] ?? '') . ';' . $br;
            }
            if (isset($attributes['margin-right'])) {
                $style .= $displacement . 'margin-right:' . $attributes['margin-right'] . self::dimension(@$attributes['margin_right_measure'], '', $attributes['margin-right']) . ($importantArr['margin-right'] ?? '') . ';' . $br;
            } elseif (isset($attributes['margin_right_measure']) && $attributes['margin_right_measure'] == 'auto') {
                $style .= $displacement . 'margin-right: auto' . ($importantArr['margin-right'] ?? '') . ';' . $br;
            }
            if (isset($attributes['margin-bottom'])) {
                $style .= $displacement . 'margin-bottom:' . $attributes['margin-bottom'] . self::dimension(@$attributes['margin_bottom_measure']) . ($importantArr['margin-bottom'] ?? '') . ';' . $br;
            }
            if (isset($attributes['margin-left'])) {
                $style .= $displacement . 'margin-left:' . $attributes['margin-left'] . self::dimension(@$attributes['margin_left_measure'], '', $attributes['margin-left']) . ($importantArr['margin-left'] ?? '') . ';' . $br;
            } elseif (isset($attributes['margin_right_measure']) && $attributes['margin_right_measure'] == 'auto') {
                $style .= $displacement . 'margin-left: auto' . ($importantArr['margin-left'] ?? '') . ';' . $br;
            }
        }


        return $style;
    }

    public static function dimension($dimension, $default = '', $value = '')
    {
        if ($value && !preg_match('/^([\-0-9\.]+)$/', $value, $matches)) {
            return '';
        }

        if ($dimension){
            if ($dimension == 'pr'){
                $dimension = '%';
            }
            $text = $dimension;
        } else {
            if ($default == '') {
                $default = 'px';
            }
            $text = $default;
        }
        return $text;
    }

    public static function cssImportant($importantArr, $attr)
    {
        if (
            (isset($importantArr[$attr]) && $importantArr[$attr])
            || (isset($importantArr[$attr . '-top']) && $importantArr[$attr . '-top'])
            || (isset($importantArr[$attr . '-left']) && $importantArr[$attr . '-left'])
            || (isset($importantArr[$attr . '-right']) && $importantArr[$attr . '-right'])
            || (isset($importantArr[$attr . '-bottom']) && $importantArr[$attr . '-bottom'])
        ) {
            return '!important';
        }
    }

    public static function vArr($visibility, $string = false)
    {

        $arr = explode(',', $visibility);

        foreach ($arr as $key => $item) {
            if ($string) {
                $arr[$key] = trim($item);
            } else {
                $arr[$key] = (int)trim($item);
            }
        }

        return $arr;
    }

    public static function vStr($arr, $string = false)
    {

        foreach ($arr as $key => $item) {
            if ($string) {
                $arr[$key] = trim($item);
            } else {
                $arr[$key] = (int)trim($item);
            }
        }

        $str = implode(',', $arr);

        if (!$str) $str = '';

        return $str;
    }

    private static function addThemeStyleCacheRecord($theme_name, $accessibility, $accessibilityStyles)
    {
        $css = self::getCss($theme_name, array($accessibility), '', true, $accessibilityStyles);
        $sqlDataArray = array(
            'theme_name' => $theme_name,
            'accessibility' => $accessibility,
            'css' => $css,
        );
        tep_db_perform(TABLE_THEMES_STYLES_CACHE, $sqlDataArray);
        return $css;
    }

    public static function createCache($theme_name, $accessibility = false, $needDelete = true)
    {
        if ($accessibility == 'all'){
            $accessibility = false;
        } elseif ($accessibility == 'main'){
            $accessibility = '';
        }

        self::$cssFrontend = true;
        $themesPath = DIR_FS_CATALOG . 'themes' . DIRECTORY_SEPARATOR . $theme_name . DIRECTORY_SEPARATOR;

        if ($needDelete) {
            tep_db_query("delete from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($theme_name) . "'" . ($accessibility !== false ? " and accessibility = '" . $accessibility . "'" : ""));
        }

        $bottom = '';
        $basicThemesPath = DIR_FS_CATALOG . 'themes' . DIRECTORY_SEPARATOR . 'basic' . DIRECTORY_SEPARATOR;
        if (file_exists($basicThemesPath . 'css' . DIRECTORY_SEPARATOR . 'bottom.css')) {
            $bottom = file_get_contents($basicThemesPath . 'css' . DIRECTORY_SEPARATOR . 'bottom.css');
        }

        if ($accessibility === false) {
            $query = \common\models\ThemesStyles::find()->where(['theme_name' => $theme_name])->orderBy('accessibility, media, selector, attribute, visibility')->asArray();

            $accessibilityStyles = [];
            $prevAccessibility = null;
            foreach($query->each() as $item) {
                if ($item['accessibility'] != $prevAccessibility && !empty($accessibilityStyles)) {
                    $css = self::addThemeStyleCacheRecord($theme_name, $prevAccessibility, $accessibilityStyles);
                    if ($prevAccessibility == '.b-bottom') {
                        $bottom .= $css;
                    }
                    $accessibilityStyles = [];
                }
                $prevAccessibility = $item['accessibility'];
                $accessibilityStyles[] = $item;
            }
            if (!empty($accessibilityStyles)) {
                $css = self::addThemeStyleCacheRecord($theme_name, $prevAccessibility, $accessibilityStyles);
                if ($prevAccessibility == '.b-bottom') {
                    $bottom .= $css;
                }
            }
            unset($accessibilityStyles);

            $bottom = \frontend\design\Info::minifyCss($bottom);
            $filePath = $themesPath . 'css' . DIRECTORY_SEPARATOR;
            \yii\helpers\FileHelper::createDirectory($filePath);
            file_put_contents($filePath . 'style.css', $bottom);

        } else {
            $css = self::getCss($theme_name, array($accessibility));
            $sqlDataArray = array(
                'theme_name' => $theme_name,
                'accessibility' => $accessibility,
                'css' => $css,
            );
            tep_db_perform(TABLE_THEMES_STYLES_CACHE, $sqlDataArray);

            if ($accessibility == '.b-bottom') {
                $bottom .= $css;

                $bottom = \frontend\design\Info::minifyCss($bottom);
                $filePath = $themesPath . 'css' . DIRECTORY_SEPARATOR;
                \yii\helpers\FileHelper::createDirectory($filePath);
                file_put_contents($filePath . 'style.css', $bottom);
            }
        }

        if (file_exists($themesPath . 'cache' . DIRECTORY_SEPARATOR)) {
            \yii\helpers\FileHelper::removeDirectory($themesPath . 'cache' . DIRECTORY_SEPARATOR);
        }
        
    }

    public static function compareAttributes($attr1, $attr2)
    {
        if (
            $attr1['selector'] == $attr2['selector'] &&
            $attr1['attribute'] == $attr2['attribute'] &&
            $attr1['visibility'] == $attr2['visibility'] &&
            $attr1['media'] == $attr2['media'] &&
            $attr1['accessibility'] == $attr2['accessibility']
        ) {
            return true;
        } else {
            return false;
        }
    }

    public static function getOneAttribute($attr, $old = false)
    {
        $arr['selector'] =  $attr['selector'];
        $arr['attribute'] =  $attr['attribute'];
        $arr['value'] =  $attr['value'];
        if ($old) {
            $arr['value_old'] =  $attr['value_old'];
        }
        $arr['visibility'] =  $attr['visibility'];
        $arr['media'] =  $attr['media'];
        $arr['accessibility'] =  $attr['accessibility'];

        return $arr;
    }

    /*
     * Parsing string with form data.
     * Using when the form has too many inputs
     * */
    public static function paramsFromOneInput($values)
    {
        if (is_array($values)){
            $params1 = $values;
        } else {
            $params1 = json_decode($values, true);
        }
        $params = [];
        if (!is_array($params1)) {
            return '';
        }

        foreach ($params1 as $key => $value) {
            $keys = explode('[', $key);
            foreach ($keys as $i => $val) {
                $keys[$i] = str_replace(']', '', $val);
            }
            if (isset($keys[0])){
                if (isset($keys[1])) {
                    if (isset($keys[2])) {
                        if (isset($keys[3])) {
                            if (isset($keys[4])) {
                                if (isset($keys[5])) {
                                    $params[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]][$keys[5]] = $value;
                                } else {
                                    $params[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $value;
                                }
                            } else {
                                $params[$keys[0]][$keys[1]][$keys[2]][$keys[3]] = $value;
                            }
                        } else {
                            $params[$keys[0]][$keys[1]][$keys[2]] = $value;
                        }
                    } else {
                        $params[$keys[0]][$keys[1]] = $value;
                    }
                } else {
                    $params[$keys[0]] = $value;
                }
            }
        }

        return $params;
    }


    /*
     * changeCssAttributes change all old css attributes (not general) to new
     * it can be removed after update all projects
     * */
    public static function changeCssAttributes($theme_name)
    {

        $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . $theme_name . "' and setting_group = 'hide' and setting_name = 'new_attributes'");

        if (tep_db_num_rows($query) === 0) {
            tep_db_perform(TABLE_THEMES_SETTINGS, array(
                'theme_name' => $theme_name,
                'setting_group' => 'hide',
                'setting_name' => 'new_attributes',
                'setting_value' => '1'
            ));

            $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . $theme_name . "'");

            $attributesArr = ['z_index', 'vertical_align', 'text_transform', 'text_decoration', 'min_width', 'max_width', 'min_height', 'max_height', 'padding_left', 'padding_right', 'border_left_width', 'border_right_width', 'font_family', 'font_size', 'font_weight', 'font_style', 'line_height', 'text_align', 'background_color', 'background_position', 'background_repeat', 'background_size', 'padding_top', 'padding_bottom', 'margin_top', 'margin_left', 'margin_right', 'margin_bottom', 'border_top_width', 'border_top_color', 'border_left_color', 'border_right_color', 'border_bottom_width', 'border_bottom_color'];

            $attributesArr2 = ['top_dimension', 'left_dimension', 'right_dimension', 'bottom_dimension', 'font_size_dimension', 'padding_top_dimension', 'padding_left_dimension', 'padding_right_dimension', 'padding_bottom_dimension', 'margin_top_dimension', 'margin_left_dimension', 'margin_right_dimension', 'margin_bottom_dimension'];

            $attributesArr3 = ['border_radius_1', 'border_radius_2', 'border_radius_3', 'border_radius_4'];

            while ($item = tep_db_fetch_array($query)) {
                if (in_array($item['attribute'], $attributesArr)) {

                    tep_db_perform(TABLE_THEMES_STYLES, array('attribute' => str_replace('_', '-', $item['attribute'])), 'update', " id = '" . $item['id'] . "'");
                    //tep_db_perform(TABLE_THEMES_STYLES_TMP, array('attribute' => str_replace('_', '-', $item['attribute'])), 'update', " id = '" . $item['id'] . "'");

                } elseif (in_array($item['attribute'], $attributesArr2)) {

                    tep_db_perform(TABLE_THEMES_STYLES, array('attribute' => str_replace('_dimension', '_measure', $item['attribute'])), 'update', " id = '" . $item['id'] . "'");
                    //tep_db_perform(TABLE_THEMES_STYLES_TMP, array('attribute' => str_replace('_dimension', '_measure', $item['attribute'])), 'update', " id = '" . $item['id'] . "'");

                } elseif (in_array($item['attribute'], $attributesArr3)) {

                    switch ($item['attribute']) {
                        case 'border_radius_1': $attr = 'border-top-left-radius'; break;
                        case 'border_radius_2': $attr = 'border-top-right-radius'; break;
                        case 'border_radius_3': $attr = 'border-bottom-right-radius'; break;
                        case 'border_radius_4': $attr = 'border-bottom-left-radius'; break;

                    }
                    tep_db_perform(TABLE_THEMES_STYLES, array('attribute' => $attr), 'update', " id = '" . $item['id'] . "'");
                    //tep_db_perform(TABLE_THEMES_STYLES_TMP, array('attribute' => $attr), 'update', " id = '" . $item['id'] . "'");

                }
            }


            $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS_TMP);

            while ($item = tep_db_fetch_array($query)) {
                if (in_array($item['setting_name'], $attributesArr)) {

                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, array('setting_name' => str_replace('_', '-', $item['setting_name'])), 'update', " id = '" . $item['id'] . "'");
                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, array('setting_name' => str_replace('_', '-', $item['setting_name'])), 'update', " id = '" . $item['id'] . "'");

                } elseif (in_array($item['setting_name'], $attributesArr2)) {

                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, array('setting_name' => str_replace('_dimension', '_measure', $item['setting_name'])), 'update', " id = '" . $item['id'] . "'");
                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, array('setting_name' => str_replace('_dimension', '_measure', $item['setting_name'])), 'update', " id = '" . $item['id'] . "'");

                } elseif (in_array($item['attribute'] ?? null, $attributesArr3)) {

                    switch ($item['attribute']) {
                        case 'border_radius_1': $attr = 'border-top-left-radius'; break;
                        case 'border_radius_2': $attr = 'border-top-right-radius'; break;
                        case 'border_radius_3': $attr = 'border-bottom-right-radius'; break;
                        case 'border_radius_4': $attr = 'border-bottom-left-radius'; break;

                    }
                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, array('setting_name' => $attr), 'update', " id = '" . $item['id'] . "'");
                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, array('setting_name' => $attr), 'update', " id = '" . $item['id'] . "'");

                }
            }

        }
    }

    public static function getCssWidgetsList($theme_name)
    {
        $listArr = [];

        $query = tep_db_query("select distinct accessibility from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($theme_name) . "' order by accessibility");

        while ($item = tep_db_fetch_array($query)) {
            $listArr[] = $item['accessibility'];
        }

        return $listArr;
    }

    public static function getNewUpdates($theme_name)
    {
        $updates = [];

        $update = tep_db_fetch_array(tep_db_query("
                select setting_value 
                from " . TABLE_THEMES_SETTINGS . " 
                where 
                    theme_name = '" . tep_db_input($theme_name) . "' and
                    setting_group = 'hide' and
                    setting_name = 'theme_update'
            "));

        $path = DIR_FS_CATALOG . 'themes'
            . DIRECTORY_SEPARATOR . $theme_name
            . DIRECTORY_SEPARATOR . 'updates';

        if (file_exists($path)) {
            $dir = scandir($path);
            foreach ($dir as $file) {
                $time = str_replace('.json', '', $file);
                if (
                    file_exists($path . DIRECTORY_SEPARATOR . $file) &&
                    is_file($path . DIRECTORY_SEPARATOR . $file) &&
                    (int)$time > (int)$update['setting_value']
                ) {
                    $updates[$time] = json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . $file), true);
                }
            }
        }
        ksort($updates);

        return $updates;
    }

    public static function saveUpdateDate($theme_name, $date)
    {
        $update = tep_db_fetch_array(tep_db_query("
                select id 
                from " . TABLE_THEMES_SETTINGS . " 
                where 
                    theme_name = '" . tep_db_input($theme_name) . "' and
                    setting_group = 'hide' and
                    setting_name = 'theme_update'
            "));

        if ($update['id']) {
            $sql_data_array = array(
                'setting_value' => $date
            );
            tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " id = '" . (int)$update['id'] . "'");
        } else {
            $sql_data_array = array(
                'theme_name' => $theme_name,
                'setting_group' => 'hide',
                'setting_name' => 'theme_update',
                'setting_value' => $date,
            );
            tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
        }
    }

    public static function changeVisibilityFromIdToWidth($array)
    {
        foreach ($array as $key => $attr) {
            $visibilityArr = explode(',', $array[$key]['visibility']);
            foreach ($visibilityArr as $i => $id) {
                if ($id > 10) {
                    $width = tep_db_fetch_array(tep_db_query("
                        select setting_value
                        from " . TABLE_THEMES_SETTINGS . " 
                        where id = '" . (int)$id . "' and 	setting_name = 'media_query'
                    "));
                    $visibilityArr[$i] = $width['setting_value'];
                }
            }
            $array[$key]['visibility'] = implode(',', $visibilityArr);
        }

        return $array;
    }

    public static function changeVisibilityFromWidthToId($array, $theme_name)
    {
        foreach ($array as $key => $attr) {
            $visibilityArr = explode(',', $array[$key]['visibility']);
            foreach ($visibilityArr as $i => $width) {
                if (strlen($width) > 1) {
                    $id = tep_db_fetch_array(tep_db_query("
                        select id
                        from " . TABLE_THEMES_SETTINGS . " 
                        where setting_value = '" . tep_db_input($width) . "' and setting_name = 'media_query'
                    "));
                    if ($id['id']) {
                        $visibilityArr[$i] = $id['id'];
                    } else {
                        $sql_data_array = array(
                            'theme_name' => $theme_name,
                            'setting_group' => 'extend',
                            'setting_name' => 'media_query',
                            'setting_value' => $width,
                        );
                        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
                        $visibilityArr[$i] = tep_db_insert_id();
                    }
                }
            }
            $array[$key]['visibility'] = implode(',', $visibilityArr);
        }

        return $array;
    }

    public static function mergeSteps($steps)
    {
        $attributesChanged = [];
        $attributesDelete = [];
        $attributesNew = [];

        foreach ($steps as $dataFromStep) {

            foreach ($dataFromStep['attributes_new'] as $itemNew) {
                $foundInDelete = false;
                foreach ($attributesDelete as $key => $attr) {
                    if (Style::compareAttributes($itemNew, $attr)) {
                        $itemNew['value_old'] = $attributesDelete[$key]['value'];
                        $attributesChanged[] = Style::getOneAttribute($itemNew, true);
                        unset($attributesDelete[$key]);
                        $foundInDelete = true;
                        break;
                    }
                }
                if (!$foundInDelete) {
                    $attributesNew[] = Style::getOneAttribute($itemNew);
                }
            }

            foreach ($dataFromStep['attributes_changed'] as $itemChanged) {
                $foundInNew = false;
                foreach ($attributesNew as $key => $attr) {
                    if (Style::compareAttributes($itemChanged, $attr)) {
                        $attributesNew[$key] = Style::getOneAttribute($itemChanged);
                        $foundInNew = true;
                        break;
                    }
                }
                if (!$foundInNew) {
                    $foundInChanged = false;
                    foreach ($attributesChanged as $key => $attr) {
                        if (Style::compareAttributes($itemChanged, $attr)) {
                            $old = $attributesChanged[$key]['value_old'];
                            $attributesChanged[$key] = Style::getOneAttribute($itemChanged);
                            $attributesChanged[$key]['value_old'] = $old;
                            if ($attributesChanged[$key]['value_old'] == $attributesChanged[$key]['value']) {
                                unset($attributesChanged[$key]);
                            }
                            $foundInChanged = true;
                            break;
                        }
                    }
                    if (!$foundInChanged) {
                        $attributesChanged[] = Style::getOneAttribute($itemChanged, true);
                    }
                }
            }

            foreach ($dataFromStep['attributes_delete'] as $itemDelete) {
                foreach ($attributesNew as $key => $attr) {
                    if (Style::compareAttributes($itemDelete, $attr)) {
                        unset($attributesNew[$key]);
                        break;
                    }
                }
                $attributesDelete[] = Style::getOneAttribute($itemDelete);
            }

        }

        return [
            'attributes_new' => $attributesNew,
            'attributes_changed' => $attributesChanged,
            'attributes_delete' => $attributesDelete
        ];

    }

    public static function addExistValueFromCurrentTheme($update, $theme_name)// and add local_id
    {
        foreach ($update as $newChangedDelete => $updatePart) {
            foreach ($updatePart as $i => $attribute) {
                if (!$attribute) {
                    break;
                }

                $update[$newChangedDelete][$i]['local_id'] = $i;

                $query = tep_db_fetch_array(tep_db_query("
                    select * 
                    from " . TABLE_THEMES_STYLES . " 
                    where 
                        theme_name = '" . tep_db_input($theme_name) . "' and
                        selector = '" . tep_db_input($attribute['selector']) . "' and
                        attribute = '" . tep_db_input($attribute['attribute']) . "' and
                        visibility = '" . tep_db_input($attribute['visibility']) . "' and
                        media = '" . tep_db_input($attribute['media']) . "'
                "));

                if ($query['value']) {
                    $update[$newChangedDelete][$i]['value_exist'] = $query['value'];
                }

            }
        }

        return $update;
    }

    public static function changeSelectorsByVisibility($update)
    {
        foreach ($update as $newChangedDelete => $updatePart) {
            foreach ($updatePart as $i => $attribute) {
                if (!$attribute) {
                    break;
                }

                if ($attribute['visibility']) {
                    $visibilityArr = explode(',', $attribute['visibility']);
                    $update[$newChangedDelete][$i]['visibility'] = '';
                    foreach ($visibilityArr as $visibilityId) {
                        if ($visibilityId < 10) {

                            $selectorArr = explode(',', $attribute['selector']);
                            foreach ($selectorArr as $sItem => $class) {
                                $selectorArr[$sItem] = trim($selectorArr[$sItem]);
                                if ($visibilityId == 2){
                                    $selectorArr[$sItem] .= '.active';
                                }
                                if ($visibilityId == 3){
                                    $selectorArr[$sItem] .= ':before';
                                }
                                if ($visibilityId == 4){
                                    $selectorArr[$sItem] .= ':after';
                                }
                                if ($visibilityId == 1){
                                    $selectorArr[$sItem] .= ':hover';
                                }
                            }
                            $update[$newChangedDelete][$i]['selector'] = implode(', ', $selectorArr);

                        } else {
                            $update[$newChangedDelete][$i]['visibility'] = $visibilityId;// in visibility only media width id
                        }
                    }
                }
            }
        }

        return $update;
    }

    public static function addToArraySortedByMediaAndSelector($update, $theme_name)
    {
        $attributesByMedia = [];

        foreach (self::getThemeMediaQueries($theme_name) as $item) {
            $mediaQuery = $item ? $item['full'] : '';
            $mediaId = $item && $item['id'] ? $item['id'] : '';

            foreach ($update as $newChangedDelete => $updatePart) {
                foreach ($updatePart as $i => $attribute) {
                    if (!$attribute) {
                        break;
                    }

                    if ($attribute['visibility'] == $mediaId && $attribute['media'] == $mediaQuery) {
                        $attributesByMedia[$newChangedDelete][$mediaQuery][$attribute['selector']][$i] = $attribute;
                    }
                }
            }
        }
        return $attributesByMedia;
    }

    public static function getThemeMediaQueries($theme_name, $visibilityAndMedia = 'all', $addEmptyField = true)
    {
        $queries = [];

        if ($addEmptyField) {
            $queries[0] = '';
        }

        if ($visibilityAndMedia == 'all' || $visibilityAndMedia == 'visibility') {
            $queriesTmp = [];
            $mediaQueries = tep_db_query("
                select id, setting_value 
                from " . TABLE_THEMES_SETTINGS . " 
                where
                    theme_name = '" . tep_db_input($theme_name) . "' and
                    setting_name = 'media_query'
            ");

            while ($item = tep_db_fetch_array($mediaQueries)) {

                $arr2 = explode('w', $item['setting_value']);
                $full = '';
                if ($arr2[0]) {
                    $full .= '(min-width:' . $arr2[0] . 'px)';
                }
                if ($arr2[0] && $arr2[1]) {
                    $full .= ' and ';
                }
                if ($arr2[1]) {
                    $full .= '(max-width:' . $arr2[1] . 'px)';
                }

                $queriesTmp[($arr2[1] ? $arr2[1] : $arr2[0])] = [
                    'id' => $item['id'],
                    'full' => $full,
                    'short' => $item['setting_value']
                ];
            }

            krsort($queriesTmp);

            $queries = array_merge($queries, $queriesTmp);
        }

        if ($visibilityAndMedia == 'all' || $visibilityAndMedia == 'media') {
            $mediaQueries = tep_db_query("
                select distinct media 
                from " . TABLE_THEMES_STYLES . " 
                where
                    theme_name = '" . tep_db_input($theme_name) . "' and
                    media != ''
            ");
            while ($item = tep_db_fetch_array($mediaQueries)) {
                $queries[] = [
                    'full' => $item['media']
                ];
            }
        }

        return $queries;
    }

    public static function saveUpdate($submittedElements, $updates, $theme_name)
    {
        foreach ($updates as $tideName => $tide) {
            foreach ($tide as $attribute) {
                if (!$attribute) {
                    break;
                }
                if ($submittedElements[$tideName][$attribute['local_id']]) {

                    if ($tideName == 'attributes_new' || $tideName == 'attributes_changed') {

                        $query = tep_db_fetch_array(tep_db_query("select id from " . TABLE_THEMES_STYLES . " where
                                theme_name = '" . tep_db_input($theme_name) . "' and
                                selector = '" . tep_db_input($attribute['selector']) . "' and
                                attribute = '" . tep_db_input($attribute['attribute']) . "' and
                                visibility = '" . tep_db_input($attribute['visibility']) . "' and
                                media = '" . tep_db_input($attribute['media']) . "'
                        "));
                        if ($query['id']) {
                            $sql_data_array = array(
                                'value' => tep_db_input($attribute['value'])
                            );
                            tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array, 'update', "
                                theme_name = '" . tep_db_input($theme_name) . "' and
                                selector = '" . tep_db_input($attribute['selector']) . "' and
                                attribute = '" . tep_db_input($attribute['attribute']) . "' and
                                visibility = '" . tep_db_input($attribute['visibility']) . "' and
                                media = '" . tep_db_input($attribute['media']) . "'
                            ");
                        } else {
                            $sql_data_array = array(
                                'theme_name' => $theme_name,
                                'selector' => $attribute['selector'],
                                'attribute' => $attribute['attribute'],
                                'value' => $attribute['value'],
                                'visibility' => $attribute['visibility'],
                                'media' => $attribute['media'],
                            );
                            tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array);
                        }

                    } elseif ($tideName == 'attributes_delete') {

                        tep_db_query("
                            delete 
                            from " . TABLE_THEMES_STYLES . " 
                            where
                                theme_name = '" . tep_db_input($theme_name) . "' and
                                selector = '" . tep_db_input($attribute['selector']) . "' and
                                attribute = '" . tep_db_input($attribute['attribute']) . "' and
                                visibility = '" . tep_db_input($attribute['visibility']) . "' and
                                media = '" . tep_db_input($attribute['media']) . "'
                        ");

                    }
                }
            }
        }
    }

    public static function cssBoxSave($attributes, $theme_name)
    {
        $count1 = 0;
        $count2 = 0;
        $count3 = 0;
        $count4 = 0;

        $boxes = tep_db_query("
            select bs.box_id, bs.setting_value, bs.visibility, bs.setting_name
            from " . TABLE_DESIGN_BOXES_TMP . " b, " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " bs 
            where 
                b.theme_name = '" . tep_db_input($theme_name) . "' and
                b.id = bs.box_id
            ");
        while ($item = tep_db_fetch_array($boxes)) {
            if (
                !in_array($item['setting_name'], self::$attributesHaveRules) &&
                !in_array($item['setting_name'], self::$attributesNoRules) &&
                !in_array($item['setting_name'], self::$attributesHasMeasure) ||
                $item['setting_name'] == 'display_none' ||
                $item['setting_name'] == 'box_align'
            ) {
                continue;
            }
            $item['attribute'] = $item['setting_name'];
            $item['value'] = $item['setting_value'];
            $attributesOld[] = [
                'box_id' => $item['box_id'],
                'attribute' => $item['attribute'],
                'value' => $item['value'],
                'visibility' => $item['visibility']
            ];

            $find = false;
            foreach ($attributes as $i => $attr) {
                if (strpos($attr['selector'], '#box-') !== 0) {
                    unset($attributes[$i]);
                    continue;
                }
                if (preg_match('/^\#box\-([0-9]+)$/', trim($attr['selector']), $matches)) {
                    $attr['box_id'] =  $matches[1];
                    $attributes[$i]['box_id'] =  $matches[1];
                } else {
                    continue;
                }
                if (
                    $attr['box_id'] == $item['box_id'] &&
                    $attr['attribute'] == $item['attribute'] &&
                    (string)$attr['visibility'] === (string)$item['visibility']
                ) {
                    if ($attr['value'] == $item['value']) {
                        $count1++;
                    } else {
                        // update styles
                        $keys[] = [$attr['value'], $item['value']];
                        $count2++;
                        $attributesChanged[] = [
                            'box_id' => $attr['box_id'],
                            'attribute' => $attr['attribute'],
                            'value_old' => $item['value'],
                            'value' => $attr['value'],
                            'visibility' => $attr['visibility']
                        ];

                        tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, array('setting_value' => $attr['value']), 'update', "
                            box_id = '" . tep_db_input($item['box_id']) . "' and
                            setting_name = '" . tep_db_input($item['attribute']) . "' and
                            visibility = '" . tep_db_input($item['visibility']) . "'
                      ");
                        tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, array('setting_value' => $attr['value']), 'update', "
                            box_id = '" . tep_db_input($item['box_id']) . "' and
                            setting_name = '" . tep_db_input($item['attribute']) . "' and
                            visibility = '" . tep_db_input($item['visibility']) . "'
                      ");
                    }
                    unset($attributes[$i]);
                    $find = true;
                } elseif (
                    $attr['box_id'] == $item['box_id'] &&
                    $attr['attribute'] == $item['attribute'] &&
                    (string)$attr['visibility'] === (string)$item['visibility'] &&
                    $attr['media'] == $item['media']
                ) {
                    unset($attributes[$i]);
                }
            }
            if (!$find) {
                // remove styles
                $count3++;
                $attributesDelete[] = [
                    'box_id' => $item['box_id'],
                    'setting_name' => $item['attribute'],
                    'setting_value' => $item['value'],
                    'visibility' => $item['visibility']
                ];
                tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . "
                          where 
                            box_id = '" . tep_db_input($item['box_id']) . "' and
                            setting_name = '" . tep_db_input($item['attribute']) . "' and
                            visibility = '" . tep_db_input($item['visibility']) . "'
              ");
                tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . "
                          where 
                            box_id = '" . tep_db_input($item['box_id']) . "' and
                            setting_name = '" . tep_db_input($item['attribute']) . "' and
                            visibility = '" . tep_db_input($item['visibility']) . "'
              ");
            }
        }

        // add new styles
        foreach ($attributes as $attr) {
            if (strpos($attr['selector'], '#box-') !== 0) {
                unset($attributes[$i]);
                continue;
            }
            if (preg_match('/^\#box\-([0-9]+)$/', trim($attr['selector']), $matches)) {
                $attr['box_id'] =  $matches[1];
            } else {
                continue;
            }
            $sglArray = [
                'box_id' => $attr['box_id'],
                'setting_name' => $attr['attribute'],
                'setting_value' => $attr['value'],
                'visibility' => $attr['visibility']
            ];
            $attributesNew[] = $sglArray;
            tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, $sglArray);
            tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sglArray);
            $count4++;
        }
        $data = [
            'attributes_box_changed' => $attributesChanged,
            'attributes_box_delete' => $attributesDelete,
            'attributes_box_new' => $attributesNew,
        ];
        return $data;
    }

    public static function cssSave($params)
    {

        /*$query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'css' and setting_group = 'css'");
      $css_old = tep_db_fetch_array($query);
      $css_old = $css_old['setting_value'];*/

        if ($params['widget'] == 'all' || !$params['widget'] || $params['widget'] == 'block_box') {
            $accessibility = false;
        } elseif ($params['widget'] == 'main') {
            $accessibility = '';
        } else {
            $accessibility = $params['widget'];
        }

        $attributes = Style::cssCompile($params['css'], $params['theme_name'], $accessibility);

        $allAttr = count($attributes);

        $at = array();
        $attributesOld = array();
        $attributesChanged = array();
        $attributesDelete = array();
        $attributesNew = array();
        $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'" . ($accessibility !== false ? " and accessibility = '" . tep_db_input($accessibility) . "' " : ""));
        $keys = array();
        $count1 = 0;
        $count2 = 0;
        $count3 = 0;
        $count4 = 0;

        if ($params['widget'] == 'all' || $params['widget'] == 'block_box') {
            $boxSaveData = self::cssBoxSave($attributes, $params['theme_name']);
        }

        if ($params['widget'] != 'block_box') {
            while ($item = tep_db_fetch_array($query)) {
                $attributesOld[] = [
                    'selector' => $item['selector'],
                    'attribute' => $item['attribute'],
                    'value' => $item['value'],
                    'visibility' => $item['visibility'],
                    'media' => $item['media'],
                    'accessibility' => $item['accessibility']
                ];

                $find = false;
                foreach ($attributes as $i => $attr) {
                    if (strpos($attr['selector'], '#box-') === 0) {
                        unset($attributes[$i]);
                        continue;
                    }
                    if (
                        $attr['selector'] == $item['selector'] &&
                        $attr['attribute'] == $item['attribute'] &&
                        (string)$attr['visibility'] === (string)$item['visibility'] &&
                        $attr['media'] == $item['media'] &&
                        $attr['accessibility'] == $item['accessibility']
                    ) {
                        if ($attr['value'] == $item['value']) {
                            $count1++;
                        } else {
                            // update styles
                            $keys[] = [$attr['value'], $item['value']];
                            $count2++;
                            $attributesChanged[] = [
                                'selector' => $attr['selector'],
                                'attribute' => $attr['attribute'],
                                'value_old' => $item['value'],
                                'value' => $attr['value'],
                                'visibility' => $attr['visibility'],
                                'media' => $attr['media'],
                                'accessibility' => $attr['accessibility']
                            ];

                            tep_db_perform(TABLE_THEMES_STYLES, array('value' => $attr['value']), 'update', "
                            theme_name = '" . tep_db_input($params['theme_name']) . "' and
                            selector = '" . tep_db_input($item['selector']) . "' and
                            attribute = '" . tep_db_input($item['attribute']) . "' and
                            visibility = '" . tep_db_input($item['visibility']) . "' and
                            media = '" . tep_db_input($item['media']) . "' and
                            accessibility = '" . tep_db_input($item['accessibility']) . "'
                      ");
                        }
                        unset($attributes[$i]);
                        $find = true;
                    } elseif (
                        $attr['selector'] == $item['selector'] &&
                        $attr['attribute'] == $item['attribute'] &&
                        (string)$attr['visibility'] === (string)$item['visibility'] &&
                        $attr['media'] == $item['media'] &&
                        !$attr['accessibility'] && $item['accessibility']
                    ) {
                        unset($attributes[$i]);
                    }
                }
                if (!$find) {
                    // remove styles
                    $count3++;
                    $attributesDelete[] = [
                        'selector' => $item['selector'],
                        'attribute' => $item['attribute'],
                        'value' => $item['value'],
                        'visibility' => $item['visibility'],
                        'media' => $item['media'],
                        'accessibility' => $item['accessibility']
                    ];
                    tep_db_query("delete from " . TABLE_THEMES_STYLES . "
                          where 
                            theme_name = '" . tep_db_input($params['theme_name']) . "' and
                            selector = '" . tep_db_input($item['selector']) . "' and
                            attribute = '" . tep_db_input($item['attribute']) . "' and
                            visibility = '" . tep_db_input($item['visibility']) . "' and
                            media = '" . tep_db_input($item['media']) . "' and
                            accessibility = '" . tep_db_input($item['accessibility']) . "'
              ");
                }
            }

            // add new styles
            foreach ($attributes as $attr) {
                if (strpos($attr['selector'], '#box-') === 0) {
                    continue;
                }
                $sglArray = [
                    'theme_name' => $params['theme_name'],
                    'selector' => $attr['selector'],
                    'attribute' => $attr['attribute'],
                    'value' => $attr['value'],
                    'visibility' => $attr['visibility'],
                    'media' => $attr['media'],
                    'accessibility' => $attr['accessibility']
                ];
                $attributesNew[] = $sglArray;
                tep_db_perform(TABLE_THEMES_STYLES, $sglArray);
                $count4++;
            }
        }

        tep_db_query("delete from " . TABLE_THEMES_SETTINGS . "
                          where 
                            theme_name = '" . tep_db_input($params['theme_name']) . "' and
                            setting_group = 'css' and
                            setting_name = 'css'
              ");

        $response = 'All attributes: ' . $allAttr . "\n";
        $response .= 'Not changed: ' . $count1 . "\n";
        $response .= 'Changed: ' . $count2 . "\n";
        $response .= 'Removed: ' . $count3 . "\n";
        $response .= 'Added: ' . $count4 . "\n\n";

        Style::createCache($params['theme_name'], $params['widget']);

        $data = $boxSaveData ?? [];
        $data['theme_name'] = $params['theme_name'];
        $data['attributes_changed'] = $attributesChanged;
        $data['attributes_delete'] = $attributesDelete;
        $data['attributes_new'] = $attributesNew;
        Steps::cssSave($data);

        /*if (tep_db_num_rows($query) == 0) {
          $sql_data_array = array(
            'theme_name' => $params['theme_name'],
            'setting_group' => 'css',
            'setting_name' => 'css',
            'setting_value' => $params['css']
          );
          tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
        } else {
          $sql_data_array = array(
            'setting_value' => $params['css']
          );
          tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'css' and setting_name = 'css'");
        }

        $data = [
          'theme_name' => $params['theme_name'],
          'css_old' => $css_old,
          'css' => $params['css'],
        ];
        Steps::cssSave($data);*/

        return $response;

    }


    public static function getStylesByClasses($theme_name, $accessibility)
    {
        $styles = ThemesStyles::find()
            ->select(['selector', 'attribute', 'value'])
            ->where([
                'theme_name' => $theme_name,
                'accessibility' => $accessibility
            ])
            ->asArray()
            ->all();

        $classes = [];
        foreach ($styles as $attr) {
            $selector = str_replace($accessibility . ' ', '', $attr['selector']);
            $classes[$selector][$attr['attribute']] = $attr['value'];
        }

        $stylesByClasses = [];

        foreach ($classes as $class => $attributes) {
            $stylesByClasses[$class] = self::getAttributes($attributes);
        }

        return [
            'attributesArray' => $classes,
            'attributesText' => $stylesByClasses
        ];
    }

    public static function getStylesWrapper($blockStyles){
        $styles = (isset($blockStyles[0]) ? $blockStyles[0] : '');

        $mediaArr = \common\models\ThemesSettings::find()
            ->where([
                'setting_name' => 'media_query',
            ])->orderBy('setting_value')->asArray()->all();
        $media = [];
        $mediaSorting = [];
        foreach ($mediaArr as $mediaItem) {
            $arr = explode('w', $mediaItem['setting_value']);
            $mediaSorting[$mediaItem['id']] = $arr[1] ? $arr[1] : '';
            $media[$mediaItem['id']] = $mediaItem;
        }
        arsort($mediaSorting);

        foreach ($mediaSorting as $id => $val) {
            $item = $media[$id];
            $arr = explode('w', $item['setting_value']);
            $styles .= '@media';
            if ($arr[0]){
                $styles .= ' (min-width:' . $arr[0] . 'px)';
            }
            if ($arr[0] && $arr[1]){
                $styles .= ' and ';
            }
            if ($arr[1]){
                $styles .= ' (max-width:' . $arr[1] . 'px)';
            }
            $styles .= '{';
            $styles .= (isset($blockStyles[$item['id']]) ? $blockStyles[$item['id']] : '');
            $styles .= '} ';
        }

        return $styles;
    }

    public static function schema($val, $selector){
        $htm = '';
        $block_table = $selector . '{display:flex;flex-direction:column} ';
        $flex = $selector . '{display:flex;flex-wrap:wrap;} ';
        $block = $selector . '{display:block;} ';
        $div = $selector . ' > div:nth-child(n){width:100%;}';
        $div_n = $selector . ' > div:nth-child(%s){width:%s;%s}';
        $clear = 'clear:both;';
        $header = 'order:1;';
        $body = 'order:2;';
        $footer = 'order:3;';
        $float_none = 'float:none;';

        switch ($val){
            case '2-2':
            case '3-4':
            case '4-2':
            case '5-2':
            case '6-2':
            case '7-2':
            case '8-4':
            case '13-4':
            case '9-2':
            case '10-2':
            case '11-2':
            case '12-2':
            case '14-3':
            case '15-6':
                $htm .= $block;
                $htm .= $div;
                break;
            case '2-3':
            case '4-3':
            case '5-3':
            case '6-3':
            case '7-3':
            case '9-3':
            case '10-3':
            case '11-3':
            case '12-3':
                $htm .= $block_table;
                $htm .= sprintf($div_n, 1, '100%', $footer . $float_none);
                $htm .= sprintf($div_n, 2, '100%', $header . $float_none);
                break;
            case '3-2':
                $htm .= $block;
                $htm .= sprintf($div_n, 1, '50%', '');
                $htm .= sprintf($div_n, 2, '50%', '');
                $htm .= sprintf($div_n, 3, '100%', '');
                break;
            case '3-3':
                $htm .= $block;
                $htm .= sprintf($div_n, 1, '100%', '');
                $htm .= sprintf($div_n, 2, '50%', '');
                $htm .= sprintf($div_n, 3, '50%', '');
                break;
            case '3-5':
            case '8-5':
            case '13-5':
                $htm .= $block_table;
                $htm .= sprintf($div_n, 1, '100%', $footer . $float_none);
                $htm .= sprintf($div_n, 2, '100%', $body . $float_none);
                $htm .= sprintf($div_n, 3, '100%', $header . $float_none);
                break;
            case '3-6':
            case '8-6':
            case '13-6':
                $htm .= $block_table;
                $htm .= sprintf($div_n, 1, '100%', $body . $float_none);
                $htm .= sprintf($div_n, 2, '100%', $header . $float_none);
                $htm .= sprintf($div_n, 3, '100%', $footer . $float_none);
                break;
            case '8-2':
            case '13-2':
                $htm .= $flex;
                $htm .= sprintf($div_n, 1, '50%', 'order:1;');
                $htm .= sprintf($div_n, 2, '100%', 'order:3;');
                $htm .= sprintf($div_n, 3, '50%', 'order:2;');
                break;
            case '8-3':
            case '13-3':
                $htm .= $flex;
                $htm .= sprintf($div_n, 1, '50%', 'order:2;');
                $htm .= sprintf($div_n, 2, '100%', 'order:1;');
                $htm .= sprintf($div_n, 3, '50%', 'order:3;');
                break;
            case '14-2':
                $htm .= $block;
                $htm .= sprintf($div_n, 1, '50%', '');
                $htm .= sprintf($div_n, 2, '50%', '');
                $htm .= sprintf($div_n, 3, '50%', $clear);
                $htm .= sprintf($div_n, 4, '50%', '');
                break;
            case '15-2':
                $htm .= $block;
                $htm .= sprintf($div_n, 1, '50%', '');
                $htm .= sprintf($div_n, 2, '50%', '');
                $htm .= sprintf($div_n, 3, '33.33%', $clear);
                $htm .= sprintf($div_n, 4, '33.33%', '');
                $htm .= sprintf($div_n, 5, '33.33%', '');
                break;
            case '15-3':
                $htm .= $block;
                $htm .= sprintf($div_n, 1, '33.33%', '');
                $htm .= sprintf($div_n, 2, '33.33%', '');
                $htm .= sprintf($div_n, 3, '33.33%', '');
                $htm .= sprintf($div_n, 4, '50%', $clear);
                $htm .= sprintf($div_n, 5, '50%', '');
                break;
            case '15-4':
                $htm .= $block;
                $htm .= sprintf($div_n, 1, '100%', '');
                $htm .= sprintf($div_n, 2, '50%', $clear);
                $htm .= sprintf($div_n, 3, '50%', '');
                $htm .= sprintf($div_n, 4, '50%', $clear);
                $htm .= sprintf($div_n, 5, '50%', '');
                break;
            case '15-5':
                $htm .= $block;
                $htm .= sprintf($div_n, 1, '50%', '');
                $htm .= sprintf($div_n, 2, '50%', '');
                $htm .= sprintf($div_n, 3, '50%', $clear);
                $htm .= sprintf($div_n, 4, '50%', '');
                $htm .= sprintf($div_n, 5, '100%', $clear);
                break;
        }

        return $htm;
    }

    public static function getCreateCss($stylesRawArray, $mediaSizesArr, $widgets = array(), $page = '', $all = true)
    {
        $css = '';
        $tab = '  ';
        $displacement = '';
        $byMedia = array();
        $areaArr = array();
        if (!is_array($widgets) && !$widgets) {
            $widgets = array();
        } elseif (is_string($widgets) && $widgets) {
            $widgets = array($widgets);
        }

        if ($all && $page) {
            $areaArr[] = '';
            $areaArr[] = $page;
            foreach ($widgets as $widget) {
                $areaArr[] = $widget;
            }
            foreach ($widgets as $widget) {
                $areaArr[] = $page . ' ' . $widget;
            }
        } elseif (count($widgets) > 0 && $page) {
            foreach ($widgets as $widget) {
                $areaArr[] = $page . ' ' . $widget;
            }
        } elseif ($page) {
            $areaArr[] = $page;
        } elseif (count($widgets) > 0) {
            foreach ($widgets as $widget) {
                $areaArr[] = $widget;
            }
        }

        $area = "'" . implode("','", $areaArr) . "'";

        foreach ($stylesRawArray as $item) {
            $vArr = self::vArr($item['visibility']);
            $visibility = '';
            foreach ($vArr as $vKey => $vItem) {
                if ($vItem > 10) {
                    $visibility = $vItem;
                    unset($vArr[$vKey]);
                }
            }
            if (
                self::$cssFrontend &&
                $item['accessibility'] &&
                (strpos($item['accessibility'], '.b-') === 0 || strpos($item['accessibility'], '.s-') === 0 ) &&
                strpos($item['selector'], $item['accessibility']) !== false
            ) {
                $item['selector'] = trim(str_replace($item['accessibility'], '', $item['selector']));
            }
            if (count($vArr) > 0) {
                $selectorArr = explode(',', $item['selector']);
                foreach ($selectorArr as $sItem => $class) {
                    if (in_array(2, $vArr)){
                        $selectorArr[$sItem] .= '.active';
                    }
                    if (in_array(3, $vArr)){
                        $selectorArr[$sItem] .= ':before';
                    }
                    if (in_array(4, $vArr)){
                        $selectorArr[$sItem] .= ':after';
                    }
                    if (in_array(1, $vArr)){
                        $selectorArr[$sItem] .= ':hover';
                    }
                }
                $item['selector'] = implode(', ', $selectorArr);
            }

            if ($visibility) {
                $byMedia['visibility'][$visibility][$item['selector']][$item['attribute']] = $item['value'];
            } elseif ($item['media']) {
                $byMedia['media'][$item['media']][$item['selector']][$item['attribute']] = $item['value'];
            } else {
                $byMedia['general'][$item['selector']][$item['attribute']] = $item['value'];
            }
        }


        $cssArr = [
            'general' => '',
            'visibility' => '',
            'media' => ''
        ];

        foreach ($byMedia as $key => $item) {
            if ($key == 'general') {
                $cssArr['general'] = $cssArr['general'] . self::getCssMedia($item, '', $tab, $displacement);
            } elseif ($key == 'visibility') {

                $mediaSizes = [];
                foreach ($mediaSizesArr as $mediaSize) {
                    $arr2 = explode('w', $mediaSize['setting_value']);
                    $mediaSizes[(int)$arr2[1]] = $mediaSize['id'];
                }
                krsort($mediaSizes);
                foreach ($mediaSizes as $media => $mediaId) {
                    $arr = $item[$mediaId];
                    $arr2 = explode('w', $media);
                    $mediaStr = '';
                    if ($arr2[0]){
                        $mediaStr .= '(min-width:' . $arr2[0] . 'px)';
                    }
                    if ($arr2[0] && $arr2[1]){
                        $mediaStr .= ' and ';
                    }
                    if ($arr2[1]){
                        $mediaStr .= '(max-width:' . $arr2[1] . 'px)';
                    }
                    $cssArr['visibility'] = $cssArr['visibility'] . self::getCssMedia($arr, $mediaStr, $tab, $displacement);
                }


            } elseif ($key == 'media') {
                foreach ($item as $media => $arr) {
                    $cssArr['media'] = $cssArr['media'] . self::getCssMedia($arr, $media, $tab, $displacement);
                }
            }
        }

        $css .= $cssArr['general'];
        $css .= $cssArr['visibility'];
        $css .= $cssArr['media'];

        return $css;
    }

    public static function getThemeMedia($themeName, $id = true)
    {
        static $mediaSizes = [];
        if (count($mediaSizes)) {
            return $mediaSizes[$id];
        }

        $mediaSizesArr = ThemesSettings::find()
            ->where([
                'theme_name' => $themeName,
                'setting_name' => 'media_query',
            ])
            ->asArray()->all();
        foreach ($mediaSizesArr as $size) {
            $mediaSizes[true][$size['id']] = $size['setting_value'];
            $mediaSizes[false][$size['setting_value']] = $size['id'];
        }

        return $mediaSizes[$id];
    }

    public static function mainStyles($themeName)
    {
        static $styles = [];
        $styles[$themeName] = false;
        if ($styles[$themeName]) {
            return $styles[$themeName];
        }
        $styles[$themeName] = [];

        $themesStylesMain = ThemesStylesMain::find()->where(['theme_name' => $themeName])->asArray()->all();
        foreach ($themesStylesMain as $style) {
            $styles[$themeName]['$' . $style['name']] = $style['value'];
        }

        return $styles[$themeName];
    }

    private static function flushCacheTheme($themeName, $needDelete = true)
    {
        $themeMobile = $themeName . '-mobile';
        if ($needDelete) {
            \common\models\DesignBoxesCache::deleteAll(['or', ['theme_name' => $themeName], ['theme_name' => $themeMobile]]);
        }
        self::createCache($themeName, false, $needDelete);
        self::createCache($themeMobile, false, $needDelete);
    }

    public static function flushCacheAll()
    {
        $themes = \common\models\Themes::find()->asArray()->all();

        $dbArr = [
            'theme_name' => $themes[0]['theme_name'],
            'setting_group' => 'hide',
            'setting_name' => 'flush_cache_stamp',
        ];

        $setting = \common\models\ThemesSettings::findOne($dbArr);

        if ($setting && $setting->setting_value + 300 > time()) {
            return false;
        }
        \common\models\ThemesSettings::deleteAll($dbArr);

        $setting = new \common\models\ThemesSettings();
        $setting->theme_name = $themes[0]['theme_name'];
        $setting->setting_group = 'hide';
        $setting->setting_name = 'flush_cache_stamp';
        $setting->setting_value = time();
        $setting->save();

        // speed up
        \Yii::$app->db->createCommand()->truncateTable(\common\models\DesignBoxesCache::tableName())->execute();
        \Yii::$app->db->createCommand()->truncateTable(\common\models\ThemesStylesCache::tableName())->execute();

        foreach ($themes as $theme) {
            self::flushCacheTheme($theme['theme_name'], false);
        }

        \common\models\ThemesSettings::deleteAll($dbArr);
        return true;
    }

    private static $needResetStyleCache = false;
    public static function invalidateCache() {
        self::$needResetStyleCache = true;
    }

    public static function validateCache() {
        if (self::$needResetStyleCache) {
            self::flushCacheAll();
            self::$needResetStyleCache = false;
        }
    }

}