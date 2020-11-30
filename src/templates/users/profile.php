

<main>
    <h1><?= $user->username . '\'s Profile' ?><h1>
    <?php $this->loadTemplate("users/sidebar", array($user)); ?>        
</main>
