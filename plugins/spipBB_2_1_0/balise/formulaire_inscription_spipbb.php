<?php
// Plugin Spipbb
// Balise importee de SPIP 192 et 193
// 15/1/2008 : adaptations chryjs pour compatibilite et autre

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2008                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;	#securite
include_spip('inc/spipbb_common'); // define + log
spipbb_log('included',2,__FILE__);

include_spip('base/abstract_sql');
include_spip('inc/filtres');

// Balise independante du contexte

// http://doc.spip.org/@balise_FORMULAIRE_INSCRIPTION
function balise_FORMULAIRE_INSCRIPTION_SPIPBB ($p) {

	return calculer_balise_dynamique($p, 'FORMULAIRE_INSCRIPTION_SPIPBB', array());
}

// args[0] un statut d'auteur (redacteur par defaut)
// args[1] indique la rubrique eventuelle de proposition
// args[2] indique le focus eventuel
// [(#FORMULAIRE_INSCRIPTION{nom_inscription, #ID_RUBRIQUE})]
// attention en 1.9.3 mode=6forum en 1.9.2 mode=forum !!!!

// http://doc.spip.org/@balise_FORMULAIRE_INSCRIPTION_stat
function balise_FORMULAIRE_INSCRIPTION_SPIPBB_stat($args, $filtres) {
	list($mode, $id, $focus) = $args;
	if (version_compare($GLOBALS['spip_version_code'],_SPIPBB_REV_REQSQL,'<')) {
		$mode='forum';
		if(!$mode) $mode = $GLOBALS['meta']['accepter_inscriptions'] == 'oui' ? 'redac' : '';
		if (!test_mode_inscription($mode))
			return '';
		else return array($mode, $focus, $id);
	} else {
		$mode='6forum';
		$mode = tester_config($id, $mode);
		return $mode ? array($mode, $focus, $id) : '';
	}
}

// Si inscriptions pas autorisees, retourner une chaine d'avertissement
// Sinon inclusion du squelette
// Si pas de mon ou pas de mail valide, premier appel rien d'autre a faire
// Autrement 2e appel, envoyer un mail et le squelette ne produira pas de
// formulaire.

// http://doc.spip.org/@balise_FORMULAIRE_INSCRIPTION_dyn
function balise_FORMULAIRE_INSCRIPTION_SPIPBB_dyn($mode, $focus, $id=0) {
	if (version_compare($GLOBALS['spip_version_code'],_SPIPBB_REV_REQSQL,'<')) {
		$mode='forum';
		if (!test_mode_inscription($mode)) return _T('pass_rien_a_faire_ici');
	} else {
		$mode='6forum';
		if (!tester_config($id, $mode)) return _T('pass_rien_a_faire_ici');
	}

	$nom = _request('nom_inscription');
	$mail = _request('mail_inscription');

	if ($mail) {
		$commentaire = message_inscription($mail, $nom, $mode, $id);
		if (is_array($commentaire)) {
			if (function_exists('envoyer_inscription'))
				$f = 'envoyer_inscription';
			else
				$f = 'envoyer_inscription_dist';
			$commentaire = $f($commentaire, $nom, $mode, $id);
		}
	} else $commentaire = ($mode=='1comite') ? _T('pass_espace_prive_bla') : _T('pass_forum_bla');

	$message = $commentaire ? '' : _T('form_forum_identifiant_mail');

	// #ENV*{message} doit etre non vide lorsque tout s'est bien passe
	// #ENV*{commentaire} doit etre non vide pour afficher le formulaire
	// et il indique si on s'inscrit a l'espace public ou prive
	// ou donne un message d'erreur aux appels suivants si pb

	return array("formulaires/inscription_spipbb", $GLOBALS['delais'],
			array('focus' => $focus,
				'message' => $message,
				'mode' => $mode,
				'commentaire' => $commentaire,
				'nom_inscription' => _request('nom_inscription'),
				'mail_inscription' => _request('mail_inscription'),
				'self' => self('&')
			)
		);
}


// fonction qu'on peut redefinir pour filtrer les adresses mail et les noms,
// et donner des infos supplementaires
// Std: controler que le nom (qui sert a calculer le login) est plausible
// et que l'adresse est valide (et on la normalise)
// Retour: une chaine message d'erreur
// ou un tableau avec au minimum email, nom, mode (redac / forum)

// http://doc.spip.org/@test_inscription_dist
function test_inscription_dist($mode, $mail, $nom, $id=0) {

	include_spip('inc/filtres');
	$nom = trim(corriger_caracteres($nom));
	if (!$nom || strlen($nom) > 64)
	    return _T('ecrire:info_login_trop_court');
	if (!$r = email_valide($mail)) return _T('info_email_invalide');
	return array('email' => $r, 'nom' => $nom, 'bio' => $mode);
}

