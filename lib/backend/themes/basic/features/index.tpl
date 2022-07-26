{use class="yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->

<div class="order-wrap feat">
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
  <div class="dataTables_header clearfix">
    <form id="filterForm" name="filterForm" action="{$app->urlManager->createUrl('features/index')}" method="get">
      <div class="ord_status_filter_row">
        {$app->controller->view->filterFeaturesTypes}
      </div>
      <div class="col-md-6 col-md-6-new">
        <div class="dataTables_filter"><label><div class="input-group input-group-order"><span type="search" class="input-group-addon dt-ic-search"><i class="icon-search"></i></span>{$app->controller->view->filterSearch}</div></label></div>
      </div>
    </form>
  </div>
  <div class="featureContent">
{if {$app->controller->view->featuresArray|@count} > 0}
  {foreach $app->controller->view->featuresTypesArray as $id => $type}
    {if {$app->controller->view->featuresArray[$id]|@count} > 0}
      <fieldset class="main">
        <legend>{$type.features_types_title}</legend>
      {foreach $app->controller->view->featuresArray[$id] as $feature}
          <div class="featureItem">
            <div class="featureTitle">{$feature.features_title}</div>
            {if {strlen($feature['features_image'])} > 0}
            <div class="featureImage"><img src="{$feature.features_image}" alt="{$feature.features_title}" width="200"></div>
            {/if}
            {if {strlen($feature['features_setup_price'])} > 0}
            <div class="featurePrice">{$smarty.const.TEXT_FEATURES_SETUP_PRICE} {$feature.features_setup_price}</div>
            {/if}
            {if {strlen($feature['features_monthly_price'])} > 0}
                <div class="featurePrice">{$smarty.const.TEXT_FEATURES_MONTHLY_PRICE} <b>{$feature.features_monthly_price}</b></div>
            {/if}
            <div class="featureStatus">{if {$feature['feature_enabled']} > 0}{$smarty.const.TEXT_ACTIVE}{else}{$smarty.const.TEXT_NOT_ACTIVE}{/if}</div>
            <div class="featureAction"><a class="btn btn-mar-right btn-primary" href="{$app->urlManager->createUrl(['features/view', 'fID' => {$feature.features_id}])}">{$smarty.const.IMAGE_DETAILS}</a></div>
          </div>
      {/foreach}
      </fieldset>
    {/if}
  {/foreach}
{/if}
  </div>
  <!-- /Page Content -->
</div>
<script>
    $(window).resize(function(){ 
        $('.featureContent').inrow({ item1:'.featureItem',item2:'.featureTitle',item3:'.featureImage' });
    });
    $(window).resize();
</script>