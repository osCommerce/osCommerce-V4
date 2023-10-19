{use class="yii\helpers\Html"}
{use class='yii\widgets\ActiveForm' type='block'}
<!-- Page Header START -->
<div class="page-header" style="display: block;">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- Page Header END -->
<div class="">
    <a href="{Yii::$app->urlManager->createUrl(['catalog-pages/','platform_id'=> $platform_id, 'parent_id'=> $parent_parent_id, 'item_id'=> $parent_id])}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
    <div style="display: inline-block; margin-left: 1em;">{$breadcrumbs}</div>
</div>
<div class="location-edit-content">
    {ActiveForm assign='form' id='frm' options=['class' => '']}
        {Html::input('hidden', 'platform_id', $platform_id, ['id' => "platform_id"])}
        {Html::input('hidden', 'parent_id', $parent_id, ['id' => "parent_id"])}
        <div class="prop_wrapper">
            <div class="tabbable tabbable-custom">
                <ul class="nav nav-tabs">
                    <li class="active" data-bs-toggle="tab" data-bs-target="#tab_main"><a><span>{$smarty.const.TEXT_NAME_DESCRIPTION}</span></a></li>
                    <li data-bs-toggle="tab" data-bs-target="#tab_details"><a><span>{$smarty.const.TEXT_MAIN}</span></a></li>
                    <li data-bs-toggle="tab" data-bs-target="#tab_seo"><a><span>{$smarty.const.TEXT_SEO}</span></a></li>
                    <li data-bs-toggle="tab" data-bs-target="#tab_products"><a><span>{$smarty.const.TEXT_INFORMATION}</span></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane topTabPane tabbable-custom active" id="tab_main">
                        {include file="./edit/description.tpl"}
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_details">
                        {include file="./edit/details.tpl"}
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_seo">
                        {include file="./edit/seo.tpl"}
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_products">
                        {include file="./edit/info.tpl"}
                    </div>
                </div>
            </div>
        </div>
        <div class="btn-bar edit-btn-bar">
            <div class="btn-left">
                <a href="{Yii::$app->urlManager->createUrl(['catalog-pages/','platform_id'=> $platform_id, 'parent_id'=> $parent_parent_id, 'item_id'=> $parent_id])}" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a>
            </div>
            <div class="btn-right">
                <button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button>
            </div>
        </div>
    </form>
    {/ActiveForm}
</div>
<br>
<script type="text/javascript">
    function backStatement() {
        window.history.back();
        return false;
    }
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                switchChange(element, arguments);
                return true;
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '38px',
            labelWidth: '24px'
        }
    );
    $('body').on('click','#createPage',function(e){
        e.preventDefault();
        var requestParams = '&platform_id='+$('#platform_id').val()+'&parent_id='+$('#parent_id').val();
        window.location = '{Yii::$app->urlManager->createUrl(['catalog-pages/edit','id' => 0])}'+requestParams;
        return false;
    });
</script>
