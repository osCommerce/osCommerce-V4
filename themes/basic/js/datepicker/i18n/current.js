/* English/UK initialisation for the jQuery UI date picker plugin. */
/* Written by Stuart. */
( function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "../widgets/datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}( function( datepicker ) {
if (typeof $tranlations == undefined ) $tranlations = {};
datepicker.regional[ "current" ] = {
	closeText: $tranlations.hasOwnProperty('DATEPICKER_DONE') ?  $tranlations.DATEPICKER_DONE : "Done",
	prevText: $tranlations.hasOwnProperty('DATEPICKER_PREV') ?  $tranlations.DATEPICKER_PREV : "Prev",
	nextText: $tranlations.hasOwnProperty('DATEPICKER_NEXT') ?  $tranlations.DATEPICKER_NEXT : "Next",
	currentText: $tranlations.hasOwnProperty('DATEPICKER_TODAY') ?  $tranlations.DATEPICKER_TODAY : "Today",
	monthNames: [ 
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_JANUARY') ?  $tranlations.DATEPICKER_MONTH_JANUARY : "January",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_FEBRUARY') ?  $tranlations.DATEPICKER_MONTH_FEBRUARY : "February",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_MARCH') ?  $tranlations.DATEPICKER_MONTH_MARCH : "March",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_APRIL') ?  $tranlations.DATEPICKER_MONTH_APRIL : "April",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_MAY') ?  $tranlations.DATEPICKER_MONTH_MAY : "May",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_JUNE') ?  $tranlations.DATEPICKER_MONTH_JUNE : "June",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_JULY') ?  $tranlations.DATEPICKER_MONTH_JULY : "July",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_AUGUST') ?  $tranlations.DATEPICKER_MONTH_AUGUST : "August",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_SEPTEMBER') ?  $tranlations.DATEPICKER_MONTH_SEPTEMBER : "September",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_OCTOBER') ?  $tranlations.DATEPICKER_MONTH_OCTOBER : "October",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_NOVEMBER') ?  $tranlations.DATEPICKER_MONTH_NOVEMBER : "November",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_DECEMBER') ?  $tranlations.DATEPICKER_MONTH_DECEMBER : "December" 
    ],
	monthNamesShort: [
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_JAN') ?  $tranlations.DATEPICKER_MONTH_JAN : "Jan",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_FEB') ?  $tranlations.DATEPICKER_MONTH_FEB : "Feb",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_MAR') ?  $tranlations.DATEPICKER_MONTH_MAR : "Mar",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_APR') ?  $tranlations.DATEPICKER_MONTH_APR : "Apr",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_MAY') ?  $tranlations.DATEPICKER_MONTH_MAY : "May",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_JUN') ?  $tranlations.DATEPICKER_MONTH_JUN : "Jun",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_JUL') ?  $tranlations.DATEPICKER_MONTH_JUL : "Jul",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_AUG') ?  $tranlations.DATEPICKER_MONTH_AUG : "Aug",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_SEP') ?  $tranlations.DATEPICKER_MONTH_SEP : "Sep",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_OCT') ?  $tranlations.DATEPICKER_MONTH_OCT : "Oct",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_NOV') ?  $tranlations.DATEPICKER_MONTH_NOV : "Nov",
    $tranlations.hasOwnProperty('DATEPICKER_MONTH_DEC') ?  $tranlations.DATEPICKER_MONTH_DEC : "Dec"
    ],
	dayNames: [ 
    $tranlations.hasOwnProperty('DATEPICKER_DAY_SUNDAY') ?  $tranlations.DATEPICKER_DAY_SUNDAY : "Sunday",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_MONDAY') ?  $tranlations.DATEPICKER_DAY_MONDAY : "Monday",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_TUESDAY') ?  $tranlations.DATEPICKER_DAY_TUESDAY : "Tuesday",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_WEDNESAY') ?  $tranlations.DATEPICKER_DAY_WEDNESAY : "Wednesday",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_THURSDAY') ?  $tranlations.DATEPICKER_DAY_THURSDAY : "Thursday",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_FRIDAY') ?  $tranlations.DATEPICKER_DAY_FRIDAY : "Friday",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_SATURDAY') ?  $tranlations.DATEPICKER_DAY_SATURDAY : "Saturday"
    ],
	dayNamesShort: [ 
    $tranlations.hasOwnProperty('DATEPICKER_DAY_SUN') ?  $tranlations.DATEPICKER_DAY_SUN : "Sun",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_MON') ?  $tranlations.DATEPICKER_DAY_MON : "Mon",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_TUE') ?  $tranlations.DATEPICKER_DAY_TUE : "Tue",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_WED') ?  $tranlations.DATEPICKER_DAY_WED : "Wed",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_THU') ?  $tranlations.DATEPICKER_DAY_THU : "Thu",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_FRI') ?  $tranlations.DATEPICKER_DAY_FRI : "Fri",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_SAT') ?  $tranlations.DATEPICKER_DAY_SAT : "Sat"
    ],
	dayNamesMin: [ 
    $tranlations.hasOwnProperty('DATEPICKER_DAY_SU') ?  $tranlations.DATEPICKER_DAY_SU : "Su",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_MO') ?  $tranlations.DATEPICKER_DAY_MO : "Mo",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_TU') ?  $tranlations.DATEPICKER_DAY_TU : "Tu",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_WE') ?  $tranlations.DATEPICKER_DAY_WE : "We",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_TH') ?  $tranlations.DATEPICKER_DAY_TH : "Th",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_FR') ?  $tranlations.DATEPICKER_DAY_FR : "Fr",
    $tranlations.hasOwnProperty('DATEPICKER_DAY_SA') ?  $tranlations.DATEPICKER_DAY_SA : "Sa"
    ],
	weekHeader: "Wk",
	dateFormat: "dd/mm/yy",
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: "" };
datepicker.setDefaults( datepicker.regional[ "current" ] );

return datepicker.regional[ "current" ];

} ) );
