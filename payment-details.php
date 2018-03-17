<?php

require_once 'src/rates.php';

if (!isset($_GET['rate']) || !array_key_exists($_GET['rate'], $rates)) {
	header('Location: index.html');
	exit;
}

$dinner = !empty($_GET['dinner']);

$rate = $rates[$_GET['rate']];

$total = $rate['price'] + ($dinner ? $dinner_rate : 0);

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Thank you &middot; JURIX 2018</title>
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
				<li><a href="cfp.html">Call for papers</a></li>
				<li><a href="committees.html">Committees</a></li>
				<li><a href="practicalities.html">Practicalities</a></li>
			</ul>
		</nav>

		<div class="website-content">
			<div class="container">
				<section>
					<h2>Thank you!</h2>
					<p>Thank you for your registration. We have sent you an email as a confirmation.</p>
				
					<h3>Payment</h3>
					<p>Please pay as soon as possible. Don't forget to mention both the project code 190 193 412 and JURIX 2018 when making your payment.</p>

					<p>Note: we have also sent you this information in your confirmation email.</p>

					<p>
						You have registered as a <?= htmlentities($rate['label']) ?>.
						<?php if ($dinner): ?>You've also opted to join the dinner.<?php endif ?>
						Would you be so kind to transfer € <?=$total?><?php if ($dinner): ?> (&euro;&nbsp;<?=$rate['price']?> <?=$rate['label']?> rate, &euro;&nbsp;<?=$dinner_rate?> dinner)<?php endif ?> to the following account as soon as possible?
					</p>

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

						<dt>Amount</dt>
						<dd>&euro;&nbsp;<?=$total?></dd>
					</dl>

					<p>Please don't forget to mention both the project code <em>190 193 412</em> and <em>JURIX 2018</em> in your description.</p>
				</section>
			</div>
		</div>

		<footer>
			<div class="container">
				<p>Contact: <a href="mailto:Elina Sietsema &lt;e.sietsema@rug.nl&gt;">Elina Sietsema (e.sietsema@rug.nl)</a></p>
			</div>
		</footer>
	</body>
</html>
