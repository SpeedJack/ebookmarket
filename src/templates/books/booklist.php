<?php declare(strict_types=1) ?>
<h1><?= $title ?></h1>
<div id="booklist">
	<?php        
	foreach ($books as $book)
		$this->loadTemplate('books/book', [ 'book' => $book ] );
	?>
</div>
