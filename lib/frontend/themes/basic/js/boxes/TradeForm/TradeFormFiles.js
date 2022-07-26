;(function(){
    let tradeFormDelay = setInterval(function(){
        if (typeof jQuery === "function"){
            clearInterval(tradeFormDelay);

            let allScripts = ['main.js']
                .map((url) => new Promise((resolve, reject) => {
                    $.ajax({
                        url: createJsUrl(url),
                        success: resolve,
                        error: reject,
                        dataType: 'script',
                        cache: true
                    });
                }))

            Promise.all(allScripts).then(tradeForm)
        }
    }, 100);

    function addFileTemplate(fieldId, groupId) {
        return `
            <div class="tf-file tf-file-new">
                <div class="remove"></div>
                <input class="form-control"
                        name="field[${fieldId}][]"
                        type="file"
                        accept="pdf,png,jpeg,jpg,bmp,gif"
                        data-type="file"
                        data-group-id="${groupId}">
            </div>
    `;
    }

    function tradeForm() {
        $('.w-account-customer-additional-field .files').each(function(){
            var $files = $(this);

            $('.btn-add', $files).on('click', function(){
                $(this).closest('.add-buttons').before(addFileTemplate($files.data('field-id'), $files.data('group-id')))
            });

            $files.on('click', '.remove', function(){
                $(this).closest('.tf-file').remove()
            })
        })
    }

})();