{use class="yii\helpers\Html"}
<span class="btn btn-primary" id="btn-change-status-{$id}" style="display: none"></span>

<script type="text/javascript">
    $(function(){
        const data = JSON.parse('{$data}');
        const $currentStatus = $('.current-page-status');
        const $btnChangeStatus = $('#btn-change-status-{$id}');

        let nextStatusKey;
        applyStatus();

        $btnChangeStatus.on('click', function(){
            $.post('information_manager/change-page-status', {
                type: data.type,
                page_id: data.pageId,
                status: nextStatusKey,
            }, function(response){
                if (response === 'ok') {
                    $(window).trigger('changePageStatus', nextStatusKey);
                } else {
                    alertMessage('<div style="padding: 20px;">Something went wrong</div>')
                }
            })
        });

        $(window).on('changePageStatus', function(e, status){
            data.status = status;
            applyStatus()
        });

        function applyStatus() {
            $currentStatus.html(data.statuses[data.status]);

            const index = data.statusKeys.indexOf(data.status);
            nextStatusKey = data.statusKeys[index + 1] || data.statusKeys[0];

            if (data.status === nextStatusKey) {
                $btnChangeStatus.hide();
            } else {
                $btnChangeStatus.show();
            }

            $btnChangeStatus.html(data.pageStatusActions[nextStatusKey]);
        }
    })
</script>