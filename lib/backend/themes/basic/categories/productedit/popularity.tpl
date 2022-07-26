{use class="\yii\helpers\Html"}
<style>
 .spinner-input{ width:100px; }
 .spinner-row{ width:100px; }
</style>
<div style="padding:10px;">
    <div class="row">
        <label>{$smarty.const.TEXT_CURRENT_POPULARITY}</label>
        {Html::textInput('products_popularity', $pInfo->products_popularity, ['class' => 'form-control spinner-input', 'readonly' => true])}
    </div>
    <div class="row">
        <label>{$smarty.const.TEXT_SIMPLE_POPULARITY}</label>
        <div class="spinner-row">
        {Html::textInput('popularity_simple', $pInfo->popularity_simple, ['class' => 'form-control spinner spinner-input'])}
        </div>
    </div>
    <div class="row">
        <label>{$smarty.const.TEXT_BESTSELLER_POPULARITY}</label>
        <div class="spinner-row">
        {Html::textInput('popularity_bestseller', $pInfo->popularity_bestseller, ['class' => 'form-control spinner spinner-input'])}
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('.spinner').spinner({ 
            step: 0.001,
        });
    })
</script>