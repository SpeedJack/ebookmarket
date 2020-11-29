<main>
    <h1><?= __('Login') ?></h1>
    <form action="<?= $app->buildLink("/login") ?>" method = "post">
        <label for="username"><?= __('Username') ?></label>
        <input type="text" name="username" />
        <label for="password"><?= __('Password') ?></label>
        <input type="password" name="password" />
        <label for="remember_me"><?= __('Remember Me') ?></label>
        <input type="checkbox" name="remember_me">
        <button type="submit"><?= __('Login') ?></button>
    </form>
</main>
