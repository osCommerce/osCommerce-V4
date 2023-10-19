{use class="\yii\helpers\Html"}
{use class="frontend\design\Info"}
{assign var=re1 value='.{'}
{assign var=re2 value='}'}
<div class="contact-info form-inputs">
{$model = $manager->getCustomerContactForm()}
<div class="columns">
    {if in_array(ACCOUNT_EMAIL, ['required', 'required_register', 'visible', 'visible_register'])}
		<div class="col-2">
			<label>
				<span>{field_label const="ENTRY_EMAIL_ADDRESS" configuration="ACCOUNT_EMAIL"}</span>
				{if in_array(ACCOUNT_EMAIL, ['required', 'required_register'])}
					{Html::activeInput('email', $model, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email", 'autocomplete' => 'email'])}
				{else}
					{Html::activeInput('email', $model, 'email_address', ['data-pattern' => "email", 'autocomplete' => 'email'])}
				{/if}
			</label>
		</div>
	{/if}
    {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-2">
            <label>
                <span>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</span>
                {if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register'])}
                    {Html::activeTextInput($model, 'telephone', ['data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}", 'autocomplete' => 'tel'])}
                {else}
                    {Html::activeTextInput($model, 'telephone', ['autocomplete' => 'tel'])}
                {/if}
            </label>
        </div>
    {/if}
    {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
        <div class="col-2">
            <label>
                <span>{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</span>
                {if in_array(ACCOUNT_LANDLINE, ['required', 'required_register'])}
                    {Html::activeTextInput($model, 'landline', ['data-required' => "{sprintf($smarty.const.ENTRY_LANDLINE_NUMBER_ERROR, $smarty.const.ENTRY_LANDLINE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_LANDLINE_MIN_LENGTH}{$re2}", 'autocomplete' => 'landline'])}
                {else}
                    {Html::activeTextInput($model, 'landline', ['autocomplete' => 'landline'])}
                {/if}
            </label>
        </div>
    {/if}
</div>
</div>

<div class="contact-info-data" style="display: none"></div>
<div class="contact-info-buttons" style="display: none">
{if $manager->get('is_multi') != 1}
    <a href="javascript:void(0);" class="btn-edit1">{$smarty.const.EDIT}</a>
{/if}
    <a href="javascript:void(0);" class="btn-save btn" style="display: none">{$smarty.const.TEXT_SAVE}</a>
    <a href="javascript:void(0);" class="btn-cancel" style="display: none">{$smarty.const.CANCEL}</a>
</div>

<script>
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/password-strength.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
        '{Info::themeFile('/js/bootstrap.min.js')}',
    ], function(){
        var box = $('#box-{$id}');

        let fields = $('input, select', box);
        fields.validate();
        let contactData = $('.contact-info-data', box);
        let contactForm = $('.contact-info', box);
        let buttons = $('.contact-info-buttons', box);
        let edit = $('.btn-edit1', box);
        let save = $('.btn-save', box);
        let cancel = $('.btn-cancel', box);

        var isData = true;

        contactData.html('');
        fields.each(function(){
            if (!$(this).val() && $(this).attr('data-pattern')) {
                isData = false;
            }
            if ($(this).val()) {
                var curr_val = $(this).val();
{if $manager->get('is_multi') == 1}
                if ($(this).attr('name') == 'checkout[email_address]') {
                    curr_val = '{$manager->get('customer_email_address')}';
                }
{/if}
                contactData.append('<div data-name="' + $(this).attr('name') + '">' + curr_val.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>');
            }
        });

        if (window.location.hash === '#contact_information') {
            isData = false;
        }

        if (isData){
            contactData.show();
            buttons.show();
            contactForm.hide()
        }

        edit.on('click', function(){
            edit.hide();
            save.show();
            cancel.show();
            contactForm.show();
            contactData.hide();
        });

        save.on('click', function(){
            save.hide();
            cancel.hide();
            edit.show();
            contactData.show();
            contactForm.hide();

            contactData.html('');
            fields.each(function(){
                if ($(this).val()) {
                    contactData.append('<div data-name="' + $(this).attr('name') + '">' + $(this).val().replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>');
                }
            });
        });

        cancel.on('click', function(){
            save.hide();
            cancel.hide();
            edit.show();
            contactData.show();
            contactForm.hide();

            $('div', contactData).each(function(){
                $('input[name="' + $(this).data('name') + '"]').val($(this).text())
            });
        })

    })
</script>