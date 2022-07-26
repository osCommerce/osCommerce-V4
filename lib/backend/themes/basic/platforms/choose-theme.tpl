{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}

{$message}

<div id="platforms_management_data">
    <form action="" name="save_item_form" id="save_item_form" enctype="multipart/form-data" onsubmit="return saveItem();">

        <div class="widget box box-no-shadow">
            <div class="widget-header widget-header-theme"><h4>{$smarty.const.CATEGORY_ASSIGNED_THEME}</h4></div>
            <div class="widget-content">
                <div class="w-line-row w-line-row-2-big">
                    <div class="theme_wr">
                        {foreach $theme_array as $res}
                            <div class="theme_title act">
                                {if $res.theme_image}
                                    <img width="100" height="80" src="{DIR_WS_CATALOG}{$res.theme_image}">
                                {else}
                                    <img width="100" height="80" src="{DIR_WS_CATALOG}themes/{$res.theme_name}/screenshot.png">
                                {/if}
                                <div class="theme_title2">{$res.title}</div>
                            </div>
                            {foreachelse}
                            <div class="theme_title">{$smarty.const.TEXT_NOT_CHOOSEN}</div>
                        {/foreach}
                        <a href="{Url::to(['addtheme'])}" class="btn popup">{$smarty.const.TEXT_CHOOSE_THEME}</a>
                    </div>
                </div>
            </div>
        </div>

        {Html::input('hidden', 'id', $pInfo->platform_id)}
        {Html::input('hidden', 'theme_id', $pInfo->theme_id)}
        <div class="btn-bar">
            <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
            <div class="btn-right"><button type="submit" class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
        </div>
    </form>

</div>

<script>
    function saveItem() {
        $.post("{Url::current()}", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#platforms_management_data').html(data);
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }
    function backStatement() {
        window.history.back();
        return false;
    }

    $(document).ready(function(){
        $('.theme_wr .popup').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box theme_popup'><div class='pop-up-close pop-up-close-alert'></div><div class='popup-heading theme_choose'>{$smarty.const.TEXT_CHOOSE_THEME}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });
    })
</script>