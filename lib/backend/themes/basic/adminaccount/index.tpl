{use class="common\helpers\Html"}
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>

<div id="load"></div>

{if $expiredFlag}
    <div class="alert alert-warning fade in">
        <i data-dismiss="alert" class="icon-remove close"></i>
        {$smarty.const.PLEASE_CHANGE_PASSWORD}
    </div>
{/if}

<script type="text/javascript">

    $(document).ready(function(){
        $("#adminaccount_management").hide();
        loadMyAccount(0);
        
        $('input.hide-lang').on('change', function(){
            var id = $(this).data('id');
            var hide = 0;

            if (this.checked) {
                hide = 1;
            }

            
            $.post("adminaccount/hide-language", { 'id': id, 'hide': hide }, function(data, status){
                if (status == "success") {
                    if (data.ok == 1) {
                        //if (hide==1) {
                        if (jQuery.inArray(id, data.hidden_admin_language) !== -1) {
                            $('#lang_wrap_' + id).addClass('dis_module');
                        } else {
                            $('#lang_wrap_' + id).removeClass('dis_module');
                        }
                        
                        $('.popup').popUp({
                                        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_NOTIFIC|escape}</div><div class='alert alert-success'>{$smarty.const.TEXT_MESSEAGE_SUCCESS|escape}</div></div></div>",
                                        
                                        event:'show'
                         });

                        setTimeout(function(){
                            closePopup();
                        }, 1500);


                    } else {
                        alert("{$smarty.const.TEXT_MESSAGE_ERROR|escape:'javascript'}");
                    }
                } else {
                    alert("Request error.");
                }
            },"json");

            return false;
        });
    });
		function closePopup() {
				$('.popup-box').trigger('popup.close');
				$('.popup-box-wrap').remove();
				return false;
		}
    function loadMyAccount(admin_id) {
        $.post("adminaccount/adminaccountactions", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
								var result = $(data).find('.account1 .ac_value a').text();
								var result_img = $(data).find('div.avatar img').attr('src');
								if($('span.avatar img').attr('src') !=result_img ){
								if(typeof result_img != 'undefined' && result_img.length > 0){
									$('span.avatar').remove('');									
									$('.user .dropdown-toggle').html('<span class="avatar"><img src="'+result_img+'"></span><span class="username">'+result+'</span>');
								}else{
									$('span.avatar img').remove();
									$('span.avatar').addClass('avatar_noimg');
									$('span.avatar').append('<i class="icon-user"></i>');
								}
								}
								$('.dropdown .username').text(result);
                $('#adminaccount_info_data').html(data);
                $("#adminaccount_info").show();
                $('.popup').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_EDITING_ACCOUNT}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
     });
            } else {
                alert("Request error.");

            }
        },"html");
    }

  /*  function getChangeForm(){
        $("#adminaccount_management").show();
        $("#admin_info_collapse").click();
        $.post("adminaccount/getpasswordform", { }, function(data, status){
            if (status == "success") {
                $('#adminaccount_management_data').html(data);
                // $("#adminaccount_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    }*/

    function hidePasswordForm(refresh){
        $("#adminaccount_management").hide();
        $("#admin_info_collapse").click();

        if(refresh == 1){
            loadMyAccount(0);
        }
    }

    function check_form(admin_id) {
        //ajax save
        $("#admin_management").hide();
        var admin_id = $( "#password_change_form input[name='admin_id']" ).val();
        $.post("adminaccount/passwordsubmit", $('#password_change_form').serialize(), function(data, status){
            if (status == "success") {
                //$('#admin_management_data').html(data);
                //$("#admin_management").show();
                $('#adminaccount_management_data').html(data);
                $("#adminaccount_management").show();
            } else {
                alert("Request error.");
                //$("#adminaccount_management").hide();
            }
        },"html");
        //$('#adminaccount_management_data').html('');
        return false;
    }
		function checkPassword() {
			var admin_id = $( "#check_pass_form input[name='admin_id']" ).val();
        $.post("adminaccount/checkpassword", $('#check_pass_form').serialize(), function(data, status){
            if (status == "success") {
							if($(data).filter('form').text().length > 0){
							$('#accountpopup #check_pass_form').remove();
								$('#accountpopup').html(data);
							}else{
								$('#accountpopup .alert-warning').remove();
								$('#accountpopup').prepend(data);
							}
            } else {
                alert("Request error.");
            }
        },"html");
				return false;
		}

    function saveAccount() {
        var admin_id = $( "#save_account_form input[name='admin_id']" ).val();
        var popupname = $( "#save_account_form input[name='popupname']" ).val();
				
        $.post("adminaccount/saveaccount",$('#save_account_form').serialize(), function(data, status){
            if (status == "success") { 
								$('#accountpopup').html(data);
								setTimeout(function(){									
									closePopup();
									loadMyAccount(admin_id);
								}, 5000)
								
              //  $("#adminaccount_management").show();
            } else {
                alert("Request error.");

            }
        },"html");

        return false;
    }
		function deleteImage(){
			var admin_id = $(this).data('admin_id');
			$.post("adminaccount/deleteimage",{ 'admin_id' : admin_id }, function(data, status){
            if (status == "success") { 								
								loadMyAccount(admin_id);
								$('body').append(data);
								setTimeout(function(){
									closePopup();									
								}, 1000)
								
              //  $("#adminaccount_management").show();
            } else {
                alert("Request error.");

            }
        },"html");

        return false;
		}
    function changeFormCollapse() {
        $("#adminaccount_management").hide();
        $('#adminaccount_management_data').html('');
        $('#admin_info_collapse').click();
    }

