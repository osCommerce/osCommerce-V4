{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-personal">
        <h4 style="width:83%">{$smarty.const.T_CUSTOMER}</h4>

        {if $manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')}
            {*<span>{Html::a('Reassing', Url::to('editor/reassign'), ['class' => 'btn reassign-customer'])}</span>*}
            {$manager->render('CustomerAssign', ['manager' => $manager, 'hide'=> true])}
        {/if}
        {*$manager->render('Toolbar')*}
    </div>
    <div class="widget-content address-block">
            {assign var=re1 value='.{'}
            {assign var=re2 value='}'}
            {if $manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')}
                {assign var=customer value=$manager->getCustomersIdentity()}

                <div class="">
                    {if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="">
                            <label>{$smarty.const.ENTRY_GENDER}:</label>
                            {$g = $model->getGenderList()}
                            <span class="checkout-gender-data">{$g[$customer->customers_gender]}</span>
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="">
                            <label>{$smarty.const.ENTRY_FIRST_NAME}:</label>
                            <span class="checkout-firstname-data">{$customer->customers_firstname|escape:'html'}</span>
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="">
                            <label>{$smarty.const.ENTRY_LAST_NAME}:</label>
                            <span class="checkout-lastname-data">{$customer->customers_lastname|escape:'html'}</span>
                        </div>
                    {/if}

                    <div class="">
                        <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}:</label>
                        <span class="checkout-email_address-data">{$customer->customers_email_address|escape:'html'}</span>
                    </div>
                    {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="">
                            <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}:</label>
                            <span class="checkout-telephone-data">{$customer->customers_telephone|escape:'html'}</span>
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="">
                            <label>{$smarty.const.ENTRY_LANDLINE}:</label>
                            <span class="checkout-landline-data">{$customer->customers_landline|escape:'html'}</span>
                        </div>
                    {/if}
                    <div class="">
                        <a href="#change-customer" class="change-customer">Change</a>
                    </div>
                </div>

            {else}
                <span class="walkin-title">{$smarty.const.TEXT_WALKIN_ORDER} {$admin_name}</span>
                {$manager->render('CustomerAssign', ['manager' => $manager, 'hide'=> true])}
            {/if}
            <div class="contact-info-source">
            <div class="pop-up-content-page"  style="display: none" id="change-customer">
                <div class="popup-heading">
                    {$smarty.const.CUSTOMER_DATA}
                </div>
                <div class="popup-content">
                    <div class="contact-info form-inputs contact-info-popup">
                        <div class="row me-0">
                            {if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
                                <div class="col-12 genders-title  mb-2">
                                    <div class="">{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</div>
                                    {Html::activeRadioList($model, 'gender', ($model->getGenderList()), ['unselect' => null])}
                                </div>
                            {/if}
                            {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                                <div class="col-6 mb-2">
                                    <label>{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>
                                    {if in_array(ACCOUNT_FIRSTNAME, $model->getRequired())}
                                        {Html::activeTextInput($model, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                                    {else}
                                        {Html::activeTextInput($model, 'firstname', ['class' => 'form-control'])}
                                    {/if}
                                </div>
                            {/if}
                            {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                                <div class="col-6 mb-2">
                                    <label>{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>
                                    {if in_array(ACCOUNT_LASTNAME, $model->getRequired())}
                                        {Html::activeTextInput($model, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                                    {else}
                                        {Html::activeTextInput($model, 'lastname', ['class' => 'form-control'])}
                                    {/if}
                                </div>
                            {/if}

                            <div class="col-6 mb-2">
                                <label>{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
                                {Html::activeInput('email', $model, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email", 'class' => 'form-control'])}
                            </div>
                            {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
                                <div class="col-6 mb-2">
                                    <label>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
                                    {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register'])}
                                        {Html::activeTextInput($model, 'telephone', ['data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}", 'class' => 'form-control'])}
                                    {else}
                                        {Html::activeTextInput($model, 'telephone',['class' => 'form-control'])}
                                    {/if}
                                </div>
                            {/if}
                            {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
                                <div class="col-6 mb-2">
                                    <label>{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>
                                    {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register'])}
                                        {Html::activeTextInput($model, 'landline', ['data-required' => "{sprintf($smarty.const.ENTRY_LANDLINE_NUMBER_ERROR, $smarty.const.ENTRY_LANDLINE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_LANDLINE_MIN_LENGTH}{$re2}", 'class' => 'form-control'])}
                                    {else}
                                        {Html::activeTextInput($model, 'landline', ['class' => 'form-control'])}
                                    {/if}
                                </div>
                            {/if}
                            <div class="col-12">
                                <span class="required">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="popup-buttons">
                    <span class="btn btn-primary btn-save-customer" data-upridsuffix="-38-1-1">{$smarty.const.TEXT_APPLY}</span>
                    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                </div>
            </div>
            </div>

    </div>
</div>
<script>
(function($){

    $(function () {
        let genders = { };
        {if ($manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')) && $model && in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
            genders = JSON.parse(`{json_encode($model->getGenderList())}`)
        {/if}
        $('body').on('click', '.btn-save-customer', function () {
            $('.popup-box .contact-info-popup input').each(function () {
                if ($(this).attr('type') == 'radio') {
                    if ($(this).prop('checked')) {
                        $(`.${ $(this).closest('div').attr('id') }-data`).html(genders[$(this).val()])
                        $(`.contact-info-source input[value="${ $(this).attr('value') }"]`).prop('checked', true).trigger('change')
                    }
                } else {
                    $(`.${ $(this).attr('id') }-data`).html($(this).val())
                    $(`.contact-info-source input[name="${ $(this).attr('name') }"]`).val($(this).val())
                }
            });
            $('.popup-box-wrap:last').remove()
        });
    })

    $('.change-customer').popUp();

    $(document).click(function(e){

        let box = $('.contact-info.form-inputs');
        let fields = $('input, select', box);

        fields.validate();

    })
  })(jQuery)
</script>