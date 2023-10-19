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

namespace frontend\design\boxes;

use common\classes\Images;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class SocialLinks extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $socials = \common\models\Socials::find()
            ->select(['link', 'image', 'css_class', 'name' => 'module'])
            ->where(['!=', 'link', ''])
            ->andWhere(['platform_id' => \common\classes\platform::currentId()])
            ->asArray()
            ->all();

        if (!$socials) {
            return '';
        }

        foreach ($socials as $key => $social) {

            \frontend\design\JsonLd::addData(['Organization' => [
                'sameAs' => [$social['link']]
            ]], ['Organization', 'sameAs']);

            if (is_file(Images::getFSCatalogImagesPath() . $socials[$key]['image'])) {
                $socials[$key]['image'] = DIR_WS_IMAGES . \common\classes\Images::getWebp($social['image']);
            } else {
                $socials[$key]['image'] = '';
            }
        }

        return IncludeTpl::widget([
            'file' => 'boxes/social-links.tpl',
            'params' => [
                'settings' => $this->settings,
                'socials' => $socials,
            ],
        ]);
    }

}