// cree un nouvel utilisateur et renvoie un message d'impossibilite
// ou le tableau representant la ligne SQL le decrivant.
// $mode = 'forum' ou 'redac' selon ce a quoi on s'inscrit
// $id = une id_rubrique eventuelle (?)
// http://doc.spip.org/@message_inscription
function message_inscription($mail, $nom, $mode, $id=0) {
	if (function_exists('test_inscription'))
		$f = 'test_inscription';
	else
		$f = 'test_inscription_dist';
	$declaration = $f($mode, $mail, $nom, $id);

	if (is_string($declaration))
		return  $declaration;

	$row = sql_select("statut, id_auteur, login, email", "spip_auteurs", "email=" . sql_quote($declaration['email']));
	$row = sql_fetch($row);

	if (!$row)
		// il n'existe pas, creer les identifiants
		return inscription_nouveau($declaration);
	if (($row['statut'] == '5poubelle') AND !$declaration['pass'])
		// irrecuperable
		return _T('form_forum_access_refuse');

	if (($row['statut'] != 'nouveau') AND !$declaration['pass'])
		// deja inscrit
		return _T('form_forum_email_deja_enregistre');

	// existant mais encore muet, ou ressucite: renvoyer les infos
	$row['pass'] = creer_pass_pour_auteur($row['id_auteur']);
	return $row;
}

// On enregistre le demandeur comme 'nouveau', en memorisant le statut final
// provisoirement dans le champ Bio, afin de ne pas visualiser les inactifs
// A sa premiere connexion il obtiendra son statut final (auth->activer())

// http://doc.spip.org/@inscription_nouveau
function inscription_nouveau($declaration)
{
	if (!isset($declaration['login']))
		$declaration['login'] = test_login($declaration['nom'], $declaration['email']);

	$declaration['statut'] = 'nouveau';

	$n = sql_insertq('spip_auteurs', $declaration);
	// c: 2/8/5 BUG de la librairie de compat de CFG en 1.9.2
	if (version_compare($GLOBALS['spip_version_code'],_SPIPBB_REV_SQL,'<')) {
		$row=sql_fetsel('id_auteur','spip_auteurs',"login="._q($declaration['login']));
		$n=$row['id_auteur'];
	}

	$declaration['id_auteur'] = $n;

	$declaration['pass'] = creer_pass_pour_auteur($declaration['id_auteur']);
	return $declaration;
}

// envoyer identifiants par mail
// fonction redefinissable qui doit retourner false si tout est ok
// ou une chaine non vide expliquant pourquoi le mail n'a pas ete envoye

// http://doc.spip.org/@envoyer_inscription_dist
function envoyer_inscription_dist($ids, $nom, $mode, $id) {
	if (version_compare($GLOBALS['spip_version_code'],_SPIPBB_REV_REQSQL,'<')) {
		include_spip('inc/mail'); // pour nettoyer_titre_email
	}

	$envoyer_mail = charger_fonction('envoyer_mail','inc');
	$nom_site_spip = nettoyer_titre_email($GLOBALS['meta']["nom_site"]);
	$adresse_site = $GLOBALS['meta']["adresse_site"];

	$message = _T('form_forum_message_auto')."\n\n"
	  . _T('form_forum_bonjour', array('nom'=>$nom))."\n\n"
	  . _T((($mode == 'forum')  ?
		'form_forum_voici1' :
		'form_forum_voici2'),
	       array('nom_site_spip' => $nom_site_spip,
		     'adresse_site' => $adresse_site . '/',
		     'adresse_login' => $adresse_site .'/'. _DIR_RESTREINT_ABS))
	  . "\n\n- "._T('form_forum_login')." " . $ids['login']
	  . "\n- ".  _T('form_forum_pass'). " " . $ids['pass'] . "\n\n";


	if ($envoyer_mail($ids['email'],
			 "[$nom_site_spip] "._T('form_forum_identifiants'),
			 $message))
		return false;
	else
		return _T('form_forum_probleme_mail');
}

// http://doc.spip.org/@test_login
function test_login($nom, $mail) {
	include_spip('inc/charsets');
	$nom = strtolower(translitteration($nom));
	$login_base = preg_replace("/[^\w\d_]/", "_", $nom);

	// il faut eviter que le login soit vraiment trop court
	if (strlen($login_base) < 3) {
		$mail = strtolower(translitteration(preg_replace('/@.*/', '', $mail)));
		$login_base = preg_replace("/[^\w\d]/", "_", $nom);
	}
	if (strlen($login_base) < 3)
		$login_base = 'user';

	// eviter aussi qu'il soit trop long (essayer d'attraper le prenom)
	if (strlen($login_base) > 10) {
		$login_base = preg_replace("/^(.{4,}(_.{1,7})?)_.*/",
			'\1', $login_base);
		$login_base = substr($login_base, 0,13);
	}

	$login = $login_base;

	for ($i = 1; ; $i++) {
		if (!sql_countsel('spip_auteurs', "login='$login'"))
			return $login;
		$login = $login_base.$i;
	}
}

// http://doc.spip.org/@creer_pass_pour_auteur
function creer_pass_pour_auteur($id_auteur) {
	include_spip('inc/acces');
	$pass = creer_pass_aleatoire(8, $id_auteur);
	$mdpass = md5($pass);
	$htpass = generer_htpass($pass);
	sql_updateq('spip_auteurs', array('pass'=>$mdpass, 'htpass'=>$htpass),"id_auteur = ".intval($id_auteur));
	ecrire_acces();

	return $pass;
}

// SPIP 192
function test_mode_inscription($mode) {
	return (($mode == 'redac' AND $GLOBALS['meta']['accepter_inscriptions'] == 'oui')
		OR ($mode == 'forum'
		AND ($GLOBALS['meta']['accepter_visiteurs'] == 'oui'
			OR $GLOBALS['meta']['forums_publics'] == 'abo')));
}

?>
