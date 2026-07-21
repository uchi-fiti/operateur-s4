<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::choix');

// alea  promotion sur le frais de transfert en poucentage
// - meme operateur
// - configration anaty base
// - mihena x % ny transfert rehetra
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

    // Envoi multiple : un montant reparti entre plusieurs numeros de notre operateur.
    $routes->get('envoi-multiple', 'ClientController::showEnvoiMultiple');
    $routes->post('envoi-multiple', 'ClientController::envoiMultiple');

    $routes->get('historique', 'ClientController::historique');

    // Appel AJAX : verification du destinataire pendant la saisie.
    $routes->get('verifier-destinataire', 'ClientController::verifierDestinataire');

    // Appel AJAX : calcul des frais de transfert.
    $routes->get('calculer-frais-transfert', 'ClientController::calculerFraisTransfert');

    // Appel AJAX : calcul des frais de retrait.
    $routes->get('calculer-frais-retrait', 'ClientController::calculerFraisRetrait');

});

$routes->get('/operateur/login', 'OperateurController::login');
$routes->post('/operateur/login', 'OperateurController::authenticate');
$routes->get('/operateur/dashboard', 'OperateurController::dashboard');
$routes->get('/operateur/prefixes', 'OperateurController::prefixes');
$routes->post('/operateur/prefixes', 'OperateurController::addPrefix');
$routes->get('/operateur/comptes-clients', 'OperateurController::comptesClients');

// Prefixes des autres operateurs et commissions associees.
$routes->get('/operateur/prefixes-autres', 'OperateurController::prefixesAutres');
$routes->get('/operateur/prefixes-autres/ajouter', 'OperateurController::showAjoutPrefixeAutre');
$routes->post('/operateur/prefixes-autres/ajouter', 'OperateurController::addPrefixeAutre');
$routes->post('/operateur/prefixes-autres/modifier/(:num)', 'OperateurController::updateCommission/$1');

// Situation des montants a reverser aux autres operateurs.
$routes->get('/operateur/montants-a-envoyer', 'OperateurController::montantsAEnvoyer');
$routes->get('/operateur/operations', 'OperationOperateurController::new');
$routes->post('/operateur/operations', 'OperationOperateurController::create');
$routes->get('/operateur/operations/(:num)/edit', 'OperationOperateurController::edit/$1');
$routes->post('/operateur/operations/update/(:num)', 'OperationOperateurController::update/$1');
$routes->get('/operateur/promotions', 'OperateurController::showPromotion');

// $routes->delete('/operateur/operations/(:num)', 'OperationOperateurController::delete');

