
export default function ($box, options) {
    $('.btn-from-gallery', $box).on('click', function(){
        const type = options.type || 'image';
        const path = options.path;
        var _this = $(this);
        var name = $(this).data('name');
        if (name == undefined) name = 'params';
        var theme_name = $(this).closest('form').find('input[name="theme_name"]').val();
        var filter = '';
        if (!theme_name) {
            theme_name = '';
        } else {
            filter = `<select class="form-control folder-name" name="folder_name">
                        <option value="3">${entryData.tr.TEXT_THEMES_FOLDER}</option>
                        <option value="2">${entryData.tr.TEXT_GENERAL_FOLDER}</option>
                        <option value="1">${entryData.tr.TEXT_ALL_FILES}</option>
                    </select>`;
        }
        $.get(entryData.baseUrl + 'design/gallery', { type, theme_name, path}, function(d){
            $('body').append('<div class="images-popup"><div class="close"></div><div class="search"><input type="text" class="form-control">'+filter+'</div><div class="image-content">'+d+'</div></div>');
            $('.images-popup .item-general').hide();
            $('.images-popup .item').on('click', function(){
                var img = $('.name', this).text();
                var path = $('.name', this).data('path');
                if (!path) path = '';
                $('input.file-name', $box).val(path + img).trigger('change');
                $('.images-popup').remove();
                //if (name == 'params'){
                //if (name == 'params'){
                    $('.uploaded-image img', $box).attr('src', entryData.baseUrl+'../' + path + img)
                //} else {
                //    $('.show-image[data-name="'+name+'"]').attr('src', entryData.baseUrl+'/../' + path + img).closest('video').trigger('load')
                //}
            });
            $('.images-popup .close').on('click', function(){
                $('.images-popup').remove()
            });

            if (!theme_name){
                $('.images-popup .item').show();
                $('.images-popup .item-themes').hide();
            }

            $('.images-popup .search .folder-name').on('change', function(){
                if ($(this).val() == 1){
                    $('.images-popup .item').show()
                }
                if ($(this).val() == 2){
                    $('.images-popup .item').show();
                    $('.images-popup .item-themes').hide();
                }
                if ($(this).val() == 3){
                    $('.images-popup .item').show();
                    $('.images-popup .item-general').hide();
                }
            })

            $('.images-popup .search input').on('keyup', function(){
                var val = $(this).val();

                $('.images-popup .name').each(function(){
                    if ($(this).text().search(val) != -1){
                        $(this).parent().show()
                    } else {
                        $(this).parent().hide()
                    }
                });

                if (val == '') $('.images-popup .item').show();

                if ($('.images-popup .search .folder-name').val() == 2){
                    $('.images-popup .item-themes').hide();
                }
                if ($('.images-popup .search .folder-name').val() == 3){
                    $('.images-popup .item-general').hide();
                }
            })
        })
    })
}