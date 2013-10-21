<?php
/**
 * Retourne le bloc agenda des envois
 * @package spiplistes
 */
 // $LastChangedRevision: 47229 $
 // $LastChangedBy: paladin@quesaco.org $
 // $LastChangedDate: 2011-04-30 10:51:28 +0200 (Sat, 30 Apr 2011) $
 
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/texte');
include_spip('inc/spiplistes_api');
include_spip('inc/spiplistes_agenda');

/**
 * Retourne le bloc agenda des envois
 * @version CP-20080622
 */
function action_spiplistes_agenda_dist () {

	include_spip('inc/autoriser');
	
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	
	if(autoriser('moderer', 'liste')) {
		$periode = ($arg == _SPIPLISTES_AGENDA_PERIODE_HEBDO) ? $arg : _SPIPLISTES_AGENDA_PERIODE_MOIS;
		$redirect = rawurldecode(_request('redirect'));
		echo(spiplistes_boite_agenda_contenu($arg, $redirect, "/"._DIR_IMG_PACK));
	} 
	exit(0);

} //
