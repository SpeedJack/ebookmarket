<h1>Login</h1>
<form id="login-form" autocomplete="on" action="<?= $app->buildLink('/login') ?>" method="POST">
	<label for="username">Username</label>
	<input type="text" id="username" name="username" maxlength="32" minlength="3" pattern="^[A-Za-z0-9_\-.]{3,32}$" autofocus required>
	<label for="password">Password</label>
	<input type="password" id="password" name="password" autocomplete="off" minlength="8" required>
	<input type="checkbox" name="rememberme" value="yes">
	<label for="rememberme" class="inline">Remember Me</label>
	<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
	<button type="submit">Login</button>
	<p>Not registered? <a href="<?= $app->buildLink('/register') ?>">Create your account!</a></p>
	<p>Forget your credentials? <a href="<?= $app->buildLink('/recovery') ?>">Recover your account!</a></p>
</form>
