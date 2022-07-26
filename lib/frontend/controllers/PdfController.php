<?php
namespace frontend\controllers;

use Yii;

/**
 * Site controller
 */
class PdfController extends Sceleton
{
	public function actionIndex()
	{


        $this->layout = false;

        if (\frontend\design\Info::isAdmin()) {
            return $this->render('index.tpl', [
                'page_name' => 'blog'
            ]);
        } else {
            \backend\design\PDFBlock::widget([
                'pages' => [
                    [
                        'name' => 'pdf_cover',
                        'theme_name' => 'theme-1',
                        'params' => [
                            'language_id' => '1'
                        ],
                    ],
                    [
                        'name' => 'pdf',
                        'theme_name' => 'theme-1',
                        'params' => [
                            'language_id' => '1'
                        ],
                    ]
                ],
                'params' => [
                    'theme_name' => THEME_NAME,
                    'document_name' => 'pdf',
                ]
            ]);
        }
	}

    public function actionCover()
    {


        $this->layout = false;

        if (\frontend\design\Info::isAdmin()) {
            return $this->render('cover.tpl', [
                'page_name' => 'blog'
            ]);
        } else {
            \backend\design\PDFBlock::widget([
                'pages' => [
                    [
                        'name' => 'pdf_cover',
                        'theme_name' => 'theme-1',
                        'params' => [
                            'language_id' => '1'
                        ],
                    ],
                    [
                        'name' => 'pdf',
                        'theme_name' => 'theme-1',
                        'params' => [
                            'language_id' => '1'
                        ],
                    ]
                ],
                'params' => [
                    'theme_name' => THEME_NAME,
                    'document_name' => 'pdf',
                ]
            ]);
        }
    }
}
