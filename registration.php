<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once 'src/form.php';
require_once 'src/csv.php';
require_once 'src/mail.php';
require_once 'src/rates.php'; // includes $rates and $late

$options = array();

foreach ($rates as $name => $rate)
	$options[$name] = sprintf('%s (&euro;&nbsp;%d)', $rate['label'], $rate['price'][$price_category]);

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
$registration_form->add(new FormCheckboxField('dinner', sprintf('I want to join the conference dinner (&euro;&nbsp;%d each)', $dinner_rate[$price_category])));
// $registration_form->add(new FormNumberField('dinner_seats', 'Seats', array('value' => '1', 'min' => '1')));
// $registration_form->add(new FormTextArea('dinner_wishes', 'Dietary restrictions'));
$registration_form->add(new FormCheckboxField('siks', 'I am a PhD student in the <a href="http://www.siks.nl/" target="_blank">SIKS research school</a>'));
$registration_form->add(new FormCheckboxField('gdpr', 'I consent that the JURIX 2018 conference organisation records the above provided information for the purpose and duration of the conference organisation.', array('required' => true)));

$errors = $registration_form->submitted() ? $registration_form->validate() : array();

if ($registration_form->submitted() && count($errors) == 0) {
	// Combine all the data (and calculate the total amount due)
	$data = $registration_form->data();
	$data['total'] = $rates[$data['register_as']]['price'][$price_category];

	// Don't track GDPR consent (it is mandatory for submitting the form anyway)
	unset($data['gdpr']);

	// If you don't want to come to the dinner, then you don't need any seats! Eh!
	if (empty($data['dinner']))
		$data['dinner_seats'] = 0;
	
	// Add the dinner to that if people opted in
	$data['total'] += intval($data['dinner_seats']) * $dinner_rate[$price_category];

	// First, add the info to a CSV file here on the server
	$csv = new CSVFile('../data/signups-jurix.txt');
	$csv->add($data);

	// Add the price breakdown for the emails
	$data['price_breakdown'] = html_price_breakdown($data['register_as'], $data['dinner'] ? $data['dinner_seats'] : 0);

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
		rawurlencode($registration_form->dinner->value() ? $registration_form->dinner_seats->value() : 0));
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
				<li><a href="program.html">Program</a></li>
				<li><a href="workshops.html">Workshops</a></li>
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
					<p>Please complete the form below to register for the conference. Note that early registration rates apply until Monday December 3.</p> 
					
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
							<?php foreach ($rates as $rate_category): ?>
							<tr>
								<th><?=$rate_category['label']?></th>
								<td class="early">&euro;&nbsp;<?=$rate_category['price']['early']?></td>
								<td class="regular">&euro;&nbsp;<?=$rate_category['price']['late']?></td>
							</tr>
							<?php endforeach ?>
						</tbody>
					</table>

					<p>Lunches, coffee breaks and welcome reception (Wednesday) are included in the fees.</p>
					<p>Full conference registration fee includes all events: main conference, workshops, hackathon and doctoral consortium.</p>
					<p>Workshops, hackathon and doctoral consortium only fee includes Wednesday events only and does not give access to the main conference on Thursday and Friday.</p>
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
						<?= $registration_form->siks->render($errors) ?>
						</div>

						<?php /*
							<div class="form-grouping">
							<?= $registration_form->dinner->render($errors) ?>
							</div>
						
							<div class="form-grouping" data-toggle-with="#field-dinner">
							<?= $registration_form->dinner_seats->render($errors) ?>
							<p class="explanation">Number of seats to reserve for the dinner</p>
							<?= $registration_form->dinner_wishes->render($errors) ?>
							</div>
						*/ ?>

						<div class="form-grouping">
							<div class="form-group checkbox">
								<label class="option">
									<input type="checkbox" value="yes" name="dinner" id="field-dinner" disabled>
								 	<span style="text-decoration: line-through">I want to join the conference dinner (€&nbsp;60 each)</span>
								</label>
							</div>
							<p class="explanation">Unfortunately, there are no more dinner seats available</p>
						</div>

						<div class="form-grouping">
						<?= $registration_form->gdpr->render($errors) ?>
						</div>
	
						<div class="form-controls">
							<button type="submit">Submit registration</button>
						</div>
					</form>
				</section>

				<section>
					<h3>Payment</h3>
					<p>Please pay as soon as possible. Don't forget to mention both the project code 190 193 412 and JURIX 2018 when making your payment.</p>

					<p>Note: we will also send you this information in your confirmation email.</p>

					<dl class="payment-details">
						<dt>Addressee</dt>
						<dd>
							Rijksuniversiteit Groningen<br>
							Fac. Wiskunde en Natuurwetenschappen<br>
							Nijenborg 4<br>
							9747 AG  Groningen 
						</dd>

						<dt>Bank</dt>
						<dd>
							ABN-AMRO<br>
							Zuiderzeelaan 53<br>
							Postbus 686<br>
							8000 AR Zwolle
						</dd>
							
						<dt>IBAN</dt>
						<dd>NL45ABNA0474567206</dd>
				
						<dt>BIC</dt>
						<dd>ABN ANL 2A</dd>
						
						<dt>Description</dt>
						<dd>
							Project code 190 193 412<br>
							JURIX 2018
						</dd>
					</dl>

					<p>Please don't forget to mention both the project code <em>190 193 412</em> and <em>JURIX 2018</em> in your description.</p>
				</section>
			</div>
		</div>

		<footer>
			<div class="container">
				<p>Program chair: <a href="mailto:Monica Palmirani &lt;monica.palmirani@unibo.it&gt;">Monica Palmirani</a><br>
				Contact organisation: <a href="mailto:Sarah van Wouwe &lt;s.k.van.wouwe@rug.nl&gt;">Sarah van Wouwe</a></p>
			</div>
		</footer>
	
		<script src="assets/js/main.js"></script>
		<script>
			$('*[data-toggle-with]', function(container) {
				var toggle = document.querySelector(container.dataset.toggleWith);

				var update = function() {
					container.style.display = toggle.checked ? '' : 'none';
				};

				toggle.addEventListener('change', update);

				update();
			});
		</script>
	</body>
</html>
