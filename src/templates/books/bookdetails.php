<article>
	<img class="bookcover" alt="<?= static::htmlEscapeQuotes($book->title) ?>" src="<?= $book->cover ?>">
	<div class="bookdesc">
		<span class="booktitle"><?= static::htmlEscape($book->title) ?></span>
		<span class="bookauthor"><?= static::htmlEscape($book->author) ?></span>
		<?php if($bought): ?>
			<?php foreach (explode($book->availableFormats, ',') as $format): ?>
				<a class="button" href="<?= $app->buildLink('/download', [ 'id' => $book->id, 'fmt' => $format ]) ?>" download="<?= $book->filehandle ?>">Download <?= strtoupper($format) ?></a>
			<?php endforeach; ?>
		<?php else: ?>
			<span class="bookprice"><?= number_format($book->price, 2, ',', '') ?></span>
			<a class="button" href="<?= $app->buildLink('/buy', [ 'id' => $book->id ]) ?>">Buy Book</a>
		<?php endif; ?>
	</div>
</article>
