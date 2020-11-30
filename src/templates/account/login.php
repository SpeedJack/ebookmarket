<main>
    <h1>Login</h1>
    <form action="<?= $app->buildLink("/login") ?>" method = "post">
        <label for="username">Username</label>
        <input type="text" name="username" />
        <label for="password">Password</label>
        <input type="password" name="password" />
        <label for="remember_me">Remember Me</label>
        <input type="checkbox" name="remember_me">
	<input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
        <button type="submit">Login</button>
    </form>
</main>
