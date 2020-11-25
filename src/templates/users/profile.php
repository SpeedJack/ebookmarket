

<main>
    <h1><?= __($user->username . '\'s Profile') ?><h1>
    <?php $this->loadTemplate("users/sidebar", array($user)); ?>        
</main>