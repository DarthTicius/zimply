<div class="form-container">
	<h2>Register</h2>
	<form action="/register" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token) ?>">

		<div class="form-group">
			<label for="email">Email:</label>
			<input type="email" id="email" name="email" required>
		</div>

		<div class="form-group">
			<label for="password">Password:</label>
			<input type="password" id="password" name="password" required
				minlength="6">
			<span class="">must be at least 6 length</span>
		</div>

		<div class="form-group">
			<label for="confirm_password">Confirm Password:</label>
			<input type="password" id="confirm_password" name="confirm_password" required>
		</div>

		<button type="submit">Register</button>
	</form>
</div>