<ul class="nav navbar-nav navbar-left hidden-xs hidden-sm">
    <li>
        <a id="helpLink" href="https://www.oscommerce.com/wiki/Main_Page" target="_blank">
            Help
        </a>
    </li>
</ul>
<script type="text/javascript">
if(document.addEventListener){ //code for Moz
    document.addEventListener("keydown",keyCapt,false); 
}else{
    document.attachEvent("onkeydown",keyCapt); //code for IE
}
function keyCapt(e){
    if(typeof window.event!="undefined"){
        e=window.event;//code for IE
    }
    if(e.type=="keydown"&&e.keyCode==112){
        document.getElementById('helpLink').click()
    }
}
</script>