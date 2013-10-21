<?php

function formulaires_partage_par_mail_verifier () {
    
    $erreurs = array();

    if (preg_match('/^[^@]+@[^@]+$/', _request('email_partage')) != 1) {
      $erreurs['message_erreur'] = _T('genres:email_invalide');
    }

    return $erreurs;
}

function formulaires_partage_par_mail_traiter () {

  include_spip('inc/mail');

  $sujet = _T('genres:sujet_mail_partage');
  $message = _T('genres:message_mail_partage') . _request('url_page');

  if (envoyer_mail(_request('email_partage'), $sujet, $message)) {
    return array(
                 'message_ok' => _T('genres:succes_envoi_email'),
                 );
  } else {
    return array(
                 'message_erreur' => _T('genres:erreur_envoi_email'),
                 );
  }
}

?>