{use class="yii\helpers\Url"}
<div class="widget-new widget-attr-box box box-no-shadow">
    <div class="widget-header"><h4>{$smarty.const.TEXT_ASSIGNED_PROMOTIONS}</h4></div>

    <div class="widget-content">
        <table class="table table-striped table-bordered assigned_promotions">
            <thead>
            <tr>
                <th></th>
                <th>#</th>
                <th>{$smarty.const.TABLE_HEADING_PROMOTIONS_LABEL}</th>
                <th>{$smarty.const.TABLE_HEADING_STATUS}</th>
                <th>{$smarty.const.TEXT_START_DATE}</th>
                <th>{$smarty.const.TEXT_END_DATE}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
        {foreach $app->controller->view->assigned_promotions|default:null as $promotion}
            <tr role="row" prefix="promo-{$promotion['promo_id']}" class="{if !$promotion['status']}dis_prod{/if}">
                <td class="sort-pointer"></td>
                <td>{$promotion['promo_priority']}</td>
                <td>{$promotion['label']}</td>
                <td><input type="checkbox" {if $promotion['status']}checked="checked"{/if} disabled="disabled" readonly="readonly"></td>
                <td>{$promotion['date_start']}</td>
                <td>{$promotion['date_expired']}</td>
                <td><a target="_blank" href="{Url::toRoute(['promotions/edit', 'promo_id' => $promotion['promo_id']])}">edit</a></td>
            </tr>
        {/foreach}
            </tbody>
        </table>
        <input name="assigned_promotions_sort_order" type="hidden" id="assigned_promotions_sort_order" value="">
    </div>
</div>

<script>
    $(document).ready(function(){
        $( ".assigned_promotions tbody" ).sortable({
            handle: ".sort-pointer",
            axis: 'y',
            update: function( event, ui ) {
                var data = $(this).sortable('serialize', { attribute: "prefix" });
                $("#assigned_promotions_sort_order").val(data);
            },
        }).disableSelection();
    });
</script>