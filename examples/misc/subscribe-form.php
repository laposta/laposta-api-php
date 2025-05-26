<?php

/**
 * Basic subscribe form example using the Laposta API client.
 *
 * This script demonstrates a minimal POST-to-API flow using environment variables.
 *
 * Note: This example does not implement CSRF protection. If you use this in production,
 * you should add a CSRF token to prevent cross-site request forgery attacks.
 */

require_once('../../autoload.php');

use LapostaApi\Laposta;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST');

// Validate required variables
if (!$listId) {
    echo "Error: LP_EX_LIST environment variable is not set.\n";
    exit(1);
}

// Initialize Laposta
$laposta = new Laposta($apiKey);
$memberApi = $laposta->memberApi();

// handle form POST
$success = false;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $memberApi->create($listId, [
            'email' => $_POST['email'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ]);
        $success = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Subscribe</title>
</head>
<body>
<h1>Subscribe to our newsletter</h1>

<?php if ($success) : ?>
    <p>Thank you! You’ve been subscribed.</p>
<?php else : ?>
    <?php if ($error) : ?>
        <p style="color:red;">Error: <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>
            Email:
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </label>
        <button type="submit">Subscribe</button>
    </form>
<?php endif; ?>
</body>
</html>