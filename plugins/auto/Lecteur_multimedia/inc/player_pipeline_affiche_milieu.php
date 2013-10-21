<?php 

	// inc/player_pipeline_affiche_milieu.php
	
	// $LastChangedRevision: 31819 $
	// $LastChangedBy: paladin@quesaco.org $
	// $LastChangedDate: 2009-09-27 15:37:46 +0200 (dim, 27 sep 2009) $

	
if (!defined("_ECRIRE_INC_VERSION")) return;

// pipeline (plugin.xml)
// Ajoute la boite en fin de page de configuration Fonctions avancees
function player_affiche_milieu ($flux) {

	$exec = $flux['args']['exec'];

	if ($exec == 'config_fonctions'){	
		include_spip('inc/player_affiche_config_form');
		$flux['data'] .= player_affiche_config_form($exec);
	}

	return($flux);
}

?>