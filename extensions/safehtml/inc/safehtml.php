<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2009                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


if (!defined("_ECRIRE_INC_VERSION")) return;

// Controle la presence de la lib safehtml et cree la fonction
// de transformation du texte qui l'exploite
// http://doc.spip.org/@inc_safehtml_dist
function inc_safehtml_dist($t) {
	static $process, $test;

	if (!$test) {
		$process = false;
		if ($f = find_in_path('lib/safehtml/classes')) {
			define('XML_HTMLSAX3', $f.'/');
			require_once XML_HTMLSAX3.'safehtml.php';
			$process = new safehtml();
			$process->deleteTags[] = 'param'; // sinon bug Firefox
		}
		if ($process)
			$test = 1; # ok
		else
			$test = -1; # se rabattre sur une fonction de securite basique
	}

	if ($test > 0) {
		# autoriser des trucs
		# ex: l'embed de youtube
		if (
		false !== strpos($t, 'iframe')) {
			foreach (extraire_balises($t, 'iframe') as $iframe) {
				if (preg_match(',^http://(www\.)?(youtube\.com|(player\.)?vimeo\.com)/.*,', extraire_attribut($iframe, 'src'))) {
					$re = '___IFRAME___'.md5($iframe);
					$ok[$re] = $iframe;
					$t = str_replace($iframe, $re, $t);
				}
			}
		}

		# reset ($process->clear() ne vide que _xhtml...),
		# on doit pouvoir programmer ca plus propremement
		$process->_counter = array();
		$process->_stack = array();
		$process->_dcCounter = array();
		$process->_dcStack = array();
		$process->_listScope = 0;
		$process->_liStack = array();
#		$process->parse(''); # cas particulier ?
		$process->clear();
		$t = $process->parse($t);

		# reinserer les trucs autorises
		if ($ok)
		foreach ($ok as $re => $v)
			$t = str_replace($re, $v, $t);
	}
	else
		$t = entites_html($t); // tres laid, en cas d'erreur

	return $t;
}

?>
