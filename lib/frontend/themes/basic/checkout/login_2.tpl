{use class="Yii"}
{use class="frontend\design\Info"}
{use class="frontend\design\Block"}
{use class="yii\helpers\Html"}

{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
{\frontend\design\Info::addBoxToCss('social-login')}
{\frontend\design\Info::addBoxToCss('multi-page-checkout')}
<div class="multi-page-checkout">
  <div class="checkout-login-page checkout-step active">

      {Block::widget(['name' => 'login_checkout2', 'params' => ['type' => 'login', 'params' => $params]])}
  </div>

    <div class="checkout-step">
        <div class="checkout-heading">
            <span class="count">1</span>
            {Block::widget(['name' => 'checkout_delivery_title', 'params' => ['type' => 'checkout', 'params' => $params]])}
        </div>
    </div>
    <div class="checkout-step">
        <div class="checkout-heading">
            <span class="count">2</span>
            {Block::widget(['name' => 'checkout_payment_title', 'params' => ['type' => 'checkout', 'params' => $params]])}
        </div>
    </div>
    <div class="checkout-step">
        <div class="checkout-heading"><span class="count">3</span>
            {Block::widget(['name' => 'checkout_confirmation_title', 'params' => ['type' => 'confirmation', 'params' => $params]])}
        </div>
    </div>

</div>
