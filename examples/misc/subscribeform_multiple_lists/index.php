<?php
require_once('../../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

// includes for form (you might want to namespace these classes)
require_once('./inc/Form.php');
require_once('./inc/FormField.php');
require_once('./inc/FormList.php');
require_once('./inc/lang.php');

// our form
$form = new Form(array(

	// Set the id's and labels of the lists to be shown.
	// The fields for the first list will be used as form fields.
	'lists' => array(
		array('id' => 'aglie5k0mm', 'label' => 'List A'),
		array('id' => 'pq0loro5rs', 'label' => 'List B'),
		array('id' => 'cgzhwm2xu2', 'label' => 'List C')
	),

	// language from lang.php
	'lang' => $lang
));

// if form is posted, try to submit
$errors = false;
if ($_POST) {

	// if errors are found, the form will be rendered again
	if ($errors = $form->submit($_POST)) {

		// done! redirect?
		print "DONE";
		exit;

	} else {
		$errors = true;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Subscribe form</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="./css/form.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="wrapper">
<h1>Subscribe to our newsletters</h1>
<div class="intro">Some text here.</div>
<?php if ($errors) { ?><div class="errors">Sorry, there was an error</div><?php } ?>

<form method="post">
<?php print $form->renderForm($_POST); ?>

<div class="lists">
<h2>To which lists?</h2>
<?php print $form->renderLists($_POST); ?>
</div><!-- /.lists -->

<div class="buttonbar"><button type="submit">Subscribe</button></div>
</form>

</div><!-- /.wrapper -->
</body>
</html>
