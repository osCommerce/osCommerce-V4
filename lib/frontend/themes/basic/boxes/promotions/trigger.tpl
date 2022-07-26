<div class="promo-container">
</div>
<script>
    promoAction = {
        trigger:function($action){ 
            $.post('', { 'promo-action': $action }, function(data){
                $('.promo-container').html(data);
            }, 'html');
        }
    }
</script>