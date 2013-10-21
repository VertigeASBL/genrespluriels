jQuery(document).ready(function($) {
	
	/* Script qui simule un place Holder pour toute les value des formulaires */
	$("input[type=text], input[type=password]").live("focusin", function () {
		value = $(this).val();
	if (value == this.defaultValue) $(this).val("");
	});
	$("input[type=text], input[type=password]").live("focusout",function () {
		if ($(this).val() == "") $(this).val(this.defaultValue);
	});


	/* Script qui agrandit le texte quand on clique sur les liens. */
	$("#fontplus").live("click", function () {
		$("html, body").css("font-size", "110%");
	});

	$("#fontmoin").live("click", function () {
		$("html, body").css("font-size", "100%");
	});

});