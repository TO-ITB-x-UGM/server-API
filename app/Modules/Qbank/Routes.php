<?php

namespace App\Modules\Qbank;

use CodeIgniter\Router\RouteCollection;

$routes->group('api/qbank', ['filter' => 'auth:qbank-management', 'namespace' => '\App\Modules\Qbank\Controllers'], function (RouteCollection $routes) {
	$routes->get('package',					'Package::index');
	$routes->get('package/(:segment)',		'Package::show/$1');
	$routes->post('package',				'Package::create');
	$routes->patch('package/(:segment)',	'Package::edit/$1');
	$routes->delete('package/(:segment)',	'Package::delete/$1');

	$routes->get('question',				'Question::index');
	$routes->get('question/(:segment)',		'Question::show/$1');
	$routes->post('question',				'Question::create');
	$routes->patch('question/(:segment)',	'Question::edit/$1');
	$routes->delete('question/(:segment)',	'Question::delete/$1');
});
