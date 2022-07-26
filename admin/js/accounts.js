(function ($) {
    $(function () {
        $('#table-accounts').dataTable( {
            "scrollX": false,
            "oLanguage": {
                'sSearch': '',
                'sInfo': 'Showing <b>_START_</b> to <b>_END_</b> of <b>_TOTAL_</b> entries',
                'sLengthMenu': 'Records per page&nbsp;&nbsp;_MENU_'
            },            
            'iDisplayLength': 5,
            'lengthMenu': [ 5, 10, 20, 50 ],
            "processing": true,
            "serverSide": true,
            "ajax": accounts_list_url,
            "columns": [
                {"class": "prop_atd"},
                {"class": "val_atd"},
                {"class": "mor_atd"},
                {"class": "ltv_atd"},
                {"class": "int_atd"},
                {"class": "mon_atd"},
                {"class": "net_atd"},
            ]
        });
        $('#table-accounts').wrap('<div class="table-accounts-over"></div>');
        $('#table-accounts_wrapper .dataTables_filter label input').after('<button type="submit"></button>');      
        $(window).on('resize', function () {
            if($('body').width() > 1260){
                setTimeout(function(){
                var ac_width1 = $('.val_atd').width();
                var ac_width2 = $('.mor_atd').width();
                var ac_width3 = $('.ltv_atd').width();
                var ac_width4 = $('.int_atd').width();
                var ac_width5 = $('.mon_atd').width();
                var ac_width6 = $('.net_atd').width();
                var ac_width0 = ac_width1 + ac_width2 + ac_width3 + ac_width4 + ac_width5 + ac_width6 + 235;
                var aw = 33;
                $('.val_atd_div').css('width', ac_width1 + aw);
                $('.mor_atd_div').css('width', ac_width2 + aw);
                $('.ltv_atd_div').css('width', ac_width3 + aw);
                $('.int_atd_div').css('width', ac_width4 + aw);
                $('.mon_atd_div').css('width', ac_width5 + aw);
                $('.net_atd_div').css('width', ac_width6 + aw);
                $('.t_summary').css('width', ac_width0);
            }, 1000);
            }
            if($('body').width() < 1020){
                setTimeout(function(){$('.t_summary').inrow({item1:'b'});}, 100);
            }                            
        })
        $(window).trigger('resize')
    })
})(jQuery)