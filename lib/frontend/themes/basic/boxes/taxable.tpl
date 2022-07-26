{use class="\yii\helpers\Html"}
{use class="frontend\design\Info"}
{$displayMode = $settings[0]['display_mode']}

{if $displayMode == 'checkbox-switcher'}
  {Info::addBoxToCss('switch')}
{/if}

{Html::beginForm($url, "post")}
<span class='heading-title taxable'>{$smarty.const.TEXT_ALL_PRICES}</span>
{if $displayMode == 'radio'}
  
  {foreach $tList as $key => $value}
    <label {*if $taxable == !$key}class="hide" {/if*}>
        <input type="radio" name="taxable" value="{$key}" {if $taxable == $key}checked{/if} onchange = "this.form.submit()">
        <span>{$value}</span>
    </label>
  {/foreach}

{elseif $displayMode == 'checkbox' || $displayMode == 'checkbox-switcher'}

  <label >{Html::checkbox('taxable', $taxable, ['value'=>1, 'onchange' => "this.form.submit()", 'class' => 'taxable-switcher'])}{if $displayMode == 'checkbox'}<span>{$smarty.const.TEXT_INC_VAT}</span>{/if}</label>

{else}

  {Html::dropDownList('taxable', $taxable, $tList, ['onchange' => "this.form.submit()"])}

{/if}


{Html::endForm()}
{if $displayMode == 'checkbox-switcher'}

  <script type="text/javascript">
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
    ], function () {

        $(".taxable-switcher").bootstrapSwitch({
            offText: '{$smarty.const.TEXT_EXC_VAT}',
            onText: '{$smarty.const.TEXT_INC_VAT}',
            labelWidth: '30px',
            onSwitchChange: function (d, e) {
                $('#box-{$id} .taxable-switcher-form').trigger('submit')
            }
        });
    })
  </script>
{/if}
