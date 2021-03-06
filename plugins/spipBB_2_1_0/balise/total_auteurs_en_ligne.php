<?php
#------------------------------------------------------------------------#
#  Plugin  : spipbb - Licence : GPL                                      #
#  File    : total_auteurs_en_ligne - balise #TOTAL_AUTEURS_EN_LIGNE     #
#  Authors : Chryjs, 2007 +                                              #
#  http://www.spip-contrib.net/Plugin-SpipBB#contributeurs               #
#  Contact : chryjs!@!free!.!fr                                          #
#------------------------------------------------------------------------#

//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

/* Cette balise permet d'afficher le nombre de membres en ligne */
/* pour le moment elle se base sur spip_auteurs mais... */
/* il faudra le changer pour quelque chose de plus r�aliste ! */
/* l'avantage est qu'il ne sera pas n�cessaire de changer les squelettes */
/* le delais peut etre modifie a condition de respecter la syntaxe SQL pour la fonction DATE_SUB */
/* en cas d'omission on garde 5 minutes comme d�lais */

if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/spipbb_common');
spipbb_log('included',2,__FILE__);

function balise_TOTAL_AUTEURS_EN_LIGNE($p) {
	return calculer_balise_dynamique($p,'TOTAL_AUTEURS_EN_LIGNE', array());
}

function balise_TOTAL_AUTEURS_EN_LIGNE_stat($args, $filtres) {
	return $args;
}

function balise_TOTAL_AUTEURS_EN_LIGNE_dyn($delais='30 MINUTE') {
	if (empty($delais)) $delais='30 MINUTE';

	$o = sql_fetsel('COUNT(*) AS total','spip_auteurs',"en_ligne>= DATE_SUB(NOW(), INTERVAL ".$delais." )");
	return is_array($o) ? $o['total'] : 0 ;
}

?>