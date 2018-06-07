<?php
require_once('../../../lib/Laposta.php');
//Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");
Laposta::setApiKey("yuiB1TUFegFAQEAxjG0F");

// includes for form (you might want to namespace these classes)
require_once('./inc/Form.php');
require_once('./inc/FormField.php');

// you can translate error messages here
require_once('./inc/lang.php');

// our form
$form = new Form(array(

	// set the id of the list to be used
	//'list_id' => 'BaImMu3JZA',
	'list_id' => 'dqqu0eijzs',

	// language from lang.php
	'lang' => $lang
));

// if form is posted, try to submit
$errors = false;
if ($_POST) {

	// if errors are found, the form will be rendered again
	if ($form->submit($_POST)) {

		// No errors; we are done. You can place a redirect here.
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
<h1>Subscribe to our newsletter</h1>
<div class="intro">Some text here.</div>
<?php if ($errors) { ?><div class="errors"><?php print htmlspecialchars($lang['error']) ?></div><?php } ?>

<form method="post">
<?php print $form->render(); ?>
<div class="buttonbar"><button type="submit">Subscribe</button></div>
</form>

</div><!-- /.wrapper -->
</body>
</html>
