/**
 * Sélectionner automatiquement la bonne action en fonction de l'endroit cliqué
 */
$(function(){
	$('#rib-banque, #rib-quichet, #rib-compte, #rib-cle').click(function(){$('#rib').click();});
	$('#paypal-mail').click(function(){$('#paypal').click();});
});