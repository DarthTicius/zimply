<!DOCTYPE html>
<html lang="en">
<?php
function displaySessionMessage($type = 'error')
{
	if (isset($_SESSION[$type])) {
		$message = $_SESSION[$type];
		unset($_SESSION[$type]);
		echo "<div class='alert alert-$type'>$message</div>";
	}
}
$currentDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($currentDir, '/') . '/';
$faviconPath = $basePath . 'favicon.ico';
?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="<?php echo $faviconPath; ?>" type="image/x-icon">
	<title><?php echo $title ?? 'Auth System' ?></title>
	<style>
		body {
			font-family: Arial, sans-serif;
			max-width: 400px;
			margin: 40px auto;
			padding: 20px;
		}

		.container {
			max-width: 400px;
			margin: 40px auto;
			padding: 20px;
		}

		.alert.alert-success {
			background-color: #28a745;
			color: white;
			padding: 10px;
			margin-bottom: 10px;
			border-radius: 4px;
		}

		@media (min-width: 768px) and (max-width: 1023px) {
			.container {
				max-width: 700px;
			}
		}

		@media (min-width: 1024px) and (max-width: 1279px) {
			.container {
				max-width: 1000px;
			}
		}

		@media (min-width: 1080px) {
			.container {
				max-width: 1024px;
			}
		}

		.form-container {
			background: #f9f9f9;
			padding: 20px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.form-group {
			margin-bottom: 15px;
		}

		label {
			display: block;
			margin-bottom: 5px;
		}

		input {
			width: 100%;
			padding: 8px;
			border: 1px solid #ddd;
			border-radius: 4px;
			box-sizing: border-box;
		}

		button {
			width: 100%;
			padding: 10px;
			background: #007bff;
			color: white;
			border: none;
			border-radius: 4px;
			cursor: pointer;
		}

		button:hover {
			background: #0056b3;
		}

		.error {
			color: #dc3545;
			padding: 10px;
			margin-bottom: 10px;
			border-radius: 4px;
			background: #f8d7da;
		}

		.success {
			color: #28a745;
			padding: 10px;
			margin-bottom: 10px;
			border-radius: 4px;
			background: #d4edda;
		}

		.nav {
			margin-bottom: 20px;
			text-align: center;
		}

		.nav a {
			margin: 0 10px;
			color: #007bff;
			text-decoration: none;
		}
	</style>
</head>

<body>
	<div class="nav">
		<?php if (isset($_SESSION['user'])) { ?>
			<a href="#" id="logout-link">Logout</a>
		<?php } else { ?>
			<a href="/login">Login</a>
		<?php } ?>
		<a href="/register">Register</a>
		<a href="/reset-password">Reset Password</a>
	</div>

	<?php displaySessionMessage('error'); ?>
	<?php displaySessionMessage('success'); ?>

	<div class="container">
		<?php echo $content ?>
	</div>
</body>

</html>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const message = localStorage.getItem('redirectMessage');
		const messageType = localStorage.getItem('redirectMessageType');

		if (message) {
			const messageElement = document.createElement('div');
			messageElement.className = messageType;
			messageElement.textContent = message;

			const container = document.querySelector('.container');
			if (container) {
				container.insertBefore(messageElement, container.firstChild);
			}

			localStorage.removeItem('redirectMessage');
			localStorage.removeItem('redirectMessageType');
		}
	});
</script>