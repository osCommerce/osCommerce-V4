{use class="common\helpers\Html"}
<div class="widget box box-no-shadow">
              <div class="widget-header widget-header-theme"><h4>{$smarty.const.BOX_CATALOG_WAREHOUSES}</h4></div>
              <div class="widget-content">
                  <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                    <thead>
                        <tr>
                            <th>{$smarty.const.TEXT_WAREHOUSE}</th>
                            <th>{$smarty.const.TABLE_HEADING_STATUS}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $warehouses as $warehouse}
                        <tr>
                            <td>{$warehouse['text']}</td>
                            <td>
                                <input type="checkbox" name="warehouse_status[{$warehouse['id']}]" {if $warehouse['status']} checked{/if} class="js_check_status" value="1"/>
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                    {if $ext = \common\helpers\Extensions::isAllowed('WarehousePriority')}
                        {$ext::priorityBlock($warehouse_priorities)}
                    {/if}
              </div>
          </div>
