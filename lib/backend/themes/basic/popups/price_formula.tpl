<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="{$app->view->theme->baseUrl}/css/base.css" rel="stylesheet" type="text/css" />
    <link href="{$app->view->theme->baseUrl}/css/main.css" rel="stylesheet" type="text/css" />
    <link href="{$app->view->theme->baseUrl}/css/style.css" rel="stylesheet" type="text/css" />
    {*    <link rel="stylesheet" href="/resources/demos/style.css">*}
    <style>

        .calculator ul {
            list-style-type: none;
            margin: 0; padding: 0;
            margin-bottom: 10px;
        }
        .calculator li {
            margin: 5px;
            padding: 5px;
            width: 220px;
            cursor: move;
            line-height: 22px;					
        }
		.calculator li.ui-state-highlight{
			font-size:14px;
			font-weight:700;
			border:1px solid #bdbdbd;
			color:#424242;
			background:#f5f5f5;	
		}
        .calculator .sortable {
            min-height: 150px;
            overflow: hidden;
        }
        .calculator li {
            display: inline-block;
            vertical-align: middle;
            position: relative;
            min-width: 50px;
            text-align: center;
            height: auto !important;
            width: auto !important;
        }
        .calculator .remove {
            position: absolute;
            right: 0;
            top: 0;
            width: 28px;
            height: 28px;
            line-height: 26px;
            text-align: center;
            font-size: 20px;
            cursor: pointer;
            display: none !important;
            background: #eee;
        }
        .calculator .sortable li:hover .remove {
            display: block !important;
        }
        .calculator .sortable-formula {
            min-width: 50px;
            min-height: 18px;
            margin: 0;
            display: inline-block;
            vertical-align: middle;
			border:1px solid #bdbdbd;
        }
        .calculator .sortable .sortable-formula {
            min-width: 100px;
            min-height: 30px;
            padding-right: 30px;
        }
        .calculator .parenthesis-left {
            display: inline-block;
            vertical-align: middle;
        }
        .calculator .parenthesis-right {
            display: inline-block;
            vertical-align: middle;
        }
        .calculator input[type="text"] {
            width: 50px;
            text-align: center;
			line-height: 1.428571429;
			border: 1px solid #ccc;
			box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
			transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
			box-sizing: border-box;
        }
		
    </style>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery.min.js"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script>
        function deleteMe(obj) {
            $(obj).parent('li').remove();
            $('#formula').trigger('formulaChanged');
        }
        function subcalc(obj) {
            var text = '';
            var type, subtext;
            var broken = false;

            var prev_type = '';

            $(obj).children('li').each(function( i, section ) {
                //console.log($(this).text());
                type = $(this).children('input[type="hidden"]').val();
                if (type == undefined) {
                    type = $(this).children('input[type="text"]').val();
                }
                if (type == '%') {
                    type = $(this).children('input[type="text"]').val() + '%';
                }
                if (type == '()') {
                    subtext = subcalc($(this).find('ul'));
                    if (subtext == '') {
                        broken = true;
                        //return '';
                    }
                    type = '(' + subtext + ')';
                }
                if (type == '()M') {
                    subtext = subcalc($(this).find('ul'));
                    if (subtext == '') {
                        broken = true;
                        //return '';
                    }
                    type = '((' + subtext + ')+MARGIN)';
                }
                if (type == '+' || type == '-' || type == '*' || type == '/') {
                    if (prev_type == '' || prev_type == '+' || prev_type == '-' || prev_type == '*' || prev_type == '/') {
                        broken = true;
                    }
                } else {
                    if (prev_type != '' && prev_type != '+' && prev_type != '-' && prev_type != '*' && prev_type != '/') {
                        broken = true;
                    }
                }
                if (type == '' || type == '%' ) {
                    broken = true;
                }
                prev_type = type;
                text += type;

            });
            if (type == '+' || type == '-' || type == '*' || type == '/') {
                broken = true;
            }
            if (broken) {
                return '';
            }
            return text;
        }
        function calc() {
            var text = '';
            text = subcalc($('#formula'));
            if (text == '') {
                text = 'Invalid formula';
            }
            $('#result').text(text);
            calcMassiv();
        }
        function subcalcMassiv(obj) {
            var text = [];
            var type, subtext;
            var broken = false;

            var prev_type = '';

            $(obj).children('li').each(function( i, section ) {
                type = $(this).children('input[type="hidden"]').val();
                if (type == undefined) {
                    type = $(this).children('input[type="text"]').val();
                }
                if (type == '%') {
                    type = $(this).children('input[type="text"]').val() + '%';
                }
                if (type == '()') {
                    type = subcalcMassiv($(this).find('ul'));
                    /*if (subtext == '') {
                        broken = true;
                        //return '';
                    }*/
                    //type = '(' + subtext + ')';
                }
                if (type == '()M') {
                    type = ['()M', subcalcMassiv($(this).find('ul')) ];
                }
                /*if (type == '+' || type == '-' || type == '*' || type == '/') {
                    if (prev_type == '' || prev_type == '+' || prev_type == '-' || prev_type == '*' || prev_type == '/') {
                        broken = true;
                    }
                } else {
                    if (prev_type != '' && prev_type != '+' && prev_type != '-' && prev_type != '*' && prev_type != '/') {
                        broken = true;
                    }
                }
                if (type == '' || type == '%' ) {
                    broken = true;
                }*/
                prev_type = type;
                //text += type;
                text.push(type);

            });
            /*if (type == '+' || type == '-' || type == '*' || type == '/') {
                broken = true;
            }
            if (broken) {
                return '';
            }*/
            return text;
        }
        function calcMassiv() {
            var massiv = [];
            massiv.push(subcalcMassiv($('#formula')));
            $('#massiv').text(JSON.stringify(massiv));//JSON.stringify(['123'])
        }
        $( function() {

            $( ".sortable, .sortable .sortable-formula" ).sortable({
                connectWith: ".connectedSortable",
                revert: true,
                update:function(event, ui){
                    $('#formula').trigger('formulaChanged');
                }
            });

            $( ".draggable" ).draggable({
                connectToSortable: ".sortable, .sortable .sortable-formula",
                helper: "clone",
                revert: "invalid",
                stop: function( event, ui ) {

                    $('#formula').find('span').show();

                    $( ".sortable .sortable-formula" ).sortable({
                        connectWith: ".connectedSortable",
                        revert: true,
                        update:function(event, ui){
                            $('#formula').find('input[type="text"]').not('.js-watch').each(function(){
                                $(this).addClass('js-watch');
                                $(this).on('keyup',function(){
                                    $('#formula').trigger('formulaChanged');
                                });
                            });
                            $('#formula').trigger('formulaChanged');
                        }
                    });

                    $('#formula').find('input[type="text"]').not('.js-watch').each(function(){
                        $(this).addClass('js-watch');
                        $(this).on('keyup',function(){
                            $('#formula').trigger('formulaChanged');
                        });
                    });

                    //$( ".sortable, ..sortable sortable-formula" ).sortable("refresh");
                }
            });
            $( "ul, li" ).disableSelection();

            $( ".droppable" ).droppable({
                drop: function( event, ui ) {

                    console.log(ui.draggable)
                    /*
                  $( this )
                    .addClass( "ui-state-highlight" )
                    .find( "p" )
                      .html( "Dropped!" );
              */

                }
            });

            $('#formula').on('formulaChanged',function() {
                calc();
            });

            $('.js-formula-confirm').on('click', function () {
                if ($('#result').text()!=='Invalid formula'){

                    var massiv = [];
                    massiv.push(subcalcMassiv($('#formula')));

                    var formulaObject = {
                        'text':$('#result').text(),
                        'formula':massiv
                    };
                    //opener.window.trigger('priceFormulaUpdate',[ '{$formula_input|escape:'javascript'}', formulaObject ]);
                    if ( typeof parent.window.priceFormulaUpdate ==='function' ) {
                        parent.window.priceFormulaUpdate('{$formula_input|escape:'javascript'}', formulaObject);
                    }
/*
                        JSON.stringify(massiv)
                    $('#result').text();
                    $('#massiv').val();
*/
                }

            });
            //  open popup
            var $tokens = $('.js-tokens li');
            var $percent_el = $tokens.filter('.js-percent');
            var $bracket_el = $tokens.filter('.js-brackets');
            var $margin_el = $tokens.filter('.js-margin');
            var $value_el = $tokens.filter('.js-value');
            var restoreFormula = function(formula, targetEl){
                for( var i=0; i<formula.length; i++ ){
                    var token = formula[i];
                    var $lookup = $tokens.filter( function(i,el){
                        var check = $(el).find('input[type="hidden"]').filter('[value="'+token+'"]');
                        return check.length==1;
                    } );
                    if ( $lookup.length==1 ) {
                        var $element = $($lookup.get(0)).clone();
                        targetEl.append($element);
                    }else {
                        if ( $.isArray(token) ) {
                            if (token[0] == '()M') {
                                var $nested = $($margin_el.get(0)).clone();
                                targetEl.append($nested);
                                restoreFormula(token[1],$nested.find('.connectedSortable'));
                            } else {
                                var $nested = $($bracket_el.get(0)).clone();
                                targetEl.append($nested);
                                restoreFormula(token,$nested.find('.connectedSortable'));
                            }
                        }else{
                            token = ''+token;
                            if ( token.indexOf('%')!==-1 && token.indexOf('%')==(token.length-1) ) {
                                if ( $percent_el.length==1 ) {
                                    var $perT = $($percent_el.get(0)).clone();
                                    $perT.find('input[type="text"]').val(token.replace('%',''));
                                    targetEl.append($perT);
                                }
                            }else if( parseFloat(token)!='NaN' ) {
                                if ( $value_el.length==1 ) {
                                    var $valT = $($value_el).clone();
                                    $valT.find('input[type="text"]').val(token);
                                    targetEl.append($valT);
                                }
                            }
                        }
                    }
                }
            };
            var formulaObject = parent.window.priceFormulaRetrieve('{$formula_input|escape:'javascript'}');
            if ( typeof formulaObject==='object' && formulaObject.formula) {
                $('#formula').html('');
                if ( formulaObject.formula.length===1 ) {
                    restoreFormula(formulaObject.formula[0], $('#formula'));
                    $('#formula').find('input[type="text"]').not('.js-watch').each(function(){
                        $(this).addClass('js-watch');
                        $(this).on('keyup',function(){
                            $('#formula').trigger('formulaChanged');
                        });
                    });
                    $('#formula').trigger('formulaChanged');
                }
            }
        } );
    </script>
