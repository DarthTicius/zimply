<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
	header('Location: /login');
	exit;
}
$user = $_SESSION['user'];
?>

<div>
	<h1>Welcome to the Dashboard</h1>
	<p>User ID: <?php echo htmlspecialchars($user['id']) ?></p>
	<p>Email: <?php echo htmlspecialchars($user['email']) ?></p>
	<a href="/login">Logout</a>
</div>