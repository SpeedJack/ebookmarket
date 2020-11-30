<?php declare(strict_types=1) ?>
<p><?= 'Hello, this is a test page!' ?></p>
<p><?php printf('...and here is a link to our <a href="%s">phpinfo()</a>!', $app->buildLink('/phpinfo')) ?></p>
