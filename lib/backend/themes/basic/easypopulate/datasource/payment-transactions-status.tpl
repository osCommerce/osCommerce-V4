{use class="common\helpers\Html"}
{use class="yii\helpers\ArrayHelper"}
<div class="">
    <div class="tabbable tabbable-custom tabbable-ep">
      {if count($platforms_list)>1 }
        <ul class="nav nav-tabs">
            {foreach $platforms_list as $_platform}
                <li class="{if $_platform['is_default']} active {/if}" data-bs-toggle="tab" data-bs-target="#tab_pp_{$_platform['id']}"><a><span>{$_platform['text']}</span></a></li>
            {/foreach}            
        </ul>
      {/if}
        <div class="tab-content tab-content">
            {foreach $platforms_list as $_platform}
            <div class="tab-pane topTabPane tabbable-custom {if $_platform['is_default']} active {/if}" id="tab_pp_{$_platform['id']}">
                <div class="container payments-list">
                {$selected_modules = $payments[$_platform['id']]['modules']}
                {if !is_array($selected_modules)}{$selected_modules = []}{/if}
                {foreach $_platform['modules'] as $class => $module}
                  <div class="row after padding-bottom-10px">

                    <label>
                    {Html::checkbox('datasource['|cat:$code|cat:'][payments]['|cat:$_platform['id']|cat:'][modules][]', in_array($class, $selected_modules), ['value' => $class])}
                    {$module['title']}
                    </label>
                    <div class="row">
                      <div class="col-md-4">
                        <label for="{'period'|cat:$code|cat:$_platform['id']|cat:$class}">{$smarty.const.TEXT_DATE_RANGE}</label>
                        {Html::dropDownList('datasource['|cat:$code|cat:'][payments]['|cat:$_platform['id']|cat:']['|cat:$class|cat:'][period]', $payments[$_platform['id']][$class]['period'], $periods, ['id' => 'period'|cat:$code|cat:$_platform['id']|cat:$class ])}
                      </div>
                      <div class="col-md-8">
                        <div class="row"><label>{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_STATUS}</label></div>
                        {if is_array($orderPaymentStatusArray) }
                          {foreach $orderPaymentStatusArray as $k => $v }
                            {$selected_statuses = $payments[$_platform['id']][$class]['opstatus']}
                            {if !is_array($selected_statuses)}{$selected_statuses = []}{/if}

                            <label class="col-md-3">
                              {Html::checkbox('datasource['|cat:$code|cat:'][payments]['|cat:$_platform['id']|cat:']['|cat:$class|cat:'][opstatus][]', in_array($k, $selected_statuses), ['value' => $k])} <span class='title'>{$v}</span>
                            </label>
                          {/foreach}
                        {/if}
                      </div>
                    </div>
                  </div>
                {/foreach}
                </div>
            </div>
            {/foreach}
        </div>
    </div>    
</div>