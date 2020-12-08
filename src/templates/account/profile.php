<?php declare(strict_types=1); ?>
<h1>Your Account</h1>

<h2>Info</h2>
<p><strong>Email:</strong> <?= $user->email ?></p>
<p><strong>Username:</strong> <?= $user->username ?></p>

<h2>Change password</h2>
<form id="changepwd-form" autocomplete="off" action="<?= $app->buildLink('/changepwd')?>" method="POST">
	<label for="oldpassword">Current Password</label>
	<input id="oldpassword" name="oldpassword" type="password" autocomplete="off" minlength="8" required>
	<label for="password">New Password</label>
	<input id="password" type="password" name="password" autocomplete="off" minlength="8" required>
	<label for="password-confirm">Confirm New Password</label>
	<input id="password-confirm" type="password" name="password-confirm" autocomplete="off" minlength="8" required>
	<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
	<button type="submit">Save</button>
</form>

<h2>Sessions</h2>
<p>Currently, there are <?= $sessioncount ?> other sessions active.</p>
<?php if ($sessioncount > 0): ?>
	<form id="secure-form" autocomplete="off" action="<?= $app->buildLink('/secure') ?>" method="POST">
		<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
		<button type="submit">Terminate all other sessions</a>
	</form>
<?php endif; ?>
