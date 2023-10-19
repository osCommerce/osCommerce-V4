{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TABLE_TEXT_NAME}
    </div>
    <div class="popup-content">




        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TABLE_TEXT_NAME}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">
                <div class="tab-pane active menu-list" id="type">




                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_CHOOSE_PAGE}</label>
                        <select name="setting[0][link]" id="" class="form-control">
                            <option value=""{if $settings[0].link == ''} selected{/if}>{$smarty.const.TEXT_DASHBOARD}</option>
                            <option value="logoff"{if $settings[0].link == 'logoff'} selected{/if}>{$smarty.const.HEADER_TITLE_LOGOFF}</option>
                            {foreach $links as $link}
                                <option value="{$link}"{if $settings[0].link == $link} selected{/if}>{$link}</option>
                            {/foreach}
                            <option value="personal_catalog"{if $settings[0].link == 'personal-catalog'} selected{/if}>{$smarty.const.TEXT_PERSONAL_CATALOG}</option>
                            <option value="bonus_program"{if $settings[0].link == 'bonus_program'} selected{/if}>{$smarty.const.TEXT_BONUS_PROGRAM}</option>
                            <option value="download_orders"{if $settings[0].link == 'download_orders'} selected{/if}>download_orders</option>
                            <option value="delete"{if $settings[0].link == 'delete'} selected{/if}>Delete account</option>
                            <option value="trade_form"{if $settings[0].link == 'trade_form'} selected{/if}>Trade form</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LINK_TEXT}</label>
                        <input type="text" name="setting[0][text]" value="{$settings[0].text}" id="" class="form-control" style="width: 243px">
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LIKE_BUTTON}</label>
                        <select name="setting[0][like_button]" id="" class="form-control">{if !isset($settings[0].like_button)}{$settings[0].like_button = ''}{/if}
                            <option value=""{if $settings[0].like_button == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].like_button == '1'} selected{/if}>{$smarty.const.HEADING_TYPE} 1</option>
                            <option value="2"{if $settings[0].like_button == '2'} selected{/if}>{$smarty.const.HEADING_TYPE} 2</option>
                            <option value="3"{if $settings[0].like_button == '3'} selected{/if}>{$smarty.const.HEADING_TYPE} 3</option>
                            <option value="4"{if $settings[0].like_button == '4'} selected{/if}>{$smarty.const.HEADING_TYPE} 4</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_OPEN_IN_POPUP}</label>
                        <select name="setting[0][popup]" id="" class="form-control">{if !isset($settings[0].popup)}{$settings[0].popup = ''}{/if}
                            <option value=""{if $settings[0].popup == ''} selected{/if}>{$smarty.const.CURRENT_WINDOW}</option>
                            <option value="1"{if $settings[0].popup == '1'} selected{/if}>{$smarty.const.TEXT_POP_UP}</option>
                            <option value="2"{if $settings[0].popup == '2'} selected{/if}>{$smarty.const.TEXT_OPEN_NEW_TAB}</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.HIDE_IF_EMPTY}</label>
                        <select name="setting[0][hide_link]" id="" class="form-control">
                            <option value=""{if $settings[0].hide_link == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="credit_amount_history"{if $settings[0].hide_link == 'credit_amount_history'} selected{/if}>{$smarty.const.CREDIT_AMOUNT_HISTORY}</option>
                            <option value="points_earnt_history"{if $settings[0].hide_link == 'points_earnt_history'} selected{/if}>{$smarty.const.POINTS_EARNT_HISTORY}</option>
                            <option value="wishlist"{if $settings[0].hide_link == 'wishlist'} selected{/if}>{$smarty.const.TEXT_WISHLIST}</option>
                            {if (\common\helpers\Acl::checkExtensionAllowed('Quotations'))}
                              <option value="quotations"{if $settings[0].hide_link == 'quotations'} selected{/if}>{$smarty.const.BOX_CUSTOMERS_QUOTATIONS}</option>
                            {/if}
                            {if (\common\helpers\Acl::checkExtensionAllowed('Samples'))}
                              <option value="samples"{if $settings[0].hide_link == 'samples'} selected{/if}>{$smarty.const.BOX_CUSTOMERS_SAMPLES}</option>
                            {/if}
                            <option value="review"{if $settings[0].hide_link == 'review'} selected{/if}>{$smarty.const.BOX_CATALOG_REVIEWS}</option>
                            <option value="personal_catalog"{if $settings[0].hide_link == 'personal-catalog'} selected{/if}>{$smarty.const.TEXT_PERSONAL_CATALOG}</option>
                            <option value="orders"{if $settings[0].hide_link == 'orders'} selected{/if}>{$smarty.const.BOX_CUSTOMERS_ORDERS}</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.SHOW_AS_ACTIVE_ON_PAGES}</label>
                        <select name="setting[0][active_link]" id="" class="form-control" multiple style="height: 100px;">
                            <option value=""{if $settings[0].active_link == ''} selected{/if}></option>
                            {foreach $activeLinks as $link}
                                <option value="{$link.name}"{if $link.active} selected{/if}>{$link.name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.ADD_ORDER_ID_TO_LINK}</label>
                        <select name="setting[0][order_id]" id="" class="form-control">
                            <option value=""{if $settings[0].order_id == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].order_id == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_POPUP_CLASS}</label>
                        <input type="text" name="setting[0][popup_class]" value="{$settings[0].popup_class}" id="" class="form-control" style="width: 243px">
                    </div>






                </div>
                <div class="tab-pane" id="style">
                    {include '../include/style.tpl'}
                </div>
                <div class="tab-pane" id="align">
                    {include '../include/align.tpl'}
                </div>
                <div class="tab-pane" id="visibility">
                    {include '../include/visibility.tpl'}
                </div>

            </div>
        </div>


    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</form>
