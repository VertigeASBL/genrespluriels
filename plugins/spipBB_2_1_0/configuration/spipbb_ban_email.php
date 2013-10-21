<?php
#---------------------------------------------------------------#
#  Plugin  : spipbb - Licence : GPL                             #
#  File    : configuration/spipbb_ban_email                     #
#  Authors : chryjs 2007 et als                                 #
#  http://www.spip-contrib.net/Plugin-SpipBB#contributeurs      #
#  Contact : chryjs!@!free!.!fr                                 #
# [fr] Gestion du banissement                                   #
# [en] Manage ban lists                                         #
#---------------------------------------------------------------#

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

// * [fr] Acces restreint, plugin pour SPIP * //
// * [en] Restricted access, SPIP plugin * //

if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/spipbb_common');
spipbb_log('included',2,__FILE__);
include_spip('inc/presentation');

// ------------------------------------------------------------------------------
// ------------------------------------------------------------------------------
function configuration_spipbb_ban_email_dist() {
		// ban email bloc

	$res = debut_cadre_relief("", true, "", "<label for='ban_email'>"._T('spipbb:admin_ban_email_info')."</label>")
		. "<input type='text' name='ban_email' id='ban_email' size='5' class='forml'>"
		. fin_cadre_relief(true);

		// unban email bloc

	$res .= debut_cadre_relief("", true, "", "<label for='unban_email'>"._T('spipbb:admin_unban_email_info')."</label>")
		. "<select name='unban_email[]' id='unban_email' multiple='multiple' size='5' class='forml'>";

	$query=sql_select(array("id_ban","ban_email"),"spip_ban_liste","ban_email IS NOT NULL");
	if ( sql_count($query) ) {
		while ($row = sql_fetch($query)) {
			$res .= "<option value='".$row['id_ban']."'>".$row['ban_email']."</option>";
		}
	}
	else {
		$res .= "<option value='-1'>"._T('spipbb:admin_ban_email_none')."</option>";
	}

	$res .= "</select>"
		. fin_cadre_relief(true);

	if (version_compare($GLOBALS['spip_version_code'],_SPIPBB_REV_AJAXCONFIG,'>=')) {
		$res = ajax_action_post('spipbb_configurer', 'spipbb_ban_email', 'spipbb_admin_gere_ban','',$res) ;
		return ajax_action_greffe('spipbb_configurer-spipbb_ban_email','', $res); // creer action : "ban" a  la facon de action/configurer
	}
	else { // 1.9.2
		$res = ajax_action_post('spipbb_configurer', 'spipbb_ban_email', 'spipbb_admin_gere_ban','',$res,_T('bouton_valider'),'  class="fondo" ') ;
		return ajax_action_greffe('spipbb_configurer-spipbb_ban_email', $res); // creer action : "ban" a  la facon de action/configurer
	}
} // configuration_spipbb_ban_email_dist

?>
