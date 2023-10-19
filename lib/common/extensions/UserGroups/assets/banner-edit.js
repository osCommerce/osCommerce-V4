$(function(){
    applySwitchers();
    $('body').on('saved-page', applySwitchers);

    function applySwitchers() {
        console.log(111);
        let switchKey = true;
        $('.platform-status').on('switchChange.bootstrapSwitch', function (e, d) {
            if (switchKey) {
                const platformId = $(this).data('platform-id');
                $(`.user-group[data-platform-id="${platformId}"]`).prop('checked', d);
            }
        });
        $('.user-group').on('change', function () {
            switchKey = false;
            setTimeout(() => switchKey = true, 100);
            const platformId = $(this).data('platform-id');
            let allOut = true;
            $(`.user-group[data-platform-id="${platformId}"]`).each(function () {
                if ($(this).prop('checked')){
                    allOut = false;
                }
            });
            if (allOut) {
                $(`.platform-status[data-platform-id="${platformId}"]`).bootstrapSwitch('state', false);
            } else {
                $(`.platform-status[data-platform-id="${platformId}"]`).bootstrapSwitch('state', true);
            }
        });
    }
});