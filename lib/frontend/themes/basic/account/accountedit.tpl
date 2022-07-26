{use class="frontend\design\Info"}
{assign var=re1 value='.{'}
{assign var=re2 value='}'}
{use class = "yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
<h1>{$smarty.const.HEADING_TITLE}</h1>
{\frontend\design\boxes\account\AccountEdit::widget()}
<div class="buttonBox buttons"><div class="button2 left-buttons"><a class="btn" href="{$back_link}">{$smarty.const.IMAGE_BUTTON_BACK}</a></div></div>

