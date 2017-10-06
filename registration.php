<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once 'src/form.php';
require_once 'src/csv.php';
require_once 'src/mail.php';
require_once 'src/rates.php'; // includes $rates and $late

$options = array();

foreach ($rates as $name => $rate)
	$options[$name] = sprintf('%s (€ %d)', $rate['label'], $rate['price']);

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
$registration_form->add(new FormCheckboxField('dinner', sprintf('I want to join the conference dinner (&euro; %d; Wednesday November 8)', $dinner_rate)));
$registration_form->add(new FormCheckboxField('martinitoren', 'I want to join the trip to the Martinitoren (Wednesday November 8)'));

$errors = $registration_form->submitted() ? $registration_form->validate() : array();

if ($registration_form->submitted() && count($errors) == 0) {
	// Combine all the data (and calculate the total amount due)
	$data = $registration_form->data();
	$data['total'] = $rates[$data['register_as']]['price'] + (!empty($data['dinner']) ? $dinner_rate : 0);

	// First, add the info to a CSV file here on the server
	$csv = new CSVFile('../data/signups.txt');
	$csv->add($data);

	// Then, make sure Elina receives a mail about it
	$mail_elina = Email::fromTemplate('mails/elina.txt', $data);
	$mail_elina->send();

	// Also, let the person in question know that their registration has come through
	// (and tell them what to do next)
	$mail_registrant = Email::fromTemplate('mails/registrant.txt', $data);
	$mail_registrant->send();

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
		<title>Register &middot; BNAIC 2017</title>
		<meta name="description" content="The 29th Benelux Conference on Artificial Intelligence">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="assets/css/layout.css">
	</head>
	<body>
		<header class="website-header small">
			<div class="text">
				<div class="container">
					<h1>BNAIC 2017</h1>
					<p>The 29th Benelux Conference on Artificial Intelligence</p>
					<p>November 8&ndash;9, 2017 in Groningen, The Netherlands</p>
				</div>
			</div>
		</header>

		<nav class="website-navigation fixed" tabindex="-1">
			<div class="container">
				<a href="index.html#home">Home</a>
				<a href="cfp.html">Call for papers</a>
				<a href="committees.html">Committees</a>
				<a href="practicalities.html">Practicalities</a>
				<a href="registration.php" class="registration active">Registration</a>
			</div>
		</nav>

		<div class="website-content">
			<div class="container">
				<section>
					<h1>Registration</h1>
					<p>Please complete the form below to register for the conference. Note that early registration rates apply until Friday October 27 (the day after the final paper submission deadline).</p> 
					
					<h3>Rates</h3>
					<table>
					<tr><td>Bachelor or Master student:&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td>early: &euro; 50</td><td>late: &euro; 50</td></tr>
					<tr><td>PhD student:</td>
						<td>early: &euro; 110</td><td>late: &euro; 130</td></tr>
					<tr><td>Regular:</td>
						<td>early: &euro; 160&nbsp;&nbsp;&nbsp;&nbsp;</td><td>late: &euro; 180</td></tr>
					</table>
				</section>
				<section>
					<form method="post" action="registration.php">
						<h3>Personal</h3>
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

						<h3>Relation</h3>
						<div class="form-grouping">
						<?= $registration_form->register_as->render($errors) ?>
						</div>
						<div class="form-grouping">
						<?= $registration_form->affiliation->render($errors) ?>
						</div>

						<h3>Additional</h3>
						<?= $registration_form->dinner->render($errors) ?>
						<?= $registration_form->martinitoren->render($errors) ?>
	
						<div class="form-controls">
							<button type="submit">Submit registration</button>
						</div>
					</form>
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
