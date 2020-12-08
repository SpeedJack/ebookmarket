<?php declare(strict_types=1); ?>
<h1>Account Recovery</h1>
<form id="recovery-form" autocomplete="on" action="<?= $app->buildLink('/recovery') ?>" method="POST">
	<label for="email">Email</label>
	<input type="text" name="email" maxlength="254" autofocus required>
	<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
	<button type="submit">Recover my account</button>
</form>
