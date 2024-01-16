<?php

namespace App\Modules\Exam;

use CodeIgniter\Router\RouteCollection;

$routes->group('api/exam', ['namespace' => '\App\Modules\Exam\Controllers', 'filter' => 'auth'], function (RouteCollection $routes) {

	// Subtests
	$routes->get('subtest',								'Subtest::index');				// melihat daftar subtest (butuh query exam) => /exam/subtest?exam=(exam_id)
	$routes->post('subtest',							'Subtest::create');				// membuat subtest
	$routes->get('subtest/(:segment)',					'Subtest::show/$1');			// melihat subtest detail
	$routes->patch('subtest/(:segment)',				'Subtest::edit/$1');			// mengubah
	$routes->delete('subtest/(:segment)',				'Subtest::delete/$1');			// menghapus

	// Attempt + Kepesertaan
	$routes->get('attempt',								'Attempt::index');				// melihat daftar peserta (butuh query exam) => /exam/attempt?exam=(exam_id)
	$routes->post('attempt',							'Attempt::create');				// (peserta) memilih tryout
	$routes->post('user-attempt',						'Attempt::start');				// (peserta) memilih tryout
	$routes->get('my-attempt',							'Attempt::available');			// (peserta) melihat daftar attempt
	$routes->get('attempt/(:segment)',					'Attempt::show/$1');			// melihat status pengerjaan
	$routes->patch('attempt/(:segment)',				'Attempt::edit/$1');			// mengubah status pengerjaan
	$routes->delete('attempt/(:segment)',				'Attempt::delete/$1');			// menghapus pengerjaan

	// Subattempts
	$routes->get('subattempt',							'Subattempt::index/$1');		// (admin) melihat daftar subattempt (butuh query subtest) => /exam/subattempt?subtest=(subtest_id)
	$routes->post('subattempt',							'Subattempt::start/$1');		// (peserta) memulai pengerjaan subtest, dapat answer_token
	$routes->get('subattempt/(:segment)',				'Subattempt::show/$1');			// melihat pengerjaan subtest (jawaban tiap soal) (butuh answer_token)
	$routes->get('subattempt/(:segment)/questions',		'Subattempt::material/$1');		// melihat soal (butuh answer_token)
	$routes->patch('subattempt/(:segment)',				'Subattempt::fill/$1');			// (peserta) mengisi jawaban (butuh answer_token)
	$routes->delete('subattempt/(:segment)',			'Subattempt::delete/$1');			// (admin) menghapus pengerjaan subtest
	$routes->post('subattempt/finish/(:segment)',		'Subattempt::finish/$1');		// (peserta) memulai pengerjaan subtest, dapat answer_token
	$routes->get('subattempt/(:segment)/review',		'Subattempt::review/$1');		// melihat soal (butuh answer_token)

	// Answers
	$routes->get('answer',								'Answer::get');
	$routes->put('answer',								'Answer::put');
	$routes->get('answer/review',						'Answer::review');


	// Materials
	$routes->get('question',							'Material::index');				// lihat daftar soal (butuh query subtest) => /exam/question?subtest=(subtest_id)
	$routes->post('question',							'Material::add');				// tambah soal dari bank soal ke subtest
	$routes->post('question/import',					'Material::import');			// tambah soal dari bank soal ke subtest
	$routes->get('question/(:segment)',					'Material::show/$1');			// lihat detail soal + kunci
	$routes->delete('question/(:segment)',				'Material::remove/$1');			// hapus soal dari subtest

	// Scoring
	$routes->get('ranker/irt-summary',					'Ranker::summaryIRT/$1');		//  dan menentukan nilai setiap nomor				| subtest_id
	$routes->post('ranker/preparing/(:segment)',		'Ranker::preparing/$1');		// mereview jumlah b/s/k dan 				| subtest_id
	$routes->post('ranker/weighting/(:segment)', 		'Ranker::weighting/$1');		// menentukan bobot nilai setiap nomor		| subtest_id
	$routes->post('ranker/distributing/(:segment)',		'Ranker::distributing/$1');	// menyimpan hasil nilai tiap nomor di tabel 'answers' tiap peserta			| subtest_id
	$routes->post('ranker/summarizing/(:segment)',		'Ranker::summarizing/$1');		// menyimpan hasil nilai tiap peserta di tabel 'subattempt' tiap peserta	| subtest_id
	$routes->post('ranker/agregating/(:segment)',		'Ranker::agregating/$1');		// menghitung dan menyimpan nilai tiap peserta di tabel 'attempt'			| tryout_id

	// Ranking
	$routes->get('ranker/subtest/(:segment)',			'Ranker::subtest/$1');			// melihat ranking berdasarkan score		| subtest_id
	$routes->get('ranker/agregate/(:segment)',			'Ranker::agregate/$1');			// melihat ranking berdasarkan score		| tryout_id
	$routes->get('ranker/agregate-full/(:segment)',		'Ranker::getFullRankScore/$1');
	$routes->get('certificate/(:segment)',				'Ranker::certificate/$1');		// (attempt_id)

	// Tryouts
	$routes->get('/',									'Event::index');					// melihat daftar tryout tersedia
	$routes->get('(:segment)',							'Event::show/$1');					// melihat rincian tryout info (tidak termasuk subtest)
	$routes->post('/',									'Event::create');				// membuat tryout baru
	$routes->patch('(:segment)',						'Event::edit/$1');				// mengubah tryout
	$routes->delete('(:segment)',						'Event::delete/$1');			// menghapus tryout

});
