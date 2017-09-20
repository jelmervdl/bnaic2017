<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require 'src/form.php';

$registration_form = new Form();
$registration_form->add(new FormTextField('first_name', 'First name', ['required' => true]));
$registration_form->add(new FormTextField('last_name', 'Last name', ['required' => true]));
$registration_form->add(new FormEmailField('email', 'Email address', ['required' => true]));

$registration_form->add(new FormSelectField('register_as', 'Registering as', [
							'student' => 'BSc or MSc student',
							'phd' => 'PhD student',
							'regular' => 'Regular'
						], ['required' => true]));

$errors = $registration_form->submitted() ? $registration_form->validate() : [];

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
				<!-- <a href="important-dates.html">Important dates</a> -->
				<a href="location.html">Location</a>
				<a href="registration.php" class="registration active">Registration</a>
			</div>
		</nav>

		<div class="website-content">
			<div class="container">
				<section>
					<h1>Registration</h1>
					<p>Something about the various costs for the various types of visitors, e.g. cheap for students, expensive for people who's supervisor/boss is paying anyway.</p> 
				</section>
				<section>
					<form method="post" action="registration.php">
						<h3>Personal</h3>
						<?= $registration_form->first_name->render($errors) ?>
						<?= $registration_form->last_name->render($errors) ?>
						<?= $registration_form->email->render($errors) ?>

						<h3>Relation</h3>
						<?= $registration_form->register_as->render($errors) ?>
	
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
