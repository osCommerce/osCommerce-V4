{use class="yii\helpers\Html"}
{Html::dropDownList('page_status_select', $status, $statuses, ['class' => 'form-control select-change-status', 'id' => 'select-change-status-'|cat:$id])}

<script type="text/javascript">
    $(function(){
        const data = JSON.parse('{$data}');
        const $currentStatus = $('.current-page-status');
        const $selectChangeStatus = $('#select-change-status-{$id}');

        $currentStatus.html(data.statuses[data.status]);

        $selectChangeStatus.on('change', function(){
            $.post('information_manager/change-page-status', {
                type: data.type,
                page_id: data.pageId,
                status: $selectChangeStatus.val(),
            }, function(response){
                if (response === 'ok') {
                    $(window).trigger('changePageStatus', $selectChangeStatus.val());
                } else {
                    alertMessage('<div style="padding: 20px;">Something went wrong</div>')
                }
            })
        });

        $(window).on('changePageStatus', function(e, status){
            $selectChangeStatus.val(status)
            $currentStatus.html(data.statuses[status]);
        });
    })
</script>