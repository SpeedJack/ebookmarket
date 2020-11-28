<main>
    <h1><?= __('Register') ?></h1>
    <form action =  "<?php $app->buildLink("auth/register") ?>" method = "post">
        <label for="username"><?= __('Username') ?></label>
        <input type="text" name="username" />
        <label for="email"><?= __('Email') ?></label>
        <input type="email" name="email" />
        <label for="password"><?= __('Password') ?></label>
        <input type="password" name="password" />
        <label for="password_confirm"><?= __('Confirm password') ?></label>
        <input type="password" name = "password_confirm" />
        <label for="accept_terms"><?= __('Accept terms and conditions') ?></label>
        <input type="checkbox" name="accept_terms" />
        <button type="submit"><?= __('Register') ?></button>
    </form>
</main>