$(document).ready(function(){
    $(window).resize(function(){
        var height_line = $('.account_wrapper').height();
        $('.account_wrapper > div').css('min-height', height_line);
    })
    $(window).resize();
  $.fn.uploads1 = function(options){
  var option = jQuery.extend({
    overflow: false,
    box_class: false
  },options);

  var body = $('body');
  var html = $('html');

  return this.each(function() {

    var _this = $(this);

    _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">{$smarty.const.TEXT_DROP_FILES|escape:javascript}<br>{$smarty.const.TEXT_OR|escape:javascript}<br><span class="btn">{$smarty.const.IMAGE_UPLOAD|escape:javascript}</span></div>\
      <div class="upload-file"></div>\
      <div class="upload-hidden"><input type="hidden" name="'+_this.data('name')+'"/></div>\
    </div>');


    $('.upload-file', _this).dropzone({
      url: "{Yii::$app->urlManager->createUrl('upload')}",
      sending:  function(e, data) {
        $('.upload-hidden input[type="hidden"]', _this).val(e.name);
        $('.upload-remove', _this).on('click', function(){
          $('.dz-details', _this).remove()
        })
      },
      dataType: 'json',
      previewTemplate: '<div class="dz-details"><img data-dz-thumbnail /><div class="upload-remove"></div></div>',
      drop: function(){
        $('.upload-file', _this).html('')
      }
    });

  })
};   
})
</script>
{$accountFeatures = DEPARTMENTS_ID}
<div class="account_wrapper after{if $accountFeatures  > 0}{else} full_account{/if}">
    <div class="account_right  {if $accountFeatures  > 0}account_right_dep{/if}">
        <div class="sub_acc_title"><i class="icon-user"></i>{$smarty.const.TEXT_MAIN_DETAILS}
				<div class="admin_lang after">
				<span class="title_lang">{$smarty.const.TEXT_LANGUAGES}</span>
                {$languages = \common\helpers\Language::get_languages(false, true)}
                {foreach $languages as $lKey => $lItem}
                    <span class="{if in_array($lItem['id'], $global_hidden_admin_language) }dis_module{/if}" id='lang_wrap_{$lItem['id']}'
                          {if in_array($lItem['id'], $global_hidden_admin_language)  && !in_array($lItem['id'], $hidden_admin_language)}
                              title="{$smarty.const.TEXT_HIDDEN_IN_ADMIN}"
                          {/if}
                    >
                    <a href="{Yii::$app->urlManager->createUrl(['adminaccount', 'language' => $lItem['code']])}">{$lItem['image_svg']}</a>
                    {if strtolower(DEFAULT_LANGUAGE) neq strtolower($lItem['code'])}
                    <span title="{$smarty.const.TEXT_HIDE_FOR_ME}" class="hide-action">{Html::checkbox('hide4me', in_array($lItem['id'], $hidden_admin_language), ['class' => 'hide-lang', 'data-id' => $lItem['id']])}</span>
                    {/if}
                    </span>
                {/foreach}
				</div>
				</div>
        <div id="adminaccount_info">     
            <div class="admin_pad" id="adminaccount_info_data"></div>
        </div>
    </div>
</div>
<!--===Admin account info ===-->

<!--===Admin account info ===-->

<!--===Password change form ===-->
<div class="row" id="adminaccount_management">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i>{$smarty.const.TEXT_PERSONAL_DATA_CHANGE}</h4>
                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="passwordchange_form_collapse" class="btn btn-xs widget-collapse">
                            <i class="icon-angle-down"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style" id="adminaccount_management_data">
                
            </div>
        </div>
    </div>
</div>
<!--===Password change form ===-->