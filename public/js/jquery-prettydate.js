/*
 * jQuery pretty date plug-in 1.0.0
 * 
 * http://bassistance.de/jquery-plugins/jquery-plugin-prettydate/
 * 
 * Based on John Resig's prettyDate http://ejohn.org/blog/javascript-pretty-date
 *
 * Copyright (c) 2009 Jörn Zaefferer
 *
 * $Id: jquery.validate.js 6096 2009-01-12 14:12:04Z joern.zaefferer $
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

(function() {

$.prettyDate = {
	now: function() {
		return new Date();
	},
	
	// Takes an ISO time and returns a string representing how
	// long ago the date represents.
	format: function(time) {
		var date = new Date(time);
		diff = Math.floor((date.getTime() - $.prettyDate.now().getTime()) / 1000);
		
		a_minute = 60;
		a_hour = 60*60;
		a_day = 60*60*24;
		
		day_diff = Math.floor(diff / a_day);
		hour_diff = Math.floor((diff - day_diff * a_day) / a_hour);
		min_diff = Math.floor((diff - day_diff * a_day - hour_diff * a_hour) / a_minute);
		sec_diff = (diff - day_diff * a_day - hour_diff * a_hour - min_diff * a_minute);


		if (isNaN(day_diff) || day_diff >= 30)
			return;
		
		if(day_diff < 0)
		{
			return 'échéance dépassée.';
		}
		
		return (day_diff==0?'':(day_diff==1?' un jour':' ' + day_diff + ' jours'))
			+ (hour_diff==0?'':(hour_diff==1?' une heure':' ' + hour_diff + ' heures'))
			+ (min_diff==0?'':(min_diff==1?' une minute':' ' + min_diff+ ' minutes'))
			+ (sec_diff==0?'':(sec_diff==1?' une seconde':' ' + sec_diff + ' secondes'));
	}
	
};
	
$.fn.prettyDate = function(options) {
	options = $.extend({
		value: function() {
			return $(this).attr("datetime");
		},
		interval: 1000
	}, options);
	var elements = this;
	function format() {
		elements.each(function() {
			var date = $.prettyDate.format(options.value.apply(this));
			if ( date && $(this).text() != date )
				$(this).text( date );
		});
	}
	format();
	if (options.interval)
		setInterval(format, options.interval);
	return this;
};

})();