{use class="yii\bootstrap\Nav"}

<style>
.nav{ background: #cae7ef; }
.nav > li{ display: inline-block!important; }
.nav > li:last-child{ float: right; }
</style>
{Nav::widget(['items' => $items ])}
