<?php declare(strict_types=1); ?>
<header>
	<input id="searchbar" type="text" placeholder="Search..">
	<button id="searchbtn">
		<svg xmlns="http://www.w3.org/2000/svg" width="20", height="20" fill="#D5D1CB" viewBox="0 0 512 512"><!-- Font Awesome Free 5.15.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) --><path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/></svg>
	</button>
	<h1 id="title">EbookMarket</h1>
	<nav>
		<a href="<?= $app->buildLink('') ?>"
		<?php if ($visitor->isPage('book') && !$visitor->isAction('library')): ?>
			class="active"
		<?php endif; ?>
		>Shop</a>
		<a href="<?= $app->buildLink('book/library') ?>"
		<?php if ($visitor->isRoute('book/library')): ?>
			class="active"
		<?php endif; ?>
		>My Library</a>
		<div class="right">
		<?php if ($visitor->isLoggedIn()): ?>
			<p>Logged in as: </p><a id="username" href="<?= $app->buildLink('account') ?>"
			<?php if ($visitor->isPage('account')): ?>
				class="active"
			<?php endif; ?>
			><?= $visitor->user()->username ?></a>
		<?php else: ?>
			<a href="<?= $app->buildLink('account/register') ?>"
			<?php if ($visitor->isRoute('account/register')): ?>
				class="active"
			<?php endif; ?>
			>Sign Up</a>
			<a href="<?= $app->buildLink('account/login') ?>"
			<?php if ($visitor->isRoute('account/login')): ?>
				class="active"
			<?php endif; ?>
			>Login</a>
		<?php endif; ?>
		</div>
	</nav>
</header>

