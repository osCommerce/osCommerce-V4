{use class="\yii\helpers\Html"}
{use class="\yii\helpers\ArrayHelper"}
<form name="setting" id="google_settings" action="{$app->urlManager->createUrl(['google_analytics/save', 'row_id' => $app->controller->view->row_id, 'platform_id' => $app->controller->view->platform_id])}" method="post">
	<div class="widget-content ">
	<input type="hidden" name="id" value="{$id}">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font table-send-coup">
	{$context}
    </table>
	</div>
	
<div class="noti-btn">
    <div class="btn-left"><a href="{$app->urlManager->createUrl(['google_analytics/index', 'row_id' => $app->controller->view->row_id, 'platform_id' => $app->controller->view->platform_id])}" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><input type="submit" class="btn btn-primary" value="{$smarty.const.IMAGE_SAVE}"></div>
</div>
</form>
<script>
	$(document).ready(function(){
		if ($('input[name=type]:radio').length){
			
		}
	})
    
</script>