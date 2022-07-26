{use class="yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->

<div class="order-wrap feat-view">
  <!--=== Page Content ===-->
  <div class="row order-box-list">
    <div class="col-md-12">
      <div class="widget-content">
        <div class="alert fade in" style="display:none;">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"></span>
        </div>
        {if {$messages|@count} > 0}
          {foreach $messages as $message}
            <div class="alert fade in {$message['messageType']}">
              <i data-dismiss="alert" class="icon-remove close"></i>
              <span id="message_plce">{$message['message']}</span>
            </div>               
          {/foreach}
        {/if}
      </div>
    </div>
  </div>
  <div class="featureContent">
    <div class="featureItem">
      <div class="featureTitle">{$fInfo->features_title}</div>
      {if {strlen($fInfo->features_image)} > 0}
      <div class="featureImage"><img src="{$fInfo->features_image}" alt="{$fInfo->features_title}" width="200"></div>
      {/if}
      <div class="featureDescription">{$fInfo->features_description}</div>
      {if {strlen($fInfo->features_setup_price)} > 0}
      <div class="featurePrice">{$smarty.const.TEXT_FEATURES_SETUP_PRICE} {$fInfo->features_setup_price}</div>
      {/if}
      {if {strlen($fInfo->features_monthly_price)} > 0}
      <div class="featurePrice">{$smarty.const.TEXT_FEATURES_MONTHLY_PRICE} {$fInfo->features_monthly_price}</div>
      {/if}
      <div class="featureStatus">
        {if {$fInfo->feature_enabled} > 0}
          {$smarty.const.TEXT_ACTIVE}
        {else}
          {$smarty.const.TEXT_NOT_ACTIVE}
        {/if}
      </div>
      <div class="featureAction">
        <a class="btn btn-mar-right btn-primary" href="{$app->urlManager->createUrl('features/index')}">{$smarty.const.IMAGE_BACK}</a>
        {if {$fInfo->feature_enabled} > 0}
          <a class="btn btn-mar-right btn-primary" onclick="return confirm('Are you sure?');" href="{$app->urlManager->createUrl(['features/uninstall', 'fID' => {$fInfo->features_id}])}">{$smarty.const.IMAGE_UNINSTALL}</a></div>
        {else}
          <a class="btn btn-mar-right btn-primary" href="{$app->urlManager->createUrl(['features/install', 'fID' => {$fInfo->features_id}])}">{$smarty.const.IMAGE_INSTALL}</a></div>
        {/if}
    </div>
  </div>
  <!-- /Page Content -->
</div>
