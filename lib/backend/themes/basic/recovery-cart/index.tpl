{use class="yii\helpers\Html"}
<div>
    {if {$messages|@count} > 0}
        {foreach $messages as $type => $message}
            <div class="alert alert-{$type} fade in">
                <i data-dismiss="alert" class="icon-remove close"></i>
                <span id="message_plce">{$message}</span>
            </div>
        {/foreach}
    {/if}
    {Html::beginForm([{$url}], 'post', ['name' => 'recover_cart_form', 'onsubmit' => 'return saveGoogleBaseData()'])}

        <div class="tabbable tabbable-custom">
            {if $isMultiPlatform}
                <ul class="nav nav-tabs -tab-light-gray">
                    {foreach $forms as $form}
                        <li {if $selected_platform_id==$form->platform_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#pl_{$form->platform_id}"><a class="js_link_platform_select" data-platform_id="{$form->platform_id}" {if $from->platform_id==$selected_platform_id} onclick="return false" {/if}><span>{$form->platform->platform_name}</span></a></li>
                    {/foreach}
                </ul>
            {/if}
            <div class="tab-content {if $isMultiPlatform}tab-content1{/if}">
                {foreach $forms as $key => $form}
                    <div id="pl_{$form->platform_id}" class="tab-pane {if $selected_platform_id == $form->platform_id}active{/if}">
                        <div class="widget box">
                            <div class="widget-content">
                                <div class="w-line-row w-line-row-1">
                                    <div class="wl-td">
                                        {assign var=flag_name value='['|cat:$key|cat:']enable_email_delivery' }
                                        {Html::activeCheckbox($form, $flag_name, ['label' => 'Enable delivery email']) }
                                    </div>
                                </div>

                                <div class="w-line-row w-line-row-1">
                                    <div class="wl-td">
                                        <label>First letter</label>
                                        {Html::activeDropDownList($form, '['|cat:$key|cat:']first_email_start', $form->getIntervals(), [] )}
                                        <span> Coupon </span>
                                        {Html::activeDropDownList($form, '['|cat:$key|cat:']first_email_coupon_id', $coupons, [] )}
                                    </div>
                                </div>

                                <div class="w-line-row w-line-row-1">
                                    <div class="wl-td">
                                        <label>Second letter</label>
                                        {Html::activeDropDownList($form, '['|cat:$key|cat:']second_email_start', $form->getIntervals(), [] )}
                                        <span> Coupon </span>
                                        {Html::activeDropDownList($form, '['|cat:$key|cat:']second_email_coupon_id', $coupons, [] )}
                                    </div>
                                </div>

                                <div class="w-line-row w-line-row-1">
                                    <div class="wl-td">
                                        <label>Third letter</label>
                                        {Html::activeDropDownList($form, '['|cat:$key|cat:']third_email_start', $form->getIntervals(), [] )}
                                        <span> Coupon </span>
                                        {Html::activeDropDownList($form, '['|cat:$key|cat:']third_email_coupon_id', $coupons, [] )}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="btn-bar">
                <div class="btn-right">{Html::submitButton('Save', ['class' => "btn btn-confirm", 'value'  => $smarty.const.IMAGE_SAVE])}</div>
            </div>
        </div>

    <div class="row">

    </div>
    {Html::endForm()}
</div>