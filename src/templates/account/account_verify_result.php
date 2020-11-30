<h1>Verification <?= $success ? "Complete!" : "Failed!"?></h1>
<?php if($success) : ?> <p><a href="<?= $app->buildLink("/login") ?>">Click here to Login</a></p>
<?php else : ?>  <p><a href="<?= $app->buildLink("/") ?>">Click here to Return to HomePage</a></p>
<?php endif; ?>