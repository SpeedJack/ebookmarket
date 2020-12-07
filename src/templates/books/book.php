<article>
	<a href=<?= $app->buildLink('/view', [ 'id' => $book->id ]) ?>>
		<img class="bookcover" alt="<?= static::htmlEscapeQuotes($book->title) ?>" src="<?= $book->cover ?>">
		<div class="bookdesc">
			<span class="booktitle"><?= static::htmlEscape($book->title) ?></span>
			<span class="bookauthor"><?= static::htmlEscape($book->author) ?></span>
			<span class="bookprice">$ <?= $book->price ?></span>
		</div>
	</a>
</article>
