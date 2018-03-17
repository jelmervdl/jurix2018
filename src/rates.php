<?php

$late = time() > mktime(0, 0, 0, 10, 28);

$rates = array(
	'student' => array(
		'label' => 'Bachelor or Master student',
		'price' => 50
	),
	'phd' => array(
		'label' => 'PhD student',
		'price' => $late ? 130 : 110
	),
	'regular' => array(
		'label' => 'Regular',
		'price' => $late ? 180 : 160
	)
);

$dinner_rate = 50;