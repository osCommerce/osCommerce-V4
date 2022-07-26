{use class="yii\helpers\Html"}

<div class="site-error">

    <h1>{$name}</h1>

    <div class="alert alert-danger">
        {nl2br(Html::encode($message))}
    </div>
{if $smarty.const.YII_DEBUG}
    <pre>
    {$exception}
    </pre>
{/if}
    <p>
        The above error occurred while the Web server was processing your request.
    </p>
    <p>
        Please contact us if you think this is a server error. Thank you.
    </p>

</div>
