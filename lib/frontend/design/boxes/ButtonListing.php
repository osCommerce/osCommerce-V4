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
declare(strict_types=1);

namespace frontend\design\boxes;

use common\services\FileService;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class ButtonListing extends Widget
{
    public $file;
    public $params;
    public $settings;
    /** @var FileService */
    private $fileService;
    private static $classes;

    /**
     * ButtonListing constructor.
     * @param FileService $fileService
     * @param array $config
     */
    public function __construct(
        FileService $fileService,
        array $config = []
    )
    {
        parent::__construct($config);

        if (self::$classes === null) {
            $classes = $fileService->getClassesIterator([__DIR__.'/product'], static function (string $className){
                try{
                    $object = \Yii::createObject($className);
                    return  $object instanceof ButtonListingInterface && $object->isAllowed()
                        ? ['class' => $className, 'priority' => $object->getPriority()]
                        : false;
                } catch (\Exception $e) {
                    return false;
                }
            }, 0);
            self::$classes = [];
            if ($classes) {
                foreach ($classes as $class) {
                    self::$classes[] = $class;
                }
                usort(self::$classes, static function ($a, $b){
                    return $a['priority'] <=> $b['priority'];
                });
            }
        }
    }

    public function init()
    {
        parent::init();

    }

    public function run(): string
    {
        if (!self::$classes) {
            return '';
        }
        $widgetsHtml = [];
        foreach (self::$classes as $class) {
            $widgetsHtml[] = call_user_func($class['class'] .'::widget',[
                'params' => $this->params,
                'settings' => [],
            ]);
        }
        return IncludeTpl::widget(['file' => 'boxes/product/button-listing.tpl', 'params' => [
            'widgetsHtml' => $widgetsHtml,
        ]]);
    }

}
