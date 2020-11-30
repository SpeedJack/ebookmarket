<main>
    <h1>Register</h1>
    <form action =  "<?php $app->buildLink("auth/register") ?>" method = "post">
        <label for="username">Username</label>
        <input type="text" name="username" />
        <label for="email">Email</label>
        <input type="email" name="email" />
        <label for="password">Password</label>
        <input type="password" name="password" />
        <label for="password_confirm">Confirm password</label>
        <input type="password" name = "password_confirm" />
        <label for="accept_terms">Accept terms and conditions</label>
        <input type="checkbox" name="accept_terms" />
        <input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
        <button type="submit">Register</button>
    </form>
</main>
