<main>
    <h1><?= __('Login') ?></h1>
    <form>
        <label for="email"><?= __('Email') ?></label>
        <input type="text" name="email" />
        <label for="password"><?= __('Password') ?></label>
        <input type="password" name="password" />
        <label for="remember_me"><?= __('Remember Me') ?></label>
        <input type="checkbox" name="remember_me">
        <button type="submit"><?= __('Login') ?></button>
    </form>
</main>