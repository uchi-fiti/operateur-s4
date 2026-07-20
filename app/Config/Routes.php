<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/operateur/login', 'OperateurController::login');
$routes->post('/operateur/login', 'OperateurController::authenticate');
$routes->get('/operateur/dashboard', 'OperateurController::dashboard');
$routes->get('/operateur/prefixes', 'OperateurController::prefixes');
$routes->post('/operateur/prefixes', 'OperateurController::addPrefix');
$routes->get('/operateur/comptes-clients', 'OperateurController::comptesClients');
$routes->get('/operateur/operations', 'OperationOperateurController::new');
$routes->post('/operateur/operations', 'OperationOperateurController::create');
