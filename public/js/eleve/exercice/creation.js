//Options avancées
$(function(){
	$('#div_options-avancees, #div_auto-annulation, #div_auto-accept').hide();
	
	$('#checkbox_options-avancees, #checkbox_auto-accept, #checkbox_auto-annulation').change(function()
	{
		$(this).closest('fieldset').find('div:first').toggle('slow');
	});
	
	//Remise dans l'état d'envoi en cas d'erreurs
	if($('#checkbox_auto-accept').is(':checked'))
	{
		$('#div_options-avancees, #div_auto-accept').show();
	}
	
	if($('#checkbox_auto-annulation').is(':checked'))
	{
		$('#div_options-avancees, #div_auto-annulation').show();
	}
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
			value: $('#auto_accept').val(),
			min: 0,
			max: Max,
			slide: function(event, ui)
			{
				$("#auto_accept").val(ui.value);
			}
	});
	$("#auto_accept").change(function()
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
	var dates = $("#rendu_date, #annulation_date").datepicker({
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
});
