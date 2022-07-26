{use class="frontend\design\Info"}
<h1>{$smarty.const.HEADING_TITLE_1}</h1>

{if $messages_search}
  {$messages_search}
{/if}
<div class="middle-form">
<form action="{$search_result_page_link}" method="get">{tep_hide_session_id()}
	<div class="col-full">
	<div class="info">{$smarty.const.TEXT_SEARCH_HELP_LINK} (<a href="#search-help" class="pop-up-link">{$smarty.const.MORE_INFO}</a>)</div>
  <div id="search-help" style="display: none;">
    <div class="pop-up-info">
      <div class="heading-4">{$smarty.const.HEADING_SEARCH_HELP}</div>
      <p>{$smarty.const.TEXT_SEARCH_HELP}</p>
    </div>
    <div class="center-buttons">
      <span class="btn btn-cancel">{$smarty.const.CONTINUE}</span>
    </div>
  </div>
	</div>
	<div class="col-full">
		{$controls.keywords}
	</div>	
	{if $controls.search_in_description}
		<div class="col-full incl-check">
		{$controls.search_in_description}<label for="search_in_description">{$smarty.const.TEXT_SEARCH_IN_DESCRIPTION}</label>
		</div>
	{/if}
	
	<div class="col-full col-two">
		<label>{field_label const="ENTRY_CATEGORIES" required_text=""}</label>
		{$controls.categories}
	</div>
	<div class="col-full incl-check">
		{$controls.inc_subcat} <label for="include_subcategories">{$smarty.const.ENTRY_INCLUDE_SUBCATEGORIES}</label>
	</div>
	{if $controls.manufacturers}
	<div class="col-full col-two">
		<label>{field_label const="ENTRY_MANUFACTURERS" required_text=""}</label>
		{$controls.manufacturers}
	</div>
	{/if}
  {if $controls.price_from}
	<div class="col-left">
		<label>{field_label const="ENTRY_PRICE_FROM" required_text=""}</label>
		{$controls.price_from}
	</div>
	<div class="col-right">
		<label>{field_label const="ENTRY_PRICE_TO" required_text=""}</label>
		{$controls.price_to}
	</div>
{/if}
{if $controls.date_from}
<div class="col-left">
	<label>{field_label const="ENTRY_DATE_FROM" required_text=""}</label>
	{$controls.date_from}
</div>
<div class="col-right">
	<label>{field_label const="ENTRY_DATE_TO" required_text=""}</label>
	{$controls.date_to}
</div>
{/if}
{if PRODUCTS_PROPERTIES == 'True'}
   {foreach $searchable_properties as $property}
		<div class="col-full">
			<label>{field_label text=$property.properties_name required_text=""}</label>
			{$property.control}
		</div>
   {/foreach}
{/if}
<div class="center-buttons"><button class="btn-2" type="submit">{$smarty.const.IMAGE_BUTTON_SEARCH}</button></div>
  {*
  $info_box_contents = array();
  $info_box_contents[] = array('text' => tep_template_image_submit('button_search.' . BUTTON_IMAGE_TYPE, IMAGE_BUTTON_SEARCH, 'class="transpng"'));
  $info_box_contents[] = array('text' => '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_POPUP_SEARCH_HELP) . '\')">' . TEXT_SEARCH_HELP_LINK . '</a>');
  new buttons($info_box_contents);
  ?>
  *}
</form>
</div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    $('.pop-up-link').popUp();
  })
</script>