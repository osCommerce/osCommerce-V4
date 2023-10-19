
<div class="btn-box-inv-price btn-market after">
  {if !isset($category)}
  <span class="btn-gaw-pr" id="btn-gaw-pr">{$smarty.const.FIELDSET_ASSIGNED_AS_GIVEAWAY}</span>
  <span class="btn-pop-pr" id="btn-pop-pr">{$smarty.const.TEXT_POPULARITY}</span>
  {/if}
  {foreach \common\helpers\Hooks::getList('categories/productedit', 'marketing/tab-navs') as $filename}
    {include file=$filename category=$category|default:null}
  {/foreach}
</div>

{if !isset($category)}
<div class="gaw-pr-box" id="box-gaw-pr">
  {include 'give-away.tpl'}
</div>
<div class="pop-pr-box" id="box-pop-pr">
  {include './popularity.tpl'}
</div>
{/if}
{foreach \common\helpers\Hooks::getList('categories/productedit', 'marketing/tab-content') as $filename}
    {include file=$filename category=$category|default:null}
{/foreach}

<script>
  $(function () {
    function clickMarketingButton() { // shows/hides appropriate divs
      $('.btn-market span').each(function () {
        var div_id = this.id.replace('btn-', 'box-');
        if ($(this).hasClass('active')) {
          $('#' + div_id).css('display', 'block');
        } else {
          $('#' + div_id).css('display', 'none');
        }
      });
      {if !isset($category)}
      init_gwa();
      {/if}
    }

    clickMarketingButton();
    $('.btn-market span').click(function () {
      $('.btn-market span').removeClass('active');
      $(this).toggleClass('active');
      clickMarketingButton();
    });
  });
</script>