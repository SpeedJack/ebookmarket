<?php declare(strict_types=1); ?>
<h1>Enter a new password</h1>
<form id="changepwd-form" autocomplete="off" action="<?= $app->buildLink('/changepwd') ?>" method="POST">
	<label for="password">Password</label>
	<input type="password" id="password" name="password" autocomplete="off" minlength="8" data-minpwdstrength="<?= $app->config('min_password_strength') ?>" required>
	<label for="password-confirm">Confirm Password</label>
	<input type="password" id="password-confirm" name="password-confirm" autocomplete="off" minlength="8" required>
	<input type="hidden" name="token" value="<?= $token ?>">
	<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
	<input type="hidden" name="username" id="username" value="<?= static::htmlEscapeQuotes($user->username) ?>">
	<input type="hidden" name="email" id="email" value="<?= static::htmlEscapeQuotes($user->email) ?>">
	<button type="submit">Save Password</button>
</form>
