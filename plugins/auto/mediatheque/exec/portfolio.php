<?php
/**
 * Plugin Portfolio/Gestion des documents
 * Licence GPL (c) 2006-2008 Cedric Morin, romy.tetue.net
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');

function exec_portfolio(){
	if (!autoriser('administrer','portfolio',0)) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}

	$commencer_page = charger_fonction('commencer_page','inc');
	echo $commencer_page(_T('gestdoc:documents'));
	
	echo gros_titre(_T('gestdoc:documents'),'',false);
	echo debut_grand_cadre(true);
	
	echo formulaire_recherche('portfolio');
	echo recuperer_fond('prive/galerie',$_GET);

	echo fin_grand_cadre(true),fin_page();
}

?>