//Gestion des datepickers
$(function(){
	$("#annulation_date").datepicker({
		firstDay: 1,
		showOtherMonths: true,
		selectOtherMonths: true,
		dayNamesMin: ['D', 'L','Ma','Me','J','V','S'],
		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
		nextText: 'Mois suivant',
		prevText: 'Mois précédent',
		dateFormat:'dd/mm/yy',
		minDate:'0D',
		maxDate:new Date($("#annulation_date").data('maxdate') * 1000)
	});

});
