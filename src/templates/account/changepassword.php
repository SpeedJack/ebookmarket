<h1>Enter a new password</h1>
<form id="changepwd-form" autocomplete="off" action="<?= $app->buildLink('/changepwd') ?>" method="POST">
	<label for="password">Password</label>
	<input type="password" name="password" autocomplete="off" minlength="8" required>
	<label for="password-confirm">Confirm Password</label>
	<input type="password" name="password-confirm" autocomplete="off" minlength="8" required>
	<input type="hidden" name="token" value="<?= $usertoken ?>">
	<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
	<button type="submit">Save Password</button>
</form>
