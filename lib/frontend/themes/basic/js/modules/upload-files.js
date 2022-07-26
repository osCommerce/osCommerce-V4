tl(function(){
    $('.upload-files').each(function(){
        let $uploadBox = $(this);

        let attachText = $uploadBox.data('attach-text');
        let addButtonText = $uploadBox.data('add-button-text');
        let name = $uploadBox.data('name');

        $uploadBox.html(`
        <div class="attach-block">
            <div class="attached-block">
                <div class="attach-text" style="display: none">${attachText}</div>
                <div class="attached-files"></div>
                <div class="attached-holder" style="display: none;"></div>
            </div>
            <label class="attach-active-holder">
                <input type="file" class="attach-file">
                <span class="btn">${addButtonText}</span>
            </label>
        </div>`);

        let $attachCurrent = $('.attach-active-holder input');

        $attachCurrent.on('change', applyAttach)

        function encodeID(s) {
            if (s==='') return '_';
            return s.replace(/[^a-zA-Z0-9.-]/g, function(match) {
                return '_'+match[0].charCodeAt(0).toString(16)+'_';
            });
        }
        function applyAttach(){
            let $current= $(this);

            $('.attach-text', $uploadBox).show();

            $attachCurrent.attr('name', name + '[]')
            $('.attached-holder', $uploadBox).append($attachCurrent);

            let $addedFile = $(`
                <div class="added-file">
                    <span class="added-file-name">${encodeID($attachCurrent[0].files[0].name)}</span>
                    <span class="remove-file"></span>
                </div>`);
            $('.attached-files', $uploadBox).append($addedFile);

            $('.remove-file', $addedFile).on('click', function(){
                $addedFile.remove();
                $current.remove();
            })

            $('.attach-active-holder', $uploadBox).html(`
                <input type="file" name="${name}[]" class="attach-file">
                <span class="btn">${addButtonText}</span>`);

            $attachCurrent = $('.attach-active-holder input');
            $attachCurrent.on('change', applyAttach);
        }
    })
})