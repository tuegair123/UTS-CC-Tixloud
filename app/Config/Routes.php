<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Konser::index'); // Jadikan halaman utama
$routes->get('/konser', 'Konser::index');
$routes->post('/konser/upload', 'Konser::upload');
$routes->get('/konser/beli/(:num)', 'Konser::beli/$1');
