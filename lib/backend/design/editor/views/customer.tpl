{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-personal">
        <h4 style="width:83%">Customer
        {if $manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')}
            {assign var=customer value=$manager->getCustomersIdentity()}
            <span class="cus-name cust-style">{$customer->customers_firstname|escape:'html'} {$customer->customers_lastname|escape:'html'}</span>
            {if $customer->customers_email_address}
            <span class="cust-style">/</span> <span class="cust-email cust-style"><a href="mailto:{$customer->customers_email_address}">{$customer->customers_email_address}</a></span>
            {/if}
            {if $customer->customers_telephone}
            <span class="cust-style">/</span> <span class="icon-phone cust-style"> {$customer->customers_telephone|escape:'html'}</span>
            {/if}
        {else}
            <span class="walkin-title">{$smarty.const.TEXT_WALKIN_ORDER} {$admin_name}</span>
            <span class="walkin-title">{Html::a('Add customer', Url::to('editor/reassign'), ['class' => 'assign-customer'])}</span>
            {$manager->render('CustomerAssign', ['manager' => $manager, 'hide'=> true])}
        {/if}
        </h4>
        {if $manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')}
            <span>{Html::a('Reassing', Url::to('editor/reassign'), ['class' => 'btn reassign-customer'])}</span>
            {$manager->render('CustomerAssign', ['manager' => $manager, 'hide'=> true])}
        {/if}
        {$manager->render('Toolbar')}
    </div>
    <div class="widget-content after">
        {if $manager->isCustomerAssigned() || $manager->getCustomersIdentity()->get('fromOrder')}
            {assign var=re1 value='.{'}
            {assign var=re2 value='}'}
            <div class="contact-info form-inputs w-line-row-2">
                <div class="columns">
                    {if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="col-full genders-title">
                            <div class="">{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</div>
                            {Html::activeRadioList($model, 'gender', $model->getGenderList(), ['unselect' => null])}
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="col-2">
                            <label>
                                <span>{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</span>
                                {if in_array(ACCOUNT_FIRSTNAME, $model->getRequired())}
                                    {Html::activeTextInput($model, 'firstname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                                {else}
                                    {Html::activeTextInput($model, 'firstname', ['class' => 'form-control'])}
                                {/if}
                            </label>
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="col-2">
                            <label>
                                <span>{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</span>
                                {if in_array(ACCOUNT_LASTNAME, $model->getRequired())}
                                    {Html::activeTextInput($model, 'lastname', ['data-pattern' => "{$re1}{$smarty.const.ENTRY_LAST_NAME_MIN_LENGTH}{$re2}", 'data-required' => "{sprintf($smarty.const.ENTRY_LAST_NAME_ERROR, $smarty.const.ENTRY_LAST_NAME_MIN_LENGTH)}", 'class' => 'form-control'])}
                                {else}
                                    {Html::activeTextInput($model, 'lastname', ['class' => 'form-control'])}
                                {/if}
                            </label>
                        </div>
                    {/if}
                </div>
                <div class="columns">
                    <div class="col-full">
                        <label>
                            <span>{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</span>
                            {Html::activeInput('email', $model, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email", 'class' => 'form-control'])}
                        </label>
                    </div>
                    {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="col-2">
                            <label>
                                <span>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</span>
                                {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register'])}
                                    {Html::activeTextInput($model, 'telephone', ['data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}", 'class' => 'form-control'])}                    
                                {else}
                                    {Html::activeTextInput($model, 'telephone',['class' => 'form-control'])}
                                {/if}
                            </label>
                        </div>
                    {/if}
                    {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
                        <div class="col-2">
                            <label>
                                <span>{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</span>
                                {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register'])}
                                    {Html::activeTextInput($model, 'landline', ['data-required' => "{sprintf($smarty.const.ENTRY_LANDLINE_NUMBER_ERROR, $smarty.const.ENTRY_LANDLINE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_LANDLINE_MIN_LENGTH}{$re2}", 'class' => 'form-control'])}
                                {else}
                                    {Html::activeTextInput($model, 'landline', ['class' => 'form-control'])} 
                                {/if}
                            </label>
                        </div>
                    {/if}
                    <div class="col-full">
                        <span class="required">*Required fields</span>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>
<script>
(function($){
    $('.reassign-customer, .assign-customer').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $('.customer-assign-box').show();
        $('input[name=search_customer]').focus();
    })
    $(document).click(function(e){
        
        let box = $('.contact-info.form-inputs');
        let fields = $('input, select', box);
        
        fields.validate();
        
        if (!$(e.target).closest('.customer-assign-box').size()){
            $('.customer-assign-box').hide();
        }
        $('.customer-assign-box-close').click(function(){
            $('.customer-assign-box').hide();
        });
    })    
  })(jQuery)
</script>