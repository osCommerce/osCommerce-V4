{assign var=canChangeOwner value=\common\helpers\Acl::rule(['ACL_ORDER', 'TEXT_ORDER_OWNER'])}
<div class="widget box box-no-shadow">
        <div class="widget-header widget-header-platform"><h4><i class="icon-frontends"></i>{$smarty.const.TEXT_ORDER_OWNER}</h4></div>
        <div class="widget-content">
            <div class="w-line-row w-line-row-1 wbmbp1">
                <div>{$smarty.const.TEXT_ORDER_BUSSY_BY} {$name}. {if $canChangeOwner}{$smarty.const.TEXT_ORDER_CHANGE_OWNER}{else}{$smarty.const.TEXT_CANT_EDIT_NOW}{/if}</div>
            </div>
{if $canChangeOwner}
            <div class="w-line-row w-line-row-1 wbmbp1">
                <div><b>{$smarty.const.TEXT_DIFFERENCE}:</b></div>
            </div>
            {foreach $changesList as $changesLine}
                <div class="w-line-row w-line-row-1 wbmbp1">
                    <div>{$changesLine}</div>
                </div>
            {/foreach}
{/if}
        </div>
        <div class = "noti-btn" style="margin:0px;">
{if $canChangeOwner}
            <div class="btn-left" style="width: 19%;"><a href="{$cancel}"  class="btn btn-default">{$smarty.const.TEXT_BTN_NO}</a></div>
            <div class="btn-right" style="width: 79%;">
                <a href="javascript:void(0)"  class="btn btn-primary btn-confirm-owner">{$smarty.const.TEXT_BTN_YES}, {$smarty.const.TEXT_APPLY_CHANGES}</a>
                <a href="javascript:void(0)"  class="btn btn-primary btn-discard-owner">{$smarty.const.TEXT_BTN_YES}, {$smarty.const.TEXT_DISCARD_CHANGES}</a>
            </div>
{else}
            <div class="btn-right"><a href="{$cancel}"  class="btn btn-default">{$smarty.const.TEXT_BTN_OK}</a></div>
{/if}
        </div>
        <script>
            $(document).ready(function () {
                $('.pop-up-close:last').hide();
                $('.btn-confirm-owner').click(function() {
                    $.post("{\yii\helpers\Url::to(['editor/owner', 'currentCurrent' => $currentCurrent])}", {
                        'action': 'confirm'
                    }, function (data, status) {
                        if (status == 'success') {
                            if (data.reload) {
                                window.location.href='{$redirect}';
                            }
                        }
                    }, 'json');
                });
                $('.btn-discard-owner').click(function() {
                    $.post("{\yii\helpers\Url::to(['editor/owner', 'currentCurrent' => $currentCurrent])}", {
                        'action': 'discard'
                    }, function (data, status) {
                        if (status == 'success') {
                            if (data.reload) {
                                window.location.reload();
                            }
                        }
                    }, 'json');
                });
            })
        </script>
    </div>