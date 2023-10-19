<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fileupload/jquery.fileupload.js"></script>
<h4>
    {$title} ({$social->module})
</h4>
<div>
			  {if {$messages|default:array()|@count} > 0}
			   {foreach $messages as $type => $message}
              <div class="alert alert-{$type} fade in">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>			   
			   {/foreach}
			  {/if}

    <form name="social_form" method="post" action="socials/save" enctype="multipart/form-data">
    <input type="hidden" name="platform_id" value="{$platform_id}">
    <input type="hidden" name="socials_id" value="{$socials_id}">
    <div class="tabbable tabbable-custom">
              <ul class="nav nav-tabs top_tabs_ul main_tabs">
                {if $social->hasAuth}
                <li class="active" data-bs-toggle="tab" data-bs-target="#tab_main"><a><span>{$smarty.const.TEXT_AUTHORIZATION}</span></a></li>
                {/if}
                {assign var=i value=0}
                {foreach $social->addon_settings as $block => $values}
                <li class="{if !$social->hasAuth && !$i}active{/if}" data-bs-toggle="tab" data-bs-target="#tab_{$block}"><a><span>{ucfirst($block)}</span></a></li>
                {$i++|void}
                {/foreach}
                  <li data-bs-toggle="tab" data-bs-target="#view"><a>View</a></li>
              </ul>
              <div class="tab-content">
              {if $social->hasAuth}
                <div class="tab-pane active topTabPane tabbable-custom" id="tab_main">
                      <div class="tabbable tabbable-custom">   
                          <div class="tab-inserted">
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_CLIENT_ID}</div>
                                    <div class="main_value">{\yii\helpers\Html::input('text', "settings[auth][client_id]", $social->client_id, ['class' => 'form-control'])}</div>
                                  </div>
                                  <div class="template_cell">
                                    <div class="main_title">{$smarty.const.TEXT_CLIENT_SECRET}</div>
                                    <div class="main_value">{\yii\helpers\Html::input('text', "settings[auth][client_secret]", $social->client_secret, ['class' => 'form-control'])}</div>
                                  </div>
                                  <center><a class="btn btn-primary popup" href="{$test_url}">{$smarty.const.IMAGE_TEST}</a></center>
                                  <a class="message"></a>
                          </div>
                      </div>
                </div>
                {/if}
                {assign var=i value=0}
                {foreach $social->addon_settings as $block => $values}
                 <div class="tab-pane topTabPane tabbable-custom {if !$social->hasAuth && !$i}active{/if}" id="tab_{$block}">
                      <div class="tabbable tabbable-custom">
                          <div class="tab-inserted">                            
                                {foreach $values as $key => $info}
                                <div class="template_cell">
                                    <div class="main_title">{$info['description']}</div>
                                    <div class="main_value">{\yii\helpers\Html::input('text', "settings[`$block`][`$key`]", $info['value'], ['class' => 'form-control'])}</div>
                                </div>
                            {/foreach}
                          </div>
                      </div>
                </div>
                {$i++|void}
                {/foreach}


                  <div class="tab-pane topTabPane tabbable-custom" id="view">
                      <div class="tabbable tabbable-custom">
                          <div class="tab-inserted">
                              <div class="template_cell">
                                  <div class="main_title">{$smarty.const.TEXT_SOCIAL_HOMEPAGE_LINK}</div>
                                  <div class="main_value">{\yii\helpers\Html::input('text', "settings[link]", $social->link, ['class' => 'form-control'])}</div>
                              </div>
                              <div class="template_cell">
                                  <div class="main_title">{$smarty.const.TEXT_SOCIAL_CSS_CLASS}</div>
                                  <div class="main_value">{\yii\helpers\Html::input('text', "settings[css_class]", $social->css_class, ['class' => 'form-control'])}</div>
                              </div>

                              <div class="row">
                                  <div class="col-md-6">
                                      <div class="widget box">
                                          <div class="widget-header">
                                              <h4>{$smarty.const.TEXT_SOCIAL_IMAGE}</h4>
                                          </div>
                                          <div class="widget-content">
                                              {\backend\design\Image::widget([
                                              'name' => 'settings[image]',
                                              'value' => {$social->image},
                                              'upload' => 'settings[image_upload]',
                                              'unlink' => false
                                              ])}
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
    {if $socials_id}
        <input type="hidden" name="module" value="{$social->module}">        
    {/if}    
    <div class="btn-bar">
        <div class="btn-left"><a href="{\yii\helpers\Url::to(['socials/', 'platform_id' => $platform_id])}" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
    </form>
</div>
