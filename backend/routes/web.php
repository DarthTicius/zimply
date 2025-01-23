<?php
return [
	// GET for displaying forms
	'GET /' => ['WebController', 'login'],
	'GET /login' => ['WebController', 'login'],
	'GET /register' => ['WebController', 'register'],
	'GET /reset-password' => ['WebController', 'resetPasswordRequest'],
	'GET /reset-password/confirm' => ['WebController', 'resetPasswordConfirm'],
	'GET /dashboard' => ['WebController', 'dashboard'],

	// POST for handling form submissions
	'POST /login' => ['AuthController', 'login'],
	'POST /register' => ['AuthController', 'register'],
	'POST /reset-password' => ['AuthController', 'initiatePasswordReset'],
	'POST /reset-password/confirm' => ['AuthController', 'resetPassword'],
	'POST /logout' => ['AuthController', 'logout'],
];
