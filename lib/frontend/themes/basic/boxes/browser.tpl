<a href="#ieCheck" class="ieLink"></a>
<div id="ieCheck" style="display:none;">{$smarty.const.TEXT_UNSUPPORTED_BROWSER}</div>
<script>
tl('themes/basic/js/main.js', function(){
$('.ieLink').popUp({
'box_class': 'iePopup'
});
var isIE = false;
var ua = window.navigator.userAgent;
var old_ie = ua.indexOf('MSIE ');
var new_ie = ua.indexOf('Trident/');

if ((old_ie > -1) || (new_ie > -1)) {
    isIE = true;
}

if ( isIE ) {
    $('.ieLink').click();
}
})
</script>