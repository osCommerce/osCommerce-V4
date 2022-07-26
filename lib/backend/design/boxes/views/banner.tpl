{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
	<input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
	{$smarty.const.TEXT_BANNER_EDIT}
  </div>
  <div class="popup-content">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.HEADING_TYPE}</a></li>
        <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
        <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">


          <div class="setting-row">
            <label for="banners_group">{$smarty.const.TEXT_BANNERS_GROUP}</label>
            <select name="setting[0][banners_group]" id="banners_group" class="form-control">
              <option value=""></option>
              {foreach $banners as $banner}
                <option value="{$banner.banners_group}"{if $banner.banners_group == $settings[0].banners_group} selected{/if}>{$banner.banners_group}</option>
              {/foreach}
              <option value="page_setting"{if $settings[0].banners_group == 'page_setting'} selected{/if}>{$smarty.const.TEXT_FROM_PAGE_SETTING}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="banners_type">{$smarty.const.TEXT_BANNERS_TYPE}</label>
            <select name="setting[0][banners_type]" id="banners_type" class="form-control">
              <option value="banner"{if $settings[0].banners_type == ''} selected{/if}>banner</option>
              <option value="slider"{if $settings[0].banners_type == 'slider'} selected{/if}>slider</option>
              <option value="carousel"{if $settings[0].banners_type == 'carousel'} selected{/if}>carousel</option>
              <option value="random"{if $settings[0].banners_type == 'random'} selected{/if}>random</option>
            </select>
          </div>

            <div class="setting-row slider-settings">
              <label for="">Effect</label>
              <select name="setting[0][effect]" id="effect" class="form-control">
                <option value=""{if $settings[0].effect == ''} selected{/if}>random</option>
                <option value="slideInRight"{if $settings[0].effect == 'slideInRight'} selected{/if}>slide in right</option>
                <option value="slideInLeft"{if $settings[0].effect == 'slideInLeft'} selected{/if}>slide in left</option>
                <option value="fold"{if $settings[0].effect == 'fold'} selected{/if}>fold</option>
                <option value="fade"{if $settings[0].effect == 'fade'} selected{/if}>fade</option>
                <option value="boxRandom"{if $settings[0].effect == 'boxRandom'} selected{/if}>box random</option>
                <option value="boxRain"{if $settings[0].effect == 'boxRain'} selected{/if}>box rain</option>
                <option value="boxRainReverse"{if $settings[0].effect == 'boxRainReverse'} selected{/if}>box rain reverse</option>
                <option value="boxRainGrow"{if $settings[0].effect == 'boxRainGrow'} selected{/if}>box rain grow</option>
                <option value="boxRainGrowReverse"{if $settings[0].effect == 'boxRainGrowReverse'} selected{/if}>box rain grow reverse</option>
              </select>
            </div>
            <div class="setting-row slider-settings">
              <label for="">Box columns</label>
              <input type="number" name="setting[0][boxCols]" value="{$settings[0].boxCols}" class="form-control" placeholder="8"/>
            </div>
            <div class="setting-row slider-settings">
              <label for="">Box rows</label>
              <input type="number" name="setting[0][boxRows]" value="{$settings[0].boxRows}" class="form-control" placeholder="4"/>
            </div>
            <div class="setting-row slider-settings">
              <label for="">Animation speed (ms)</label>
              <input type="text" name="setting[0][animSpeed]" value="{$settings[0].animSpeed}" class="form-control" placeholder="500"/>
            </div>
            <div class="setting-row slider-settings carousel-settings">
              <label for="">Pause time (ms)</label>
              <input type="text" name="setting[0][pauseTime]" value="{$settings[0].pauseTime}" class="form-control" placeholder="3000"/>
            </div>
            <div class="setting-row slider-settings">
              <label for="">Direction navigation</label>
              <select name="setting[0][directionNav]" id="effect" class="form-control">
                <option value=""{if $settings[0].directionNav == ''} selected{/if}>Yes</option>
                <option value="false"{if $settings[0].directionNav == 'false'} selected{/if}>No</option>
              </select>
            </div>
            <div class="setting-row slider-settings">
              <label for="">Control navigagion</label>
              <select name="setting[0][controlNav]" id="effect" class="form-control">
                <option value=""{if $settings[0].controlNav == ''} selected{/if}>Yes</option>
                <option value="false"{if $settings[0].controlNav == 'false'} selected{/if}>No</option>
              </select>
            </div>
            <div class="setting-row slider-settings">
              <label for="">Control navigation thumbnailss</label>
              <select name="setting[0][controlNavThumbs]" id="effect" class="form-control">
                <option value=""{if $settings[0].controlNavThumbs == ''} selected{/if}>No</option>
                <option value="true"{if $settings[0].controlNavThumbs == 'false'} selected{/if}>Yes</option>
              </select>
            </div>
            <div class="setting-row slider-settings">
              <label for="">Pause on hover</label>
              <select name="setting[0][pauseOnHover]" id="effect" class="form-control">
                <option value=""{if $settings[0].pauseOnHover == ''} selected{/if}>Yes</option>
                <option value="false"{if $settings[0].pauseOnHover == 'false'} selected{/if}>No</option>
              </select>
            </div>
            <div class="setting-row slider-settings">
              <label for="">Manual advance</label>
              <select name="setting[0][manualAdvance]" id="effect" class="form-control">
                <option value=""{if $settings[0].manualAdvance == ''} selected{/if}>No</option>
                <option value="true"{if $settings[0].manualAdvance == 'false'} selected{/if}>Yes</option>
              </select>
            </div>

            {include 'include/lazy_load.tpl'}


          <div class="setting-row">
            <label for="">Preload (Largest Contentful Paint)</label>
            <select name="setting[0][preload]" class="form-control">
              <option value=""{if $settings[0].preload == ''} selected{/if}>No</option>
              <option value="1"{if $settings[0].preload == '1'} selected{/if}>Yes</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">webp</label>
            <select name="setting[0][dont_use_webp]" class="form-control">
              <option value=""{if $settings[0].dont_use_webp == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
              <option value="1"{if $settings[0].dont_use_webp == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
            </select>
          </div>



<script type="text/javascript">
  (function($){
    $(function(){
      $('#banners_type').on('change', function(){
        if ($(this).val() == '' || $(this).val() == 'banner' || $(this).val() == 'random'){
          $('.slider-settings').hide();
          $('.carousel-settings').hide();
            $('.lazy-load').show();
        }
        if ($(this).val() == 'slider'){
          $('.carousel-settings').hide();
          $('.slider-settings').show();
            $('.lazy-load').show();
            $('.lazy-load select').val('').trigger('change');
            $('.lazy-load').hide();
        }
        if ($(this).val() == 'carousel'){
          $('.slider-settings').hide();
          $('.carousel-settings').show();
        }
      }).trigger('change')
    })
  })(jQuery);
</script>

          {include 'include/ajax.tpl'}


        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>




  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>