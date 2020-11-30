<main>
    <h2>Enter a new password</h2>
    <form>
        <label for="password">Password</label>
        <input type="password" name="password" />
        <label for="password">Confirm Password</label>
        <input type="password" name="password_confirm" />
        <input type="hidden" name="usertoken" value="<?= $usertoken ?>">
        <input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
        <button type="submit">Save Password</button>
    </form>
</main>
