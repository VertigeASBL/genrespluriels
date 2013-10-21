<?php

// =======================================================================================================================================
// Nombre de visiteurs sur le site
// base sur le plugin Nombre de visiteurs connectes
// Fonction : affiche le nombre de visiteurs en cours de connection sur le site
// Parametre: aucun
// =======================================================================================================================================
//

// balise #VISITEURS_CONNECTES
function balise_VISITEURS_CONNECTES($p) {

	$p->code = 'calcul_visiteurs_connectes()';
	$p->statut = 'php';
	return $p;
}

function calcul_visiteurs_connectes() {
	$nb = count(preg_files(_DIR_TMP.'visites/','.'));
	return $nb;
}


// =======================================================================================================================================
// Nombre de visiteurs sur le site et historique
// Auteur (fr): physiquark@free.fr
// version 0.2
// doc sur http://www.spip-contrib.net/Plugin-pour-des-balises-de
// =======================================================================================================================================
//

// balise #TOTAL_VISITES
function vst_total_visites() {
	$query = "SELECT SUM(visites) AS total_abs FROM spip_visites";
	$result = spip_query($query);
	if ($row = spip_fetch_array($result))
		{ return $row['total_abs']; }
	else { return "0";}
}
function balise_TOTAL_VISITES($p) {
	$p->code = "vst_total_visites()";
	$p->statut = 'php';
	return $p;
}
// balise #NBPAGES_VISITEES
function vst_total_pages_visitees() {
	$query = "SELECT SUM(visites) AS nbPages FROM spip_visites_articles";
	$result = spip_query($query);
	if ($row = spip_fetch_array($result))
		{ return $row['nbPages']; }
	else { return "0";}
}
function balise_NBPAGES_VISITEES($p) {
	$p->code = "vst_total_pages_visitees()";
	$p->statut = 'php';
	return $p;
}


function vst_ip_live() {
	if(isset($_SERVER["REMOTE_ADDR"])) {
	$iplive = $_SERVER["REMOTE_ADDR"];
	}
	else { // sinon
	$iplive = "IP cachée";
	}
	return $iplive;
}
function balise_IP_LIVE($p) {
	$p->code = "vst_ip_live()";
	$p->statut = 'php';
	return $p;
}

?>