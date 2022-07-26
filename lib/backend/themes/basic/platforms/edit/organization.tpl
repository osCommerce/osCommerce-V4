{use class="yii\helpers\Html"}
<div class="widget box box-no-shadow">
    <div class="widget-header "><h4>{$smarty.const.TEXT_ORGANIZATION}</h4></div>
    <div class="widget-content">

        <div class="w-line-row w-line-row-1">
            <div class="wl-td">
                <label>{$smarty.const.TEXT_ORGANIZATION_SITE}:</label>
                {Html::input('text', 'organization_site', $pInfo->organization_site|default:null, ['class' => 'form-control'])}
            </div>
        </div>
        <div class="w-line-row w-line-row-1">
            <div class="wl-td">
                <label>{$smarty.const.TEXT_ORGANIZATION_TYPE}:</label>
                {Html::dropDownList('organization_type', $pInfo->organization_type|default:null, $organization_types, ['class' => 'form-control'])}
            </div>
        </div>
        <div class="w-line-row w-line-row-1">
            <div class="wl-td platform-logo-box">
                <label>{$smarty.const.TEXT_LOGO_WIDGET}:</label>
                <div class="">
                    {\backend\design\Image::widget([
                    'name' => 'logo',
                    'value' => {$pInfo->logo|default:null},
                    'upload' => 'logo_upload',
                    'delete' => 'image_delete'
                    ])}
                </div>
            </div>
        </div>

    </div>
</div>