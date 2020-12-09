<?php declare(strict_types=1); ?>
<header>
	<?php if (!empty($this->showSearchbar)): ?>
		<form id="search-form" autocomplete="on" method="GET">
			<?php if (isset($category)): ?>
				<input type="hidden" name="cat" value="<?= $category->id ?>">
			<?php endif; ?>
			<input id="searchbar" type="text" autocomplete="on" name="s" placeholder="Search..">
			<button id="searchbtn" type="submit">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!-- Font Awesome Free 5.15.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) --><path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/></svg>
			</button>
		</form>
	<?php endif ?>
	<a href="<?= $app->buildLink(null) ?>"><h1 id="title">EbookMarket</h1></a>
</header>
<nav>
	<?= $this->buildMenu() ?>
</nav>

