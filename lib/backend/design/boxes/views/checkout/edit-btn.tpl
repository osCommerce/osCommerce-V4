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
                        <label for="">Choose data</label>
                        <select name="setting[0][link]" id="" class="form-control">
                            <option value=""{if $settings[0].link == ''} selected{/if}></option>
                            <option value="shipping_address"{if $settings[0].link == 'shipping_address'} selected{/if}>{$smarty.const.ENTRY_SHIPPING_ADDRESS}</option>
                            <option value="billing_address"{if $settings[0].link == 'billing_address'} selected{/if}>{$smarty.const.TEXT_BILLING_ADDRESS}</option>
                            <option value="shipping_method"{if $settings[0].link == 'shipping_method'} selected{/if}>{$smarty.const.TEXT_CHOOSE_SHIPPING_METHOD}</option>
                            <option value="payment_method"{if $settings[0].link == 'payment_method'} selected{/if}>{$smarty.const.TEXT_SELECT_PAYMENT_METHOD}</option>
                            <option value="contact_information"{if $settings[0].link == 'contact_information'} selected{/if}>{$smarty.const.CATEGORY_CONTACT}</option>
                            <option value="comments"{if $settings[0].link == 'comments'} selected{/if}>{$smarty.const.TABLE_HEADING_COMMENTS}</option>
                            <option value="products"{if $settings[0].link == 'products'} selected{/if}>{$smarty.const.TABLE_HEADING_PRODUCTS}</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LINK_TEXT}</label>
                        <input type="text" name="setting[0][text]" value="{$settings[0].text}" id="" class="form-control" style="width: 243px">
                    </div>
                    <div class="setting-row">
                        <label for="">{$smarty.const.TEXT_LIKE_BUTTON}</label>
                        <select name="setting[0][like_button]" id="" class="form-control">
                            <option value=""{if $settings[0].like_button == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                            <option value="1"{if $settings[0].like_button == '1'} selected{/if}>{$smarty.const.HEADING_TYPE} 1</option>
                            <option value="2"{if $settings[0].like_button == '2'} selected{/if}>{$smarty.const.HEADING_TYPE} 2</option>
                            <option value="3"{if $settings[0].like_button == '3'} selected{/if}>{$smarty.const.HEADING_TYPE} 3</option>
                            <option value="4"{if $settings[0].like_button == '4'} selected{/if}>{$smarty.const.HEADING_TYPE} 4</option>
                        </select>
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