</head>
<body style="height: auto">
<div class="calculator">

    <ul class="source js-tokens">
        {foreach $allowParams as $code=>$label}
            <li class="draggable ui-state-highlight">{$label}<input type="hidden" value="{$code}"><span class="remove" onclick="return deleteMe(this);">&times;</span></li>
        {/foreach}
        <li class="draggable ui-state-highlight js-percent"><input type="hidden" value="%"><input class="form-control" type="text" value="0">%<span class="remove" onclick="return deleteMe(this);">&times;</span></li>
        <li class="draggable ui-state-highlight js-value"><input class="form-control" type="text" value="0"><span class="remove" onclick="return deleteMe(this);">&times;</span></li>
        <li class="draggable ui-state-highlight sortable-formula-box js-brackets">
            <input type="hidden" value="()">
            <span class="parenthesis-left">(</span>
            <ul class="sortable-formula connectedSortable ui-widget-header"></ul>
            <span class="parenthesis-right">)</span>
            <span class="remove" onclick="return deleteMe(this);">&times;</span>
        </li>

        <li class="draggable ui-state-highlight sortable-formula-box js-margin">
            <input type="hidden" value="()M">
            <span class="parenthesis-left">(</span>
            <ul class="sortable-formula connectedSortable ui-widget-header"></ul>
            <span class="parenthesis-right">) with MARGIN</span>
            <span class="remove" onclick="return deleteMe(this);">&times;</span>
        </li>

        <li class="draggable ui-state-highlight">+<input type="hidden" value="+"><span class="remove" onclick="return deleteMe(this);">&times;</span></li>
        <li class="draggable ui-state-highlight">-<input type="hidden" value="-"><span class="remove" onclick="return deleteMe(this);">&times;</span></li>
        <li class="draggable ui-state-highlight">*<input type="hidden" value="*"><span class="remove" onclick="return deleteMe(this);">&times;</span></li>
        <li class="draggable ui-state-highlight">/<input type="hidden" value="/"><span class="remove" onclick="return deleteMe(this);">&times;</span></li>
    </ul>


    <ul class="sortable connectedSortable ui-widget-header" id="formula">
    </ul>

    <br>
    <div id="result">

    </div>
    <div class="btn-bar">
        <div class="btn-left">
            {*<button type="button" class="btn btn-cancel-foot">Cancel</button>*}
        </div>
        <div class="btn-right">
            <button type="button" class="btn btn-primary js-formula-confirm">Confirm</button>
        </div>
    </div>
    {*<button onclick="return calc();">Calculate</button>*}
    {*<textarea cols="64" rows="2" id="result"></textarea>*}
    <textarea cols="64" rows="2" id="massiv" style="display: none"></textarea>

</div>
</body>
</html>
