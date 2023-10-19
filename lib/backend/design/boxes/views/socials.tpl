{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    Socials Posts
  </div>
    <div class="popup-content box-img">
        <div class="tabbable tabbable-custom">
            <div class="nav nav-tabs">
                <div class="active" data-bs-toggle="tab" data-bs-target="#type"><a>Socials</a></div>
                <div data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></div>
                <div data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></div>
                <div data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></div>
            </div>
            <div class="tab-content">
                <div class="tab-pane active menu-list" id="type">
                    <div class="tabbable tabbable-custom">
                        <div class="nav nav-tabs">
                            <div class="active" data-bs-toggle="tab" data-bs-target="#facebook"><a>Facebook</a></div>
                            <div data-bs-toggle="tab" data-bs-target="#instagram"><a>Instagram</a></div>
                            <div data-bs-toggle="tab" data-bs-target="#twitter"><a>Twitter</a></div>
							<div data-bs-toggle="tab" data-bs-target="#youtube"><a>YouTube</a></div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane active" id="facebook">
                                <div class="setting-row">
                                    <label for="">Count posts</label>
                                    <input type="text" name="setting[0][fb_count]" class="form-control" style="width: 243px;" value="{$settings[0].fb_count}"/>
                                </div>
                            </div>
                            <div class="tab-pane" id="instagram">
                                {*<div class="setting-row">
                                    <label for="">HashTag</label>
                                    <input type="text" name="setting[0][insta_hashtag]" class="form-control" style="width: 243px;" value="{$settings[0].insta_hashtag}"/>
                                </div>*}
                                <div class="setting-row">
                                    <label for="">Count posts</label>
                                    <input type="text" name="setting[0][insta_count]" class="form-control" style="width: 243px;" value="{$settings[0].insta_count}"/>
                                </div>
                            </div>
                            <div class="tab-pane" id="twitter">
                                <div class="setting-row">
                                    <label for="">HashTag</label>
                                    <input type="text" name="setting[0][tw_hashtag]" class="form-control" style="width: 243px;" value="{$settings[0].tw_hashtag}"/>
                                </div>
                                <div class="setting-row">
                                    <label for="">Count posts</label>
                                    <input type="text" name="setting[0][tw_count]" class="form-control" style="width: 243px;" value="{$settings[0].tw_count}"/>
                                </div>
                            </div>
							<div class="tab-pane" id="youtube">
								<div class="setting-row">
                                    <label for="">Count videos</label>
                                    <input type="text" name="setting[0][yt_count]" class="form-control" style="width: 243px;" value="{$settings[0].yt_count}"/>
                                </div>
                                <div class="setting-row">
                                    <label for="">Playlist ID</label>
                                    <input type="text" name="setting[0][yt_playlist_id]" class="form-control" style="width: 243px;" value="{$settings[0].yt_playlist_id}"/>
                                </div>
							</div>
                        </div>
                    </div>
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
    <script type="text/javascript">
      $('.btn-cancel').on('click', function(){
        $('.popup-box-wrap').remove()
      })
    </script>

  </div>
</form>
<script type="text/javascript">
  /*$(function(){
    $('.nav-tabs a').on('click', function(){
      $(this).tab('show');
      $(this).closest('.nav-tabs').find('> div').removeClass('active');
      $(this).parent().addClass('active');
      return false;
    });
  });
  $('#box-save').on('submit', function(){
    var values = $(this).serializeArray();
    values = values.concat(
            $('input[type=checkbox]:not(:checked)', this).map(function() {
              return { "name": this.name, "value": 0}
            }).get()
    );
    values = values.concat(
            $('.visibility input[disabled]', this).map(function() {
              return { "name": this.name, "value": 1}
            }).get()
    );
    $.post('design/box-save', values, function(){ });
    setTimeout(function(){
      $(window).trigger('reload-frame')
    }, 300);
    return false
  })*/
</script>