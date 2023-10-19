{use class="yii\helpers\Url"}
{use class="yii\helpers\Html"}
<div class="language_edit">
			  {if {$messages|default:array()|@count} > 0}
			   {foreach $messages as $type => $message}
              <div class="alert alert-{$type} fade in">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>			   
			   {/foreach}
			  {/if}

	<form method="post" name="languages" action="{Url::to(['save'])}">
    <input type="hidden" name="row_id" value="{$row}">
    <div class="tabbable tabbable-custom">
              <ul class="nav nav-tabs top_tabs_ul main_tabs">
                <li class="active" data-bs-toggle="tab" data-bs-target="#tab_main"><a><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane topTabPane tabbable-custom" id="tab_main">
                      <div class="tabbable tabbable-custom">
                          <div class="tab-inserted">
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_KEY}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_VALUE}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_INFO_DESCRIPTION}</div>
                                  </div>
                          </div>
                </div>
      </div>{*content*}
    </div>{*tabbable*}
		<div class="btn-bar">
      <div class="btn-left">
      <a href="{Url::to(['index', 'row' => {$row}])}" class="btn btn-cancel" >{$smarty.const.IMAGE_CANCEL}</a>
      </div>
      <div class="btn-right">
      <input type="submit" value="{$smarty.const.IMAGE_UPDATE}" class="btn btn-no-margin btn-primary">      
      </div>
    </div>
		</form>