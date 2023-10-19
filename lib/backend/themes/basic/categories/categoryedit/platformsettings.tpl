{*
This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce

@link https://www.oscommerce.com
@copyright Copyright (c) 2000-2022 osCommerce LTD

Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
*}


{use class="common\helpers\Html"}


{if $app->controller->view->platformsettings_tabs|@count > 0 }
  {$tabparams = $app->controller->view->platformsettings_tabparams}
  {$tabparams[count($tabparams)-1]['callback'] = 'categoryPlatformSettings'}
  {$id_prefix = 'platformSettings'}

  {call mTab tabs=$app->controller->view->platformsettings_tabs tabparams=$tabparams  fieldsData=$app->controller->view->platformsettings_tabs_data  id_prefix = $id_prefix}

{else}
  {call categoryPlatformSettings data=$app->controller->view->platformsettings_tabs_data  id_prefix = 'platformSettings'}
{/if}


{function categoryPlatformSettings }

    <div class="settings-override {if !isset($data['maps_id'])}dis_module{/if}" id="settings-override{$idSuffix}" >
      {if $data.platform_id!=0}
      <div class="md_row after">
        <label for="plaformsettings{$idSuffix}">{$smarty.const.TEXT_OVERRIDE}</label>
        <div class="md_value">
          {Html::checkbox("plaformsettings$fieldSuffix", isset($data['maps_id']),
                          ['class' => "check_on_off plaformsettings",
                           'id' => "plaformsettings{$idSuffix}",
                           'data-idSuffix' => {$idSuffix},
                           'value' => 1,
                           'onchange' => {"plaformsettingsChange('$idSuffix')"}])}
        </div>
      </div>
      {else}
        {Html::hiddenInput("plaformsettings$fieldSuffix", 0)}
      {/if}
      <div class="row">
        <div class="col-md-6">
            <div class="widget box">
                <div class="widget-header">
                    <h4>{$smarty.const.TEXT_GALLERY_IMAGE}</h4>
                </div>
                <div class="widget-content">
                    <div class="about-image">
                        <div class="about-image-scheme-1">
                            <div></div><div></div><div></div><div></div><div></div><div></div>
                        </div>
                        <div class="about-image-text">
                            {$smarty.const.TEXT_GALLERY_IMAGE_INTRO}
                            <ul>
                                <li>{$smarty.const.TEXT_IMAGE_INTRO_LINE1}</li>
                                <li>{$smarty.const.TEXT_IMAGE_INTRO_LINE2}</li>
                                <li>{$smarty.const.TEXT_IMAGE_INTRO_LINE3}</li>
                            </ul>
                        </div>
                    </div>
                    {if !isset($data['categories_image'])}{$data['categories_image'] = ''}{/if}
                    {\backend\design\Image::widget([
                        'name' => {"categories_image$fieldSuffix"},
                        'value' => {$data['categories_image']|escape},
                        'upload' => {"categories_image_loaded$fieldSuffix"},
                        'delete' => {"delete_image$fieldSuffix"}
                    ])}
                </div>
                <div class="divider"></div>
            </div>

            <div class="widget box">
                <div class="widget-header">
                    <h4>{$smarty.const.TEXT_HOMEPAGE_IMAGE}</h4>
                </div>
                <div class="widget-content">
                    <div class="about-image">
                        <div class="about-image-scheme-1">
                            <div></div><div></div><div></div><div></div><div></div><div></div>
                        </div>
                        <div class="about-image-text">
                            {$smarty.const.TEXT_HOMEPAGE_IMAGE_INTRO}
                        </div>
                    </div>
                    {if !isset($data['categories_image_3'])}{$data['categories_image_3'] = ''}{/if}
                    {\backend\design\Image::widget([
                    'name' => {"categories_image_3$fieldSuffix"},
                    'value' => {$data['categories_image_3']|escape},
                    'upload' => {"categories_image_loaded_3$fieldSuffix"},
                    'delete' => {"delete_image_3$fieldSuffix"}
                    ])}
                </div>
                <div class="divider"></div>
            </div>

            <div class="widget box">
                <div class="widget-header">
                    <h4>{$smarty.const.IMAGE_MAP}</h4>
                </div>
                <div class="widget-content">
                    <div class="category-image-map form-container">
                        <div class="row">

                            <div class="col-md-3" style="padding: 20px 0">
                                <label for="">{$smarty.const.IMAGE_MAP_NAME}</label>
                            </div>
                            <div class="col-md-6" style="padding: 20px 0">
                                {if !isset($data.imageMapTitle.title)}{$data.imageMapTitle.title = ''}{/if}{Html::textInput('', $data.imageMapTitle.title, ['class' => "map-name", 'id' => "map_name$idSuffix", 'data-idsuffix' => "$idSuffix"])}
                                {if !isset($data['maps_id'])}{$data['maps_id'] = ''}{/if}{Html::hiddenInput("maps_id$fieldSuffix", $data['maps_id'], ['id' => "map_id$idSuffix"])}
                                <div class="search-map" id="search_map{$idSuffix}" data-idsuffix="{$idSuffix}"></div>
                            </div>
                            {if isset($data.imageMap.image)}
                                <div class="col-md-3">
                                    <div class="map-image-holder">
                                        <img src="../images/maps/{$data.imageMap.image}" class="map-image"  id="map_image{$idSuffix}" alt="" data-idsuffix='{$idSuffix}'
                                                {if !$data.imageMap.image} style="display: none" {/if}>
                                        <div class="map-image-remove" id="map_image_remove{$idSuffix}" data-idsuffix='{$idSuffix}'
                                                {if !$data.imageMap.image} style="display: none" {/if}></div>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
                <div class="divider"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="widget box">
                <div class="widget-header">
                    <h4>{$smarty.const.TEXT_HERO_IMAGE}</h4>
                </div>
                <div class="widget-content">
                    <div class="about-image">
                        <div class="about-image-scheme-2">
                            <div></div><div></div><div></div><div></div>
                        </div>
                        <div class="about-image-text">
                            {$smarty.const.TEXT_HERO_IMAGE_INTRO}
                            <ul>
                                <li>{$smarty.const.SHOULD_NOT_BE_TOO_SMALL}</li>
                                <li>{$smarty.const.TEXT_FORMATS}:  jpg, png, gif.</li>
                                <li>{$smarty.const.TEXT_COLOR_MODE}: RGB</li>
                            </ul>
                        </div>
                    </div>
                    {if !isset($data['categories_image_2'])}{$data['categories_image_2'] = ''}{/if}
                    {\backend\design\Image::widget([
                        'name' => {"categories_image_2$fieldSuffix"},
                        'value' => {$data['categories_image_2']|escape},
                        'upload' => {"categories_image_loaded_2$fieldSuffix"},
                        'delete' => {"delete_image_2$fieldSuffix"}
                    ])}

                    {if count($heroImages)}
                    <div class="widget box widget-closed" id="category-hero-image" style="margin: 33px 0 0">
                        <div class="widget-header">
                            <h4>{$smarty.const.CATEGORY_HERO_IMAGE_RESPONSIVE}</h4>
                            <div class="toolbar no-padding">
                                <div class="btn-group">
                                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content">
                            {foreach $heroImages as $heroImage}
                                <span class="image-size">
                                    <span class="label-image-width">Image/video width:</span>
                                    <span class="image-width">{$heroImage.image_types_x}</span>.
                                    {if $heroImage.image_types_x}
                                        <span class="label-image-height">Recommended height:</span>
                                        <span class="image-height">{$heroImage.image_types_y}</span>.
                                    {/if}
                                </span>
                                <span class="window-size">
                                    <span class="parenthesis">(</span>
                                    Window width
                                    from
                                    <span class="width-from">
                                        {if $heroImage.width_from}{$heroImage.width_from}{else}0{/if}
                                    </span>
                                    {if $heroImage.width_to}
                                        to <span class="width-to">{$heroImage.width_to}</span>
                                    {/if}
                                    <span class="parenthesis">)</span>
                                </span>
        {\backend\design\Image::widget([
            'name' => {"heroImage[{$heroImage.image_types_id}]$fieldSuffix"},
            'value' => {$heroImage['images'][$heroImage.image_types_id][$data['platform_id']]['image']},
            'upload' => {"heroImage_loaded[{$heroImage.image_types_id}]$fieldSuffix"},
            'delete' => {"heroImage_delete[{$heroImage.image_types_id}]$fieldSuffix"},
            'width' => $heroImage.image_types_x,
            'height' => $heroImage.image_types_y,
            'positionName' => {"heroImage_position[{$heroImage.image_types_id}]$fieldSuffix"},
            'positionValue' => {$heroImage['images'][$heroImage.image_types_id][$data['platform_id']]['position']},
            'fitName' => {"heroImage_fit[{$heroImage.image_types_id}]$fieldSuffix"},
            'fitValue' => {$heroImage['images'][$heroImage.image_types_id][$data['platform_id']]['fit']}
        ])}
                            {/foreach}
                        </div>
                    </div>
                    {/if}

                </div>
                <div class="divider"></div>
            </div>

            <div class="widget box">
                <div class="widget-header">
                    <h4>{$smarty.const.TEXT_MENU_IMAGE}</h4>
                </div>
                <div class="widget-content">
                    {if !isset($data['categories_image_4'])}{$data['categories_image_4'] = ''}{/if}
                    {\backend\design\Image::widget([
                    'name' => {"categories_image_4$fieldSuffix"},
                    'value' => {$data['categories_image_4']|escape},
                    'upload' => {"categories_image_loaded_4$fieldSuffix"},
                    'delete' => {"delete_image_4$fieldSuffix"}
                    ])}
                </div>
                <div class="divider"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">

            {include './images.tpl'}
        </div>

    </div>
    <div class="md_row after">
        <label for="status">{$smarty.const.TEXT_SHOW_ON_HOME_PAGE}</label>
        <div class="md_value">
          {if !isset($data['show_on_home'])}{$data['show_on_home'] = ''}{/if}{Html::checkbox("show_on_home$fieldSuffix", $data['show_on_home'], ['class' => "check_on_off show_on_home", 'id' => "show_on_home{$idSuffix}", 'value' => 1])}
        </div>
    </div>
  </div>
{/function}

<script>
  function plaformsettingsChange(id) {
    try {
      if (id != '') {
        $('#settings-override' + id).toggleClass('dis_module');
      }
    } catch (e) {
    }
  }
</script>
