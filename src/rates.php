<?php

$late = time() > mktime(0, 0, 0, 12, 2);

$price_category = $late ? 'late' : 'early';

$rates = array(
	'student' => array(
		'label' => 'Bachelor, Master or PhD student',
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
		'label' => 'Workshops, hackathon and doctoral consortium only (student)',
		'price' => array(
			'early' => 50,
			'late' => 60
		)
	),
	'workshops-only-other' => array(
		'label' => 'Workshops, hackathon and doctoral consortium only (others)',
		'price' => array(
			'early' => 100,
			'late' => 120
		)
	)
);

$dinner_rate = array(
	'early' => 60,
	'late' => 60
);

function html_price_breakdown($rate, $dinner)
{
	global $rates, $dinner_rate, $price_category;
	
	return sprintf('&euro;&nbsp;%d %s rate%s',
		$rates[$rate]['price'][$price_category],
		$rates[$rate]['label'],
		$dinner > 0
			? sprintf(', %d &times; &euro;&nbsp;%d dinner', $dinner, $dinner_rate[$price_category])
			: '');
}