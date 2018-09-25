<?php

exit; // Registrations are not open yet

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once 'src/form.php';
require_once 'src/csv.php';
require_once 'src/mail.php';
require_once 'src/rates.php'; // includes $rates and $late

$options = array();

foreach ($rates as $name => $rate)
	$options[$name] = sprintf('%s (&euro;&nbsp;%d)', $rate['label'], $rate['price']);

$registration_form = new Form();
$registration_form->add(new FormTextField('first_name', 'First name', array('required' => true)));
$registration_form->add(new FormTextField('last_name', 'Last name', array('required' => true)));
$registration_form->add(new FormEmailField('email', 'Email address', array('required' => true)));
$registration_form->add(new FormTextField('address1', 'Address', array('required' => true)));
$registration_form->add(new FormTextField('address2', ''));
$registration_form->add(new FormTextField('city', 'Town/City/Region', array('required' => true)));
$registration_form->add(new FormTextField('country', 'Country', array('required' => true)));
$registration_form->add(new FormRadioField('register_as', 'Registering as', $options, array('required' => true)));
$registration_form->add(new FormTextField('affiliation', 'Affiliation'));
$registration_form->add(new FormCheckboxField('dinner', sprintf('I want to join the conference dinner (&euro;&nbsp;%d; Wednesday November 8)', $dinner_rate)));
$registration_form->add(new FormCheckboxField('martinitoren', 'I want to join the trip to the Martinitoren (Wednesday November 8)'));

$errors = $registration_form->submitted() ? $registration_form->validate() : array();

if ($registration_form->submitted() && count($errors) == 0) {
	// Combine all the data (and calculate the total amount due)
	$data = $registration_form->data();
	$data['total'] = $rates[$data['register_as']]['price'] + (!empty($data['dinner']) ? $dinner_rate : 0);

	// First, add the info to a CSV file here on the server
	$csv = new CSVFile('../data/signups-jurix.txt');
	$csv->add($data);

	try {
		// Then, make sure Elina receives a mail about it
		$mail_elina = Email::fromTemplate('mails/elina.txt', $data);
		$mail_elina->send();
	} catch (Exception $e) {
		// Oh :(
	}

	try {
		// Also, let the person in question know that their registration has come through
		// (and tell them what to do next)
		$mail_registrant = Email::fromTemplate('mails/registrant.txt', $data);
		$mail_registrant->send();
	} catch (Exception $e) {
		// Well, sometimes live isn't fair
	}

	// Finally, show the payment instructions
	$link = sprintf('payment-details.php?rate=%s&dinner=%s',
		rawurlencode($registration_form->register_as->value()),
		rawurlencode($registration_form->dinner->value()));
	header('Location: ' . $link);
	echo 'Registration succeeded. Redirecting you to <a href="' . htmlentities($link) .'">the payment details page</a>.';
	exit;
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Register &middot; JURIX 2018</title>
		<meta name="description" content="The 31st international conference on Legal Knowledge and Information Systems">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="assets/css/layout.css">
	</head>
	<body>
		<header class="website-header small">
			<div class="text">
				<div class="container">
					<h1>JURIX 2018</h1>
					<p>The 31st international conference on Legal Knowledge and Information Systems</p>
					<p>December 12&ndash;14, 2018 in Groningen, The Netherlands</p>
				</div>
			</div>
		</header>

		<nav class="website-navigation fixed" tabindex="-1">
			<ul class="container">
				<li><a href="index.html#home">Home</a></li>
				<li>
					<a href="program.html">Program</a>
					<ul>
						<li><a href="program.html#schedule">Schedule</a></li>
						<li><a href="program.html#keynote-speakers">Keynote speakers</a></li>
						<li><a href="program.html#fact-talks">FACt talks</a></li>
						<li><a href="program.html#accepted-papers">Accepted papers and demonstrations</a></li>
						<li><a href="program.html#author-index">Author index</a></li>
						<li><a href="program.html#nvidia-deep-learning-workshop">NVIDIA Deep Learning Workshop</a></li>
					</ul>
				</li>
				<li class="active"><a href="registration.php">Registration</a></li>
				<li><a href="cfp.html">Calls</a></li>
				<li><a href="committees.html">Committees</a></li>
				<li><a href="practicalities.html">Practicalities</a></li>
			</ul>
		</nav>

		<div class="website-content">
			<div class="container">
				<section>
					<h1>Registration</h1>
					<p>Please complete the form below to register for the conference. Note that early registration rates apply until Friday October 27 (the day after the final paper submission deadline).</p> 
					
					<h3>Rates</h3>
					<table class="rates">
						<thead>
							<tr>
								<th>Registering as</th>
								<th>Early</th>
								<th>Regular</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th>Bachelor or Master <span class="hide-on-overflow">student</span></th>
								<td class="early">&euro;&nbsp;50</td>
								<td class="regular">&euro;&nbsp;50</td>
							</tr>
							<tr>
								<th>PhD student</th>
								<td class="early">&euro;&nbsp;110</td>
								<td class="regular">&euro;&nbsp;130</td>
							</tr>
							<tr>
								<th>Regular</th>
								<td class="early">&euro;&nbsp;160</td>
								<td class="regular">&euro;&nbsp;180</td>
							</tr>
						</tbody>
					</table>
				</section>
				<section>
					<form method="post" action="registration.php">
						<h3>Registration form</h3>
						<div class="form-grouping">
							<?= $registration_form->first_name->render($errors) ?>
							<?= $registration_form->last_name->render($errors) ?>
						</div>
						<div class="form-grouping">
							<?= $registration_form->email->render($errors) ?>
						</div>
						<div class="form-grouping">
							<?= $registration_form->address1->render($errors) ?>
							<?= $registration_form->address2->render($errors) ?>
							<?= $registration_form->city->render($errors) ?>
							<?= $registration_form->country->render($errors) ?>
						</div>

						<div class="form-grouping">
						<?= $registration_form->register_as->render($errors) ?>
						</div>
						<div class="form-grouping">
						<?= $registration_form->affiliation->render($errors) ?>
						</div>

						<div class="form-grouping">
						<?= $registration_form->dinner->render($errors) ?>
						<?= $registration_form->martinitoren->render($errors) ?>
						</div>
	
						<div class="form-controls">
							<button type="submit">Submit registration</button>
						</div>
					</form>
				</section>
			</div>
		</div>

		<footer>
			<div class="container">
				<p>Program chair: <a href="mailto:Monica Palmirani &lt;monica.palmirani@unibo.it&gt;">Monica Palmirani</a><br>
				Contact organisation: <a href="mailto:Sarah van Wouwe &lt;s.k.van.wouwe@rug.nl&gt;">Sarah van Wouwe</a></p>
			</div>
		</footer>
	</body>
</html>
