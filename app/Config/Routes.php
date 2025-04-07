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
//Register User
$routes->match(['get','post'],'/register','Registration::index');
//$routes->get('/', 'Login::index');
