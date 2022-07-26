{use class="yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('form')}

{Html::beginForm($link, 'post', ['name' => 'subscribers_form'])}
<div class="contact-info form-inputs">
    <div class="col-full">
        <label>
            <span>{field_label const="TEXT_EMAIL" required_text="*"}</span>
            <input
                    type="email"
                    name="subscribers_email_address"
                    id="subscribers_email_address"
                    value="{$subscribers_email_address|escape:'html'}"
                    placeholder="{$smarty.const.TEXT_PLEASE_ENTER_VALID_EMAIL|escape:'html'}"
                    data-required="{$smarty.const.ENTRY_EMAIL_ADDRESS|escape:'html'}"
                    data-pattern="email">
        </label>
    </div>
    <div class="buttons">
        <div class="right-buttons"><button type="submit" class="btn">{$smarty.const.CONTINUE}</button></div>
    </div>
</div>
{Html::endForm()}