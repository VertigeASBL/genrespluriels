/**
 * spiplistes_abonnement.js
 * Appele' pour le squelette abonnement
 * 
 * $LastChangedRevision: 30780 $
 * $LastChangedBy: paladin@quesaco.org $
 * $LastChangedDate: 2009-08-10 10:09:37 +0200 (Mon, 10 Aug 2009) $
 */
jQuery(document).ready(function(){
	jQuery.fn.extend({
		cacher_desc: function(){
			jQuery('span.listeDescriptif').hide();
		},
		swaper_desc: function(){
			$('ul.liste-des-listes li label').hover(
				function () {
					jQuery('span.listeDescriptif').hide();
					jQuery('#listeDescriptif' + jQuery(this).children('input').val()).fadeIn();
				}, 
				function () {
					jQuery('#listeDescriptif' + jQuery(this).children('input').val()).hide();
				}
			);
		}
	});
	jQuery(document).cacher_desc();
	jQuery(document).swaper_desc();
});

// reactiver les events si appel ajax
jQuery(document).ajaxComplete(function(event,request, settings){
	jQuery(document).cacher_desc();
	jQuery(document).swaper_desc();
 });
