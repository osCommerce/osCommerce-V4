<div class="upload-image">
  <div class="upload-image-left">
    <h4>{$smarty.const.UPLOAD_FROM_COMPUTER}</h4>
    <div class="upload" data-name="{$upload}" style="overflow: hidden"></div>
  </div>
  <div class="upload-image-or">
    <h4>or</h4>
  </div>
  <div class="upload-image-right">
    <h4>{$smarty.const.UPLOAD_FROM_GALLERY}</h4>
    <span class="btn btn-upload" data-name="{$name}">{$smarty.const.IMAGE_UPLOAD}</span>
    <div class="from-gallery"></div>
  </div>
  <div class="upload-progress">
    <div class="upload-progress-percent"></div>
    <div class="upload-progress-bar"><div class="upload-progress-bar-content"></div></div>
    <div class="upload-progress-val"></div>
  </div>
  <div class="uploaded-wrap">
    <div class="uploaded" data-name="{$name}" {if !$value} style="display: none" {/if}>
      {if $type == 'video'}
        <video class="video-js" width="200px" height="150px" controls>
          <source src="{$file}" class="show-image" data-name="{$name}">
        </video>
      {else}
        <img src="{$file}" alt="" class="show-image" data-name="{$name}">
      {/if}
      <div>
        {if $unlink}
        <span class="btn-unlink" data-name="{$name}" title="{$smarty.const.TEXT_UNLINK}"></span>
        {/if}
        {if $delete}
        <span class="btn-remove" data-name="{$name}" title="{$smarty.const.IMAGE_DELETE}"></span>
        {/if}
      </div>
    </div>
  </div>
  <input type="hidden" name="{$name}" value="{$value}"/>
  {if $delete && $value}
  <input type="hidden" class="delete" name="{$delete}" value="0" data-name="{$name}"/>
  {/if}
</div>

<script type="text/javascript">
  $(function(){

    $('.upload[data-name="{$upload}"]')
            .uploads({ 'acceptedFiles': '{$acceptedFiles}'})
            .on('upload', function(){
              var uploaded = $('.uploaded[data-name="{$name}"] img, .uploaded[data-name="{$name}"] source');
              uploaded.attr('src', '{\Yii::getAlias('@web')}/uploads/' + $('input[type="hidden"]', this).val())
                      .closest('video')
                      .trigger('load');
              uploaded.closest('.uploaded').show();
              $('input.delete[data-name="{$name}"]').remove();
              $('.btn-remove[data-name="{$name}"]').remove();
            })
            .on('upload-remove', function(){
              $('.uploaded[data-name="{$name}"] img, .uploaded[data-name="{$name}"] source')
                      .attr('src', '')
                      .closest('.uploaded').hide();
              $('input.delete[data-name="{$name}"]').remove();
              $('.btn-remove[data-name="{$name}"]').remove();
            })
            .on('error', function(file, response) {
              $(file.previewElement).find('.dz-error-message').text(response);
            });

    $('.btn-upload[data-name="{$name}"]')
            .galleryImage('{$app->request->baseUrl}', '{$type}', '{$path}')
            .on('choose-image', function(){
              $('.upload[data-name="{$upload}"] input[type="hidden"]').val('');
              $('.upload[data-name="{$upload}"] .dz-details').remove();
              $('.uploaded[data-name="{$name}"]').show();
            });

    $('.btn-unlink[data-name="{$name}"]').on('click', function(){
      $('input[name="{$name}"]').val('');
      $('.uploaded[data-name="{$name}"]').hide()
    });

    $('.btn-remove[data-name="{$name}"]').on('click', function(){
      $('input[name="{$name}"]').val('');
      $('.uploaded[data-name="{$name}"]').hide();
      $('input.delete[data-name="{$name}"]').val('1')
    })

  })
</script>