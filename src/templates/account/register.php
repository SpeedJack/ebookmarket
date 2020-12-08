<h1>Create a new account</h1>
<form id="register-form" autocomplete="on" action="<?= $app->buildLink("account/register") ?>" method="POST">
	<label for="username">Username</label>
	<input type="text" id="username" name="username" maxlength="32" minlength="3" pattern="^[A-Za-z0-9_\-.]{3,32}$" autofocus required>
	<label for="email">Email</label>
	<input type="email" id="email" name="email" maxlength="254" required>
	<p class="validator" id="account-recovery">This email address is already in use. If you do not remember your credentials, <a href="<?= $app->buildLink('/recovery') ?>">recover your account</a>.</p>
	<label for="password">Password</label>
	<input type="password" id="password" name="password" autocomplete="off" minlength="8" required>
	<label>Confirm password</label>
	<input type="password" id="password-confirm" autocomplete="off" minlength="8" required>
	<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
	<button type="submit">Create Account</button>
	<p>Already registered? <a href="<?= $app->buildLink('/login') ?>">Login!</a></p>
</form>
