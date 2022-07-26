{use class="\common\helpers\Date"}
    <div class="widget box">
      <div class="widget-header">
        <h4>{$smarty.const.TEXT_SOLD_IN_PERIOD}</h4>
      </div>
      <div class="widget-content">
        {$smarty.const.TEXT_DATE_ADDED}&nbsp;{Date::date_short($date_added)}
        {if $sold}
            <table class="datatable table sold-table table-no-search table-striped">
                <thead>
                    <tr>
                        <th class="no-sort" >{$smarty.const.TEXT_ALL_PERIOD}</th>
                        <th class="no-sort" >{$smarty.const.TEXT_DATE_RANGE}</th>
                        <th class="no-sort" align="center">{$smarty.const.TABLE_HEADING_PRODUCTS_SOLD}</th>
                    </tr>
                </thead>
                <tbody>
                {foreach $sold as $row}
                    <tr>
                    {$row}
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {else}
            No Result...
        {/if}
    </div>    
    <div class="btn-bar">
        <div style="text-align:center;"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>        
    </div>
   </div>
   
   <script>
    $(document).ready(function(){
        /*$('.sold-table').dataTable({
             "searching": false,
              "columnDefs": [ {
                  "targets": 'no-sort',
                  "orderable": false,
            } ]
        });*/
    })
   </script>
   