<main>
    <h1>Account Recovery</h1>
    <form>
        <label for="email">Email</label>
        <input type="text" name="email" />
        <input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
        <button type="submit">Recover my account's password</button>
    </form>
</main>
