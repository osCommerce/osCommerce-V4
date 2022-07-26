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


class ProductLabel
{

    protected static function getLogoData()
    {
        $platform = \common\models\Platforms::find()
            ->alias('p')
            ->leftJoin(['p2t'=>'platforms_to_themes'], 'p.platform_id=p2t.platform_id AND p2t.is_default=1')
            ->select(['p.logo', 'p2t.theme_id'])
            ->where(['p.platform_id' => \common\classes\platform::defaultId()])
            ->asArray()
            ->one();

        if ($platform['logo'] && is_file(DIR_FS_CATALOG . $platform['logo'])) {
            $image = $platform['logo'];
        }
        $theme = \common\models\Themes::findOne($platform['theme_id']);
        if ( $theme && $theme->theme_name ) {
            $image = \frontend\design\Info::themeSetting('logo', 'hide', $theme->theme_name);
        }
        if ( is_file(DIR_FS_CATALOG . $image) ){
            return '@'.base64_encode(file_get_contents(DIR_FS_CATALOG . $image));
        }
        return '';
    }

    protected static function getStoreName()
    {
        /**
         * @var $platformConfig \common\classes\platform_config
         */
        $platformConfig = \Yii::$app->get('platform')->getConfig(\common\classes\platform::defaultId());
        return $platformConfig->const_value('STORE_NAME');
    }

    public static function label($text, $count=1)
    {
        $labelSize = [89, 36];
        $pdf = new \TCPDF('L', 'mm', $labelSize);
        $pdf->setViewerPreferences(array("PrintScaling" => "None"));
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetFont('arial', '', 36);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        for ( $i=1; $i<=max(1,$count); $i++ ) {
            $pdf->addPage();
            $pdf->SetFont('arial', '', 36);

            //$pdf->Cell($labelSize[0]-0.1, $labelSize[1]-0.1,'',1);

            $brandingLogo = static::getLogoData();
            $brandingText = static::getStoreName();

            if ($brandingLogo) {
                $pdf->writeHTMLCell(12, 12, 2, 3, '<img src="' . $brandingLogo . '" width="12mm"/>');
            }
            $pdf->writeHTMLCell($labelSize[0] - 10, 10, 10, 4, '<div style="font-size: 12pt; text-align: center">' . $brandingText . '</div>');

            //$pdf->Rect(1,11,$labelSize[0]-2,14);

            $pdf->SetFont('arial', 'B', 36);
            $pdf->MultiCell($labelSize[0]-2, 16, $text, 0, 'C', false, 1, 1, 10, true, 0, false, true, 16, 'M', true);

            $barcodeSize = [38, 7.5];
            $pdf->write1DBarcode($text, 'C128', $labelSize[0] / 2 - $barcodeSize[0] / 2, 25, $barcodeSize[0], $barcodeSize[1]);
        }

        return $pdf->Output('','S');
        //$pdf->Output(preg_replace('/[^\da-z-_]+/i','_',$text).'.pdf');
    }

}