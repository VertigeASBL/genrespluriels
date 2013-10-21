<?php


	// player_mes_options.php

	// $LastChangedRevision: 31820 $
	// $LastChangedBy: paladin@quesaco.org $
	// $LastChangedDate: 2009-09-27 16:09:22 +0200 (dim, 27 sep 2009) $


	define("_PLAYER_PREFIX", "player");	
	define("_DIR_PLUGIN_PLAYER_IMAGES", _DIR_PLUGIN_PLAYER."images/");
	define("_DIR_PLUGIN_PLAYER_JAVASCRIPT", _DIR_PLUGIN_PLAYER."javascript/");
	define("_PLAYER_LANG", _PLAYER_PREFIX.":");
	
	define("_PLAYER_META_PREFERENCES", "player_preferences");

	define("_PLAYER_MP3_LECTEUR_DEFAULT", "eraplayer_playlist.swf"); // le plus leger
	
	define("_PLAYER_FLV_LECTEUR_DEFAULT", "player_flv_maxi.swf");
	
	define("_PLAYER_FLV_LECTEURS", 
		serialize(
			array(
				'mini' => array(
					'label' => _T(_PLAYER_LANG."mini")
					, 'value' => "player_flv_mini.swf"
				)
				, 'normal' => array(
					'label' => _T(_PLAYER_LANG."normal")
					, 'value' => "player_flv.swf"
				)
				, 'maxi' => array(
					'label' => _T(_PLAYER_LANG."maxi")
					, 'value' => _PLAYER_FLV_LECTEUR_DEFAULT
				)
				, 'multi' => array(
					'label' => _T(_PLAYER_LANG."multi")
					, 'value' => "player_flv_multi.swf"
				)
			)
		)
	);
?>