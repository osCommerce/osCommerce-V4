<h1>{$smarty.const.HEADING_TITLE}</h1>

<form name="account_newsletter" action="{$account_newsletter_action}" method="post" id="frmAccountNewsletter">
  <input type="hidden" name="action" value="process">

  <h2>{$smarty.const.MY_NEWSLETTERS_TITLE}</h2>

  <p>
    <label>
      <input type="checkbox" name="newsletter_general" value="1" {if $newsletter_general} checked="checked"{/if} >
      {$smarty.const.MY_NEWSLETTERS_GENERAL_NEWSLETTER}
    </label>
  </p>
  <p>
    {$smarty.const.MY_NEWSLETTERS_GENERAL_NEWSLETTER_DESCRIPTION}
  </p>

  <div class="buttons">
    <div class="right-buttons"><button class="btn-1" type="submit">{$smarty.const.IMAGE_BUTTON_CONTINUE}</button></div>
    <div class="left-buttons"><a href="{$link_back_href}" class="btn">{$smarty.const.IMAGE_BUTTON_BACK}</a></div>
  </div>

</form>