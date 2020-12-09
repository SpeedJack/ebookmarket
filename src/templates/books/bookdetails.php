<?php declare(strict_types=1); ?>
<article>
	<h1><?= static::htmlEscape($book->title) ?>&nbsp;<span class="bookpubdate">(<?= $book->pubdate ?>)</span></h1>
	<p class="bookauthor"><?= static::htmlEscape($book->author) ?></p>
	<img class="bookcover" alt="<?= static::htmlEscapeQuotes($book->title) ?>" src="<?= $book->cover ?>">
	<?php if($bought): ?>
		<?php foreach ($book->availableFormats as $format): ?>
			<a class="button fmt-<?= $format ?>" href="<?= $app->buildLink('/download', [ 'id' => $book->id, 'fmt' => $format ]) ?>" download="<?= static::htmlEscapeQuotes($book->filehandle) ?>">Download <?= strtoupper($format) ?></a>
		<?php endforeach; ?>
	<?php else: ?>
		<p class="bookprice"><?= number_format($book->price, 2, ',', '') ?></p>
		<?php if ($visitor->isLoggedIn()): ?>
			<form id="buy-form" autocomplete="off" action="<?= $app->buildLink('/buy') ?>" method="POST">
				<input type="hidden" name="id" value="<?= $book->id ?>">
				<input type="hidden" name="steptoken" value="<?= $this->getBuyStepToken() ?>">
				<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
				<button type="submit">Buy Book</button>
			</form>
		<?php endif; ?>
	<?php endif; ?>
</article>
