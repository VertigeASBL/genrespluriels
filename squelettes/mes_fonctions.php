<?php
define('_INTRODUCTION_SUITE', '...');

function calculer_date($date,$difference,$format=NULL){

  if (!$format) $format='Y-m-d H:i:s';
  $date=date($format, strtotime ($date."$difference"));

  return $date;
}

function balise_GLOBALS($p) {
  $nom = interprete_argument_balise (1, $p);
  $p->code = 'calculer_balise_GLOBALS(' . $nom . ')';
  return $p;
}


function calculer_balise_GLOBALS($nom) {
  return $GLOBALS[$nom];
}

/*
 * Reçoit un tableau serialisé #ENV et retourne le type de page. À utiliser
 * dans les squelettes ainsi : [(#ENV|trouver_type_page)]
 *
 * @param string $env : le contexte d'execution d'un squelette, tel que donné
 * par la balise #ENV
 *
 * @return string $type : le type de page, p.ex. 'article', 'rubrique' etc.
 * NULL si on n'arrive pas à trouver un type de page
 * cohérent.
 */
function trouver_type_page($env) {

  // tour de passe-passe SPIPien
  $env = unserialize(htmlspecialchars_decode($env));
 
  if ( ! is_array($env))
    trigger_error("Wrong type argument.", E_USER_ERROR);
 
  if (array_key_exists('page', $env))
    return $env['page'];
 
  if (array_key_exists('type', $env))
    return $env['type'];
 
  /* s'il n'y a pas de clé 'page', on est probablement dans le cas d'une
url raccourcie, du style spip.php?rubrique209. Dans ce cas, SPIP ajoute
à l'environnement une clé du style 'rubrique209' => ''. Il faut donc
bosser un peu…
  */
  foreach ($env as $cle => $valeur) {
    preg_match('/([a-z]+)([0-9]+)/', $cle, $matches);
    if (($matches) && ($env['id_'.$matches[1]] == $matches[2]))
      return $matches[1];
  }
 
  /* les urls propres ne donnent pas de format raccourci, ni de clé 'type ou
'page'. On essaie de deviner au mieux…
  */
  if (array_key_exists('id_auteur', $env))
    return 'auteur';
  if (array_key_exists('id_article', $env))
    return 'article';
  if (array_key_exists('id_rubrique', $env))
    return 'rubrique';
 
  return NULL;
}

function dump($variable) {
  var_dump($variable);
}

?>