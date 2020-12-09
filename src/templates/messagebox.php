<?php declare(strict_types=1); ?>
<div id="modal-content">
	<button id="modal-close"<?= $reload === true ? ' data-reload' : '' ?>>Close</button>
	<h1><?= $title ?></h1>
	<p><?= $message ?></p>
</div>
