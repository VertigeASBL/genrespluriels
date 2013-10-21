<?php 

	// inc/player_pipeline_ajouter_onglets.php
	
	// $LastChangedRevision: 23343 $
	// $LastChangedBy: kent1@arscenic.info $
	// $LastChangedDate: 2008-10-16 01:33:06 +0200 (jeu, 16 oct 2008) $

	/* CP-20080321
	Sera peut-etre necessaire si la page de config est trop longue ?
	*/
	
if (!defined("_ECRIRE_INC_VERSION")) return;

// pipeline (plugin.xml)
// Ajoute l'onglet de configuration en espace prive (exec configuration)
function player_ajouter_onglets ($flux) {

	include_spip('inc/urls');
	include_spip('inc/utils');

	global $connect_statut
		, $connect_toutes_rubriques
		;

	if(
		($flux['args'] == 'configuration')
		&& ($connect_statut == '0minirezo')
		&& $connect_toutes_rubriques
		) {
		$flux['data'][_PLAYER_PREFIX] = new Bouton( 
			_DIR_PLUGIN_PLAYER_IMAGES."player-24.gif"
			, _T(_PLAYER_LANG.'player_nom')
			, generer_url_ecrire("player_admin")
			)
			;
	}

	return ($flux);
}

?>