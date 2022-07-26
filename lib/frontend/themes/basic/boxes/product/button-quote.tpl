{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('form')}

<span class="btn btn-rfq"{if $product_has_attributes} style="display:none;"{/if}>{$smarty.const.BUTTON_REQUEST_FOR_QUOTE}</span>

{if \common\helpers\Acl::checkExtensionAllowed('Quotations', 'allowed')}
    {\common\extensions\Quotations\Quotations::productBlock()}
{else}
    <script type="text/javascript">

        tl('{Info::themeFile('/js/main.js')}' , function(){
            $('.btn-rfq').off('click').on('click', function() {
                alertMessage('\
                <div class="rfq-form">\
                {Html::beginForm(Yii::$app->urlManager->createUrl('catalog/product-request-for-quote'), 'post')|strip}\
                    <div class="middle-form">\
                        <div class="heading-3">{$smarty.const.HEADING_REQUEST_FOR_QUOTE}</div>\
                        {if !$customer_is_logged}\
                        <div class="col-full">\
                            <label>{$smarty.const.TEXT_NAME}</label>\
                            <input type="text" class="rfq-name">\
                        </div>\
                        <div class="col-full">\
                            <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}</label>\
                            <input type="text" class="rfq-email">\
                        </div>\
                        {/if}\
                        <div>\
                            <label>{$smarty.const.ENTRY_REQUEST_FOR_QUOTE_MESSAGE}</label>\
                            <textarea class="rfq-message"></textarea>\
                        </div>\
                        <div class="center-buttons"><button type="submit" class="btn">{$smarty.const.BUTTON_REQUEST_FOR_QUOTE}</button></div>\
                    </div>\
                {Html::endForm()}\
                </div>');
                $('.rfq-form').closest('.alert-message').removeClass('alert-message');
                $('.rfq-form form').on('submit', function(){
                    ajax_rfq_product()
                })
            });
        });

        if (typeof ajax_rfq_product !== "function") {
            function ajax_rfq_product() {
                var rfqForm = $('.rfq-form form');
                var check_error = false;
                var post_data = $('form[name="cart_quantity"]').serializeArray();
                {if !$customer_is_logged}
                post_data.push( { name:'_csrf', value:$('input[name="_csrf"]', rfqForm).val() } );
                post_data.push( { name:'name', value:$('.rfq-name', rfqForm).val() } );
                post_data.push( { name:'email', value:$('.rfq-email', rfqForm).val() } );
                if ($('.rfq-name', rfqForm).val() < {$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}) {
                    check_error = true;
                    alertMessage('{sprintf($smarty.const.NAME_IS_TOO_SHORT, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)|escape:'javascript'}');
                }
                if (!isValidEmailAddress($(".rfq-email", rfqForm).val())) {
                    check_error = true;
                    alertMessage('{$smarty.const.ENTER_VALID_EMAIL|escape:'javascript'}');
                }
                {/if}
                post_data.push( { name:'message', value:$('.rfq-message', rfqForm).val() } );
                if ($(".rfq-message", rfqForm).val().length==0) {
                    check_error = true;
                    alertMessage('{$smarty.const.REQUEST_MESSAGE_IS_TOO_SHORT|escape:'javascript'}');
                }
                var error = false;
                $(post_data).each(function(idx, param) {
                    if ( param.name.indexOf('id[')!==0 ) return;
                    if (!parseInt(param.value)) {
                        error = true;
                    }
                });
                if (error) {
                    check_error = true;
                    alertMessage('{$smarty.const.PLEASE_CHOOSE_ATTRIBUTES|escape:'javascript'}');
                }
                if ( !check_error ) {
                    $.ajax({
                        url: "{Yii::$app->urlManager->createUrl('catalog/product-request-for-quote')}",
                        data: post_data,
                        type: 'POST',
                        success: function(msg) {
                            rfqForm.replaceWith('<div class="rfq-form">' + msg + '</div>');
                        }
                    });
                }

                return false;
            }
        }
    </script>
{/if}
