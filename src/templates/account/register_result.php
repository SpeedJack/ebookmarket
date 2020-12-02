<h1>Registration <?= $success ? "Complete!" : "Failed!"?></h1>
<?php if($success) : ?> <p>Check your mailbox to complete the registration</p>
<?php else : ?>  <p><a href="<?= $app->buildLink("/register") ?>">Click here to Retry</a></p>
<?php endif; ?>