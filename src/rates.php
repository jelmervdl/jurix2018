<?php

$late = time() > mktime(0, 0, 0, 10, 28);

$price_category = $late ? 'late' : 'early';

$rates = array(
	'student' => array(
		'label' => 'Bachelor or Master student',
		'price' => array(
			'early' => 100,
			'late' => 150
		)
	),
	'academic' => array(
		'label' => 'Academic/Public administration',
		'price' => array(
			'early' => 250,
			'late' => 350
		)
	),
	'commercial' => array(
		'label' => 'Commercial',
		'price' => array(
			'early' => 350,
			'late' => 450
		)
	),
	'workshops-only-student' => array(
		'label' => 'Workshops and DC only (student)',
		'price' => array(
			'early' => 50,
			'late' => 60
		)
	),
	'workshops-only-other' => array(
		'label' => 'Workshops and DC only (others)',
		'price' => array(
			'early' => 100,
			'late' => 120
		)
	)
);

$dinner_rate = array(
	'early' => 60,
	'late' => 80
);