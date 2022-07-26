{use class="frontend\design\Info"}
{Info::addBoxToCss('info-popup')}
{Info::addBoxToCss('switch')}
{use class = "yii\helpers\Html"}

{Html::beginForm('subscribers/account', 'post', ['id' => 'frmAccountNewsletter'])}
  <div class="messages"></div>
    <label class="send-email">
        <input type="checkbox" class="subscription-switcher" name="newsletter" {if $newsletter} checked{/if} value="1"/> <span>{$smarty.const.SEND_ME_EMAIL_WITH_CONTENT}</span>
    </label>
    {if is_array($variants) && count($variants)>0}
    <div class="subscription-list">
        {foreach $variants as $variant}
            <div class="subscription-variant">
                {if $variant.description}
                    <div class="info-popup top-right"><div>{$variant.description}</div></div>
                {/if}
                <div class="title">{$variant.title}</div>
                <div class="subscription-switcher">
                    <input type="checkbox" class="subscription-switcher" name="lists[]" value="{$variant.id}"{if $variant.yes} checked{/if}/>
                </div>
            </div>
        {/foreach}
    </div>
    {/if}

    <div class="buttons">
        <div class="right-buttons">
            <button class="btn" type="submit">{$smarty.const.TEXT_CONFIRM}</button>
        </div>
    </div>
{Html::endForm()}
<script>
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
    ], function () {

        var form = $('#box-{$id} form');

        $("input.subscription-switcher ").bootstrapSwitch({
            onSwitchChange: function (element, arguments) {
                if (arguments && element.target.name=='lists[]' && $('#box-{$id} form input[name="newsletter"]:checked').length==0) {
                  $('#box-{$id} form input[name="newsletter"]').click();
                }
                return true;
            },
            offText: '{$smarty.const.TEXT_NO|escape:"javascript"}',
            onText: '{$smarty.const.TEXT_YES|escape:"javascript"}',
        });

        {*$('input', form).validate();*}
        form.on('submit', function(){
            if ($('.required-error', form).length === 0){
                $.post(form.attr('action'), form.serialize(), function(data){
                    var messages = '';
                    $.each(data.messages, function(key, val){
                        messages += '<div class="message '+val['type']+'">'+val.text+'</div>';
                        if (val['.type'] === 'success') {
                            setTimeout(function(){
                                $('.pop-up-close').trigger('click')
                            }, 1000)
                        }
                    });
                    $('.messages', form).html(messages)
                }, 'json')
            }
            return false;
        });



    })
</script>