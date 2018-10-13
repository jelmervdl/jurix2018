<?php

require_once 'src/rates.php';

if (!isset($_GET['rate']) || !array_key_exists($_GET['rate'], $rates)) {
	header('Location: index.html');
	exit;
}

$dinner = !empty($_GET['dinner']) ? intval($_GET['dinner']) : 0;

$rate = $rates[$_GET['rate']];

$total = $rate['price'][$price_category] + $dinner * $dinner_rate[$price_category];

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
				</li>
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
					<h2>Thank you!</h2>
					<p>Thank you for your registration. We have sent you an email as a confirmation.</p>
				
					<h3>Payment</h3>
					<p>Please pay as soon as possible. Don't forget to mention both the project code 190 193 412 and JURIX 2018 when making your payment.</p>

					<p>Note: we have also sent you this information in your confirmation email.</p>

					<p>
						You have registered as a <?= htmlentities($rate['label']) ?>.
						<?php if ($dinner): ?>You've also opted to join the dinner.<?php endif ?>
						Would you be so kind to transfer &euro;&nbsp;<?=$total?> (<?=html_price_breakdown($_GET['rate'], $dinner)?>) to the following account as soon as possible?
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
				<p>Program chair: <a href="mailto:Monica Palmirani &lt;monica.palmirani@unibo.it&gt;">Monica Palmirani</a><br>
				Contact organisation: <a href="mailto:Sarah van Wouwe &lt;s.k.van.wouwe@rug.nl&gt;">Sarah van Wouwe</a></p>
			</div>
		</footer>
	</body>
</html>
