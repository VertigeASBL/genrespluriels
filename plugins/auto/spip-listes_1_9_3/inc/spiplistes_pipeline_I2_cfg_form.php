<?php 

// inc/spiplistes_pipeline_insert_head.php (CP-20071019)

// $LastChangedRevision: 22286 $
// $LastChangedBy: cam.lafit@azerttyu.net $
// $LastChangedDate: 2008-08-28 17:39:21 +0200 (jeu, 28 aoû 2008) $

/*
	SPIP-Listes pipeline
	inc/spiplistes_pipeline_insert_head.php (CP-20071019)
	
	Nota: insert_head en cache. 
		Si modif ici, vider le cache
	
*/

if (!defined("_ECRIRE_INC_VERSION")) return;

function spiplistes_I2_cfg_form ($flux) {
    $flux .= recuperer_fond('fonds/inscription2_spip_listes');
	return ($flux);
}

?>
