{use class="\common\extensions\SMS\SMS"}
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
  <input type="hidden" id="row_id" value="" />
</div>
<!-- /Page Header -->
  <!--=== Page Content ===-->

<div class="row">
    <div class="col-md-12">
            {if $messages|count > 0}
              {foreach $messages as $type => $message}
              <div class="alert fade in alert-{$type}">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>
              {/foreach}
            {/if}
            <div class="widget-content" id="reviews_list_data">
                {SMS::renderMessageForm($mInfo)}
            </div>
    </div>
</div>

