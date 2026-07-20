<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::choix');

$routes->group('client', function ($routes) {
    // Connexion en deux etapes : telephone puis code secret.
    $routes->get('login', 'ClientController::showLogin');
    $routes->post('login', 'ClientController::verifierTelephone');
    $routes->get('code-secret', 'ClientController::showCodeSecret');
    $routes->post('code-secret', 'ClientController::verifierCodeSecret');
    $routes->get('deconnexion', 'ClientController::deconnexion');

    // Espace connecte.
    $routes->get('solde', 'ClientController::solde');

    $routes->get('depot', 'ClientController::showDepot');
    $routes->post('depot', 'ClientController::depot');

    $routes->get('retrait', 'ClientController::showRetrait');
    $routes->post('retrait', 'ClientController::retrait');

    $routes->get('transfert', 'ClientController::showTransfert');
    $routes->post('transfert', 'ClientController::transfert');

    $routes->get('historique', 'ClientController::historique');

    // Appel AJAX : verification du destinataire pendant la saisie.
    $routes->get('verifier-destinataire', 'ClientController::verifierDestinataire');
});
