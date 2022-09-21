{use class="\common\classes\platform"}
{use class="common\helpers\Html"}
<div class="filter_pad">
  <div class="widget"><div class="widget-header" style="margin-bottom: 0;"><h4>{$smarty.const.TABLE_HEAD_PLATFORM_PRODUCT_ASSIGN}</h4></div>
  <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
    <thead>
    <tr>
      <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
      <th width="150">{$smarty.const.TEXT_ASSIGN}</th>
    </tr>
    </thead>
    <tbody>
    {foreach platform::getProductsAssignList() as $platform}
      <tr>
        <td>{$platform['text']}</td>
        <td>
          {Html::checkbox('platform[]', isset($app->controller->view->platform_assigned[$platform['id']]), ['value' => $platform['id'],'class'=>'check_on_off'])}
          {Html::hiddenInput('activate_parent_categories['|cat:$platform['id']|cat:']','',['class'=>'js-platform_parent_categories'])}
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  </div>
</div>