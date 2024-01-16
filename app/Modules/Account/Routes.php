<?php

namespace App\Modules\Account;

use CodeIgniter\Router\RouteCollection;


$routes->group('api/auth', ['namespace' => '\App\Modules\Account\Controllers'], function (RouteCollection $routes) {
    $routes->get('login',                   'Session::getLoginData');
    $routes->post('login',                  'Session::loginOrdinary');
    $routes->post('google',                 'Session::loginGoogle');
    $routes->post('verify',                 'Session::checkAccessToken');
});

$routes->group('api/profile', ['namespace' => '\App\Modules\Account\Controllers', 'filter' => 'auth'], function (RouteCollection $routes) {
    $routes->get('/',                       'User::show');
    $routes->patch('/',                     'User::edit');
});

$routes->group('api/user', ['namespace' => '\App\Modules\Account\Controllers', 'filter' => 'admin'], function (RouteCollection $routes) {
    $routes->get('/',                       'User::index');
    $routes->get('committe',                'User::committe');
    $routes->get('participant',             'User::participant');
    $routes->get('(:segment)',              'User::show/$1');
    $routes->post('/',                      'User::create');
    $routes->post('batch',                  'User::createBatch');
    $routes->patch('(:segment)',            'User::edit/$1');
    $routes->delete('(:segment)',           'User::delete/$1');
});
