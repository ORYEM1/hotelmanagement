<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 */

//Reservations

$routes->match(['get', 'post'], '/','Reservation::index');
$routes->match(['get', 'post'], '/reservation','Reservation::index');


//login
$routes->match(['get','post'],'/login','Login::index');
//$routes->get('/', 'Login::index');
