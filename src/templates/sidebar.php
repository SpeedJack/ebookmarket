<?php declare(strict_types=1);
$navhtml = $this->buildSidebar();
if (empty($navhtml))
	return;
?>
<aside>
	<nav>
		<?= $navhtml ?>
	</nav>
</aside>
