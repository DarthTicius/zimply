<div class="form-container">
	<?php if (isset($_SESSION['error'])): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
		<?php unset($_SESSION['error']); ?>
	<?php endif; ?>

	<?php if (isset($_SESSION['success'])): ?>
		<div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
		<?php unset($_SESSION['success']); ?>
	<?php endif; ?>
	<h2>Reset Password</h2>
	<form action="/reset-password/confirm" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token) ?>">
		<input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? $reset_token) ?>">

		<div class="form-group">
			<label for="password">New Password:</label>
			<input type="password" id="password" name="password" required minlength="8"
				pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
				title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 characters">
			<span id="password-requirements">
				Password must contain:
				- At least 8 characters
				- One uppercase letter
				- One lowercase letter
				- One number
			</span>
		</div>

		<div class="form-group">
			<label for="confirm_password">Confirm Password:</label>
			<input type="password" id="confirm_password" name="confirm_password" required>
			<span id="password-match-message"></span>
		</div>

		<button type="submit" id="submit-btn">Set New Password</button>
	</form>
</div>
<script>
	document.getElementById('confirm_password').addEventListener('input', function() {
		const password = document.getElementById('password').value;
		const confirmPassword = this.value;
		const matchMessage = document.getElementById('password-match-message');

		if (password !== confirmPassword) {
			this.setCustomValidity('Passwords must match');
			matchMessage.textContent = 'Passwords do not match';
			matchMessage.style.color = 'red';
		} else {
			this.setCustomValidity('');
			matchMessage.textContent = 'Passwords match';
			matchMessage.style.color = 'green';
		}
	});

	document.getElementById('password').addEventListener('input', function() {
		const pattern = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/;
		if (!pattern.test(this.value)) {
			this.setCustomValidity('Password must meet all requirements');
		} else {
			this.setCustomValidity('');
		}
	});
</script>