<?php

/***************************************************************************\
sur base action/editer_article
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/spipbb_common');
spipbb_log('included',2,__FILE__);

//include_spip('inc/spipbb_util');


// http://doc.spip.org/@action_editer_article_dist
function action_spipbb_editer_article() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	// si id_article n'est pas un nombre, c'est une creation
	// mais on verifie qu'on a toutes les donn�es qu'il faut.
	if (!$id_article = intval($arg)) {
		$id_parent = _request('id_parent');
		$id_auteur = $GLOBALS['auteur_session']['id_auteur'];
		if (!($id_parent AND $id_auteur)) redirige_par_entete('./');
		$id_article = insert_article($id_parent);

		# cf. GROS HACK ecrire/inc/getdocument
		# rattrapper les documents associes a cet article nouveau
		# ils ont un id = 0-id_auteur

###h. modif
#		sql_query("UPDATE spip_documents_articles SET id_article = $id_article WHERE id_article = ".(0-$id_auteur));
	}

	// Enregistre l'envoi dans la BD
	$err = articles_set($id_article);

	$redirect = parametre_url(urldecode(_request('redirect')),
		'id_article', $id_article, '&') . $err;

	redirige_par_entete($redirect);
}

// Appelle toutes les fonctions de modification d'un article
// $err est de la forme '&trad_err=1'
// http://doc.spip.org/@articles_set
function articles_set($id_article, $c=false) {
	$err = '';

	// Edition du contenu ?
	$err .= revisions_articles($id_article, $c);

	// Modification de statut, changement de rubrique ?
	$err .= instituer_article($id_article, $c);

	// Un lien de trad a prendre en compte
	$err .= article_referent($id_article, $c);

	return $err;
}

// http://doc.spip.org/@insert_article
function insert_article($id_rubrique) {

	include_spip('base/abstract_sql');
	include_spip('inc/rubriques');

	// Si id_rubrique vaut 0 ou n'est pas definie, creer l'article
	// dans la premiere rubrique racine
	if (!$id_rubrique = intval($id_rubrique)) {
		$row = sql_fetsel("id_rubrique","spip_rubriques","id_parent=0","","0+titre,titre","1");
		$id_rubrique = $row['id_rubrique'];
	}

	// La langue a la creation : si les liens de traduction sont autorises
	// dans les rubriques, on essaie avec la langue de l'auteur,
	// ou a defaut celle de la rubrique
	// Sinon c'est la langue de la rubrique qui est choisie + heritee
	if ($GLOBALS['meta']['multi_articles'] == 'oui') {
		lang_select($GLOBALS['auteur_session']['lang']);
		if (in_array($GLOBALS['spip_lang'],
		explode(',', $GLOBALS['meta']['langues_multilingue']))) {
			$lang = $GLOBALS['spip_lang'];
			$choisie = 'oui';
		}
	}

	$row = sql_fetsel(array("lang","id_secteur"),"spip_rubriques","id_rubrique=$id_rubrique");

	$id_secteur = $row['id_secteur'];

	if (!$lang) {
		$lang = $GLOBALS['meta']['langue_site'];
		$choisie = 'non';
		$lang = $row['lang'];
	}

	$id_article = @sql_insertq("spip_articles",array(
			'id_rubrique' => $id_rubrique, 
			'id_secteur' => $id_secteur, 
			'statut' => 'prepa', 
			'date' =>  "NOW()", 
			'accepter_forum' => substr($GLOBALS['meta']['forums_publics'],0,3),
			'lang' => $lang, 
			'langue_choisie' => $choisie) );
	@sql_insertq('spip_auteurs_articles', array('id_auteur' => $GLOBALS['auteur_session']['id_auteur'], 'id_article'=>$id_article ) );

	return $id_article;
}

// Enregistre une revision d'article
// $c est un contenu (par defaut on prend le contenu via _request())
// http://doc.spip.org/@revisions_articles
function revisions_articles ($id_article, $c=false) {
	include_spip('inc/modifier');

	// unifier $texte en cas de texte trop long (sur methode POST seulement)
	if (!is_array($c)) trop_longs_articles();

	// Si l'article est publie, invalider les caches et demander sa reindexation
	$t = sql_fetsel("statut","spip_articles","id_article=$id_article");
	if ($t['statut'] == 'publie') {
		$invalideur = "id='id_article/$id_article'";
		$indexation = true;
	}

	$r = modifier_contenu('article', $id_article,
		array(
			'champs' => array(
				'surtitre', 'titre', 'soustitre', 'descriptif',
				'nom_site', 'url_site', 'chapo', 'texte', 'ps',
				'url_propre'
			),
			'nonvide' => array('titre' => _T('info_sans_titre')),
			'invalideur' => $invalideur,
			'indexation' => $indexation
		),
		$c);

	if ($r) {
		@sql_updateq("spip_articles",array('date_modif'=>"NOW()"),"id_article="._q($id_article));
	}

	return ''; // pas d'erreur
}


// $c est un array ('statut', 'id_rubrique')
//
// statut et rubrique sont lies, car un admin restreint peut deplacer
// un article publie vers une rubrique qu'il n'administre pas
// http://doc.spip.org/@instituer_article
function instituer_article($id_article, $c, $calcul_rub=true) {

	include_spip('inc/autoriser');
	include_spip('inc/rubriques');
	include_spip('inc/modifier');

	$row = sql_fetsel("statut,id_rubrique","spip_articles","id_article=$id_article");
	$id_rubrique = $row['id_rubrique'];
	$statut_ancien = $statut = $row['statut'];
	$champs = array();

	$s = _request('statut', $c);
	if ($s AND _request('statut', $c) != $statut) {
		if (autoriser('publierdans', 'rubrique', $id_rubrique))
			$statut = $champs['statut'] = $s;
		else if (autoriser('modifier', 'article', $id_article) AND $s != 'publie')
			$statut = $champs['statut'] = $s;
		else
			spipbb_log("$id_article refus " . join(' ', $c),3,"A_i_a");

		// En cas de publication, fixer la date a "maintenant"
		// sauf si $c commande autre chose
		if ($champs['statut'] == 'publie') {
			if ($d = _request('date', $c))
				$champs['date'] = $d;
			else
				$champs['date'] = date('Y-m-d H:i:s');
		}
	}

	// Verifier que la rubrique demandee existe et est differente
	// de la rubrique actuelle

	if ($id_parent = _request('id_parent', $c)
	AND $id_parent != $id_rubrique
	AND ( sql_fetsel("id_rubrique","spip_rubriques","id_rubrique=$id_parent") ) ) {
		$champs['id_rubrique'] = $id_parent;

		// si l'article etait publie
		// et que le demandeur n'est pas admin de la rubrique
		// repasser l'article en statut 'propose'.
		if ($statut == 'publie') {
			if ($GLOBALS['auteur_session']['statut'] != '0minirezo')
				$champs['statut'] = 'prop';
			else {
				include_spip('inc/auth');
				$r = auth_rubrique($GLOBALS['auteur_session']['id_auteur'], $GLOBALS['auteur_session']['statut']);
				if (is_array($r) AND !$r[$id_rubrique])
					$champs['statut'] = 'prop';
			}
		}
	}


	// Envoyer aux plugins
	$champs = pipeline('pre_edition',
		array(
			'args' => array(
				'table' => 'spip_articles',
				'id_objet' => $id_article
			),
			'data' => $champs
		)
	);

	if (!count($champs)) return;

	// Creer la requete SQL
	$update = array();
	foreach ($champs as $champ => $val) {
		// c: 10/2/8 compat pg_sql
		//$update[] = $champ . '=' . _q($val);
		$update[$champ] = _q($val);
	}

	@sql_updateq("spip_articles", $update, "id_article=$id_article");

	// Si on a deplace l'article
	// - propager les secteurs
	// - changer sa langue (si heritee)
	if (isset($champs['id_rubrique'])) {
		propager_les_secteurs();

		$row = sql_fetsel("lang,langue_choisie","spip_articles","id_article=$id_article");

		$langue_old = $row['lang'];
		$langue_choisie_old = $row['langue_choisie'];

		if ($langue_choisie_old != "oui") {
			$row = sql_fetsel("lang","spip_rubriques","id_rubrique="._q($champs['id_rubrique']));
			$langue_new = $row['lang'];
			if ($langue_new != $langue_old)
				@sql_updateq("spip_articles",array('lang'=>$langue_new),"id_article=$id_article");
		}
	}

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_article/$id_article'");

	// Recalculer les rubriques (statuts et dates) si l'on deplace
	// un article publie, ou si on le publie/depublie
	if (($statut == 'publie' AND isset($champs['id_rubrique']))
	OR (isset($champs['statut'])
		AND ($statut_ancien=='publie' OR $champs['statut']=='publie')
		AND $calcul_rub
		)
	)
		calculer_rubriques();

	// Pipeline
	pipeline('post_edition',
		array(
			'args' => array(
				'table' => 'spip_articles',
				'id_objet' => $id_article
			),
			'data' => $champs
		)
	);

	// Notifications
	if ($notifications = charger_fonction('notifications', 'inc')) {
		$notifications('instituerarticle', $id_article,
			array('statut' => $statut, 'statut_ancien' => $statut_ancien)
		);
	}

	return ''; // pas d'erreur
}


//
// Reunit les textes decoupes parce que trop longs
//

// http://doc.spip.org/@trop_longs_articles
function trop_longs_articles() {
	if (is_array($plus = _request('texte_plus'))) {
		foreach ($plus as $n=>$t) {
			$plus[$n] = preg_replace(",<!--SPIP-->[\n\r]*,","", $t);
		}
		set_request('texte', join('',$plus) . _request('texte'));
	}
}

// Poser un lien de traduction vers un article de reference
// http://doc.spip.org/@article_referent
function article_referent ($id_article, $c) {

	if (!$lier_trad = intval(_request('lier_trad', $c))) return;

	// selectionner l'article cible, qui doit etre different de nous-meme,
	// et quitter s'il n'existe pas

	if (!$row = sql_fetsel("id_trad","spip_articles","id_article=$lier_trad AND NOT(id_article=$id_article)"))
	{
		spipbb_log("echec lien de trad vers article inexistant ($lier_trad)",3,"A_a_r");
		return '&trad_err=1';
	}

	// $id_lier est le numero du groupe de traduction
	$id_lier = $row['id_trad'];

	// Si l'article vise n'est pas deja traduit, son identifiant devient
	// le nouvel id_trad de ce nouveau groupe et on l'affecte aux deux
	// articles
	if ($id_lier == 0) {
		@sql_updateq("spip_articles",array('id_trad'=> $lier_trad),"id_article IN ($lier_trad, $id_article)");
	}
	// sinon on ajouter notre article dans le groupe
	else {
		@sql_updateq("spip_articles",array('id_trad' => $id_lier),"id_article = $id_article");
	}

	return ''; // pas d'erreur
}


?>
