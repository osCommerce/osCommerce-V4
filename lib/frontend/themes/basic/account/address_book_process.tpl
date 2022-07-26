{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
<h1>{$title}</h1>

{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}

{$message}

{\frontend\design\boxes\account\EditAddress::widget()}

<div class="buttonBox buttons buttonedit"><div class="button2 left-buttons"><a class="btn" href="{$links.back_url}">{$links.back_text}</a></div></div>
