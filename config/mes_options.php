<?php
define ('_ID_WEBMESTRES', '1514');

$GLOBALS['id_mentions_legales_fr'] = 574;
$GLOBALS['id_mentions_legales_nl'] = 575;
$GLOBALS['id_mentions_legales_en'] = 576;

function autoriser_travaux($faire, $quoi, $id, $qui, $opts) {
  if ($qui['statut']=='0minirezo' OR $qui['statut']=='1comite')
    return true;
  return false;
}
?>