function numeric()
{
    this.value = this.value.replace(/[^0-9\.]/g,'');
}

//Slide du pressé
$(function(){
	$('#div_auto-accept').hide();
	$('#avance').change(function()
	{
		$('#div_auto-accept').toggle('slow');
	});
});

//Slider d'auto-accept :
var Max;
$(function() {
	Max = parseInt($("#slider_auto-accept span:first").text());
	
	$("#slider_auto-accept")
		.text('')
		.width('300px')
		.slider({
			range: "min",
			animate: true,
			value: 0,
			min: 0,
			max: Max,
			slide: function(event, ui)
			{
				$("#auto_accept").val(ui.value);
			}
	});
	$("#auto_accept")
		.val(0)
		.keyup(numeric)
		.change(function()
		{
			Val = parseInt($("#auto_accept").val());
		
			if(Val > Max)
			{
				$("#auto_accept").val(Max);
			}
			
			$("#slider_auto-accept").slider('value', Val);
		});
});

//Gestion des datepickers
$(function(){
	var dates = $( "#rendu_date, #annulation_date" ).datepicker({
		firstDay: 1,
		showOtherMonths: true,
		selectOtherMonths: true,
		dayNamesMin: ['D', 'L','Ma','Me','J','V','S'],
		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
		nextText: 'Mois suivant',
		prevText: 'Mois précédent',
		dateFormat:'dd/mm/yy',
		minDate:'0D',
		maxDate:'+4M',
		onSelect: function( selectedDate ) {
			var option = this.id == "annulation_date" ? "minDate" : "maxDate";
			var instance = $( this ).data( "datepicker" );
			date = $.datepicker.parseDate(
				instance.settings.dateFormat ||
				$.datepicker._defaults.dateFormat,
				selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});
	
	$('#rendu_heure').keyup(numeric);
	$('#annulation_heure').keyup(numeric);
});
