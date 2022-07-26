{use class="yii\helpers\Html"}
<div class="widget box box-no-shadow promos-box">
    <div class="widget-header widget-header-discount">
        <h4>{$smarty.const.TEXT_PERSONAL_PROMOTIONS}</h4></a>
    </div>
    <div class="widget-content">
        {if $promos}
            {Html::dropDownList('promo_id', null, $promos, ['class' => 'form-control', 'prompt' => $smarty.const.PULL_DOWN_DEFAULT])}
        {else}
            <label>No more promos</label>
        {/if}
    </div>
    <div class="note-block noti-btn">
      <div class="btn-left"><button class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</button></div>
      <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
    <script>
        (function($){
            $('.promos-box .btn-primary').click(function(){
                $.post('groups/add-promo', {
                    'groups_id': '{$groups_id}',
                    'promo_id': $('select[name=promo_id]').val(),
                }, function (data, status){
                    $('.promo-box').replaceWith(data);
                    $('.pop-up-close:last').trigger('click');
                }, 'html');
            })
        })(jQuery)
    </script>
</div>