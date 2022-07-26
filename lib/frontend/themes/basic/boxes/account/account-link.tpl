{if !isset($settings[0].popup)}{$settings[0].popup = ''}{/if}{if !isset($settings[0].like_button)}{$settings[0].like_button = ''}{/if}<a href="{$url}" {if $settings[0].popup == 2} target="_blank" {/if} class="{if $settings[0].popup == 1} popup-link {/if}{if $settings[0].like_button == 1} btn {/if}{if $settings[0].like_button == 2} btn-1 {/if}{if $settings[0].like_button == 3} btn-3 {/if}{if $settings[0].like_button == 4} btn-2 {/if}{if isset($active) && $active} active{/if}"{if isset($settings[0].popup_class) && $settings[0].popup_class} data-class="{$settings[0].popup_class}" {/if}><span>{$text}</span></a>
{if $settings[0].link == 'delete'}
    {\frontend\design\Info::addBoxToCss('delete-account')}
    <script>
        tl('{\frontend\design\Info::themeFile('/js/main.js')}', function(){
            var box = $('#box-{$id}');
            $('a', box).on('click', function(e){
                e.preventDefault();

                var deleteUrl = $(this).attr('href');

                var deleteBox = $('<div class="delete-box"><div>{sprintf($smarty.const.TYPE_DELETE_YOUR_ACCOUNT, $smarty.const.TYPE_DELETE)}</div></div>');
                var messageBox = $('<div class="delete-message"></div>');
                var inputBox = $('<div><input type="text"></div>');
                var buttonsBox = $('<div></div>');
                var btnCancel = $('<span class="btn btn-cancel">{$smarty.const.CANCEL}</span>');
                var btnDelete = $('<span class="btn btn-delete">{$smarty.const.IMAGE_BUTTON_DELETE}</span>');

                deleteBox.append(messageBox).append(inputBox).append(buttonsBox);
                buttonsBox.append(btnCancel).append(btnDelete)

                alertMessage(deleteBox);

                btnCancel.on('click', function(){
                    $('.popup-box-wrap').remove()
                });
                btnDelete.on('click', function(){
                    var val = $('input', inputBox).val();
                    if (!val) {
                        messageBox.html("{sprintf($smarty.const.YOUR_DONT_TYPED, $smarty.const.TYPE_DELETE)}")
                    } else if (val.toLowerCase().trim() === '{$smarty.const.TYPE_DELETE|escape:'html'}'.toLowerCase().trim()) {
                        window.location = deleteUrl;
                    } else {
                        messageBox.html("{$smarty.const.YOUR_TYPED_WRONG_WORD}")
                    }
                });

                return false
            })
        })
    </script>
{/if}