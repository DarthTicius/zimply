<div class="form-container">
	<h2>Reset Password</h2>
	<form action="/reset-password" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token) ?>">

		<div class="form-group">
			<label for="email">Email:</label>
			<input type="email" id="email" name="email" required
				pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
				title="Please enter a valid email address">
		</div>

		<button type="submit">Request Reset Link</button>
	</form>
</div>