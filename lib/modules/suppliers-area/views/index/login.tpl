{use class="\yii\helpers\Html"}
<style>
.login-box { width:50%; margin: 0 auto; }
</style>
<div>    
    {$messages}
    {Html::beginForm()}
     <div class="login-box">
        <div>
            <label>Email address</label>
            {Html::activeInput('text', $loginModel, 'email_address', ['class' => 'form-control'])}
        </div>
        
        <div>
            <label>Password</label>
            {Html::activePasswordInput($loginModel, 'password', ['class' => 'form-control'])}
        </div>
        <br/>
        <div>
            {Html::submitButton('Sign in', ['class' => 'btn btn-primary'])}            
        </div>
     </div>
    {Html::endForm()}
</div>