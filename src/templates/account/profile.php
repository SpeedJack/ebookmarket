<section>
    <h1>Profile of <?=$user->username ?></h1>
    <h2>Info</h2>
    <ul>
        <li><p><b>Email: </b><?= $user->email ?></p></li>
        <li><p><b>Username: </b> <?= $user->username ?></p></li>
        <li><p><a href="<?= $app->buildLink("books/library") ?>"> My Library</a></li>
    </ul>
    <h2>Set a new password</h2>
    <p>
    <?php 
    
    if(isset($success)){
        if($success) {
            echo  "Password changed succesfully";
        } else {
            echo "Something went wrong during your password change request"; 
       } 
    };
    
    ?>
    </p>
    <form actiion = <?= $app->buildLink("account/")?> method ="post">
        <label for = "current_password">Current Password</label>
        <input name = "current_password" type = "password" />
        <label for="password">New Password</label>
        <input type="password" name="password" />
        <label for="password_confirm">Confirm New Password</label>
        <input type="password" name = "password_confirm" />
        <input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
        <button type="submit">Save</button>
    </form>
</section>