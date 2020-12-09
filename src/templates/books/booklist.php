<?php declare(strict_types=1); ?>
<h1><?= $title ?></h1>
<div id="booklist">
	<div class="page-controls">
		<?php if ($page > 1): ?>
			<a id="page-prev" href="<?= $this->buildPageLink($page - 1, $category, $search) ?>">&lt;&lt; Prev Page</a>
		<?php endif; ?>
		<?php if (!$lastpage): ?>
			<a class="page-next" href="<?= $this->buildPageLink($page + 1, $category, $search) ?>">Next Page &gt;&gt;</a>
		<?php endif; ?>
	</div>
	<?php
	foreach ($books as $book)
		$this->loadTemplate('books/book', [ 'book' => $book, 'islibrary' => $islibrary ] );
	?>
	<div class="page-controls">
		<?php if ($page > 1): ?>
			<a id="page-prev" href="<?= $this->buildPageLink($page - 1, $category, $search) ?>">&lt;&lt; Prev Page</a>
		<?php endif; ?>
		<?php if (!$lastpage): ?>
			<a class="page-next" href="<?= $this->buildPageLink($page + 1, $category, $search) ?>">Next Page &gt;&gt;</a>
		<?php endif; ?>
	</div>
</div>
