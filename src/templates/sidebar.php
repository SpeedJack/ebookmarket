<aside>
	<nav>
		<a href="<?= $app->buildLink($visitor->getRoute()) ?>"
		<?php if ($activecat === null): ?>
			class="active"
		<?php endif; ?>
		>All Books</a>
		<?php foreach ($categories as $category): ?>
			<a href="<?= $app->buildLink($visitor->getRoute(), ['cat' => $category->id]) ?>"
			<?php if ($category->id === $activecat): ?>
				class="active"
			<?php endif; ?>
o			><?= $category->name ?></a>
		<?php endforeach; ?>
	<nav>
<aside>
