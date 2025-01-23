<div class="form-container">
	<?php if (isset($_SESSION['error'])): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
		<?php unset($_SESSION['error']); ?>
	<?php endif; ?>

	<?php if (isset($_SESSION['success'])): ?>
		<div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
		<?php unset($_SESSION['success']); ?>
	<?php endif; ?>
	<h2>Login</h2>
	<form action="/login" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token) ?>">

		<div class="form-group">
			<label for="email">Email:</label>
			<input type="email" id="email" name="email" required>
		</div>

		<div class="form-group">
			<label for="password">Password:</label>
			<input type="password" id="password" name="password" required>
		</div>

		<button type="submit">Login</button>
	</form>
</div>