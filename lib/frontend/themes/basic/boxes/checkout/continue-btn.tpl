{if $page_name == 'index_2'}
    <span class="btn-2 btn-next"><span class="btn-title">{$smarty.const.CONTINUE}</span></span>
{else}
    <button type="submit" class="btn-2 btn-next"><span class="btn-title">
    {if $smarty.const.SKIP_CHECKOUT == 'True'}
        {$smarty.const.TEXT_CONFIRM_AND_PAY}
    {else}
        {$smarty.const.CONTINUE}
    {/if}
    </span></button>
    <script type="text/javascript">
        tl(function(){
            let statuses = [];
            $(window).on('disable-checkout-button', function (e, data) {
                if (data.value === true) {
                    statuses.push(data.name)
                } else {
                    statuses = statuses.filter(i => i === data.name ? false : true)
                }
                if (statuses.length) {
                    $('#frmCheckout button').addClass('disabled-area')
                } else {
                    $('#frmCheckout button').removeClass('disabled-area')
                }
            });
        });
    </script>
    {if ($ccExt = \common\helpers\Acl::checkExtensionAllowed('CustomerCredit', 'allowed'))}
        {$ccExt::getButtonContinueHtml($manager)}
    {/if}
{/if}
{if is_array($inline)}
  {foreach $inline as $link}
      <div class="or-text">{$smarty.const.TEXT_OR}</div>
      <div class="add-buttons">
          {$link}
      </div>
  {/foreach}
{/if}