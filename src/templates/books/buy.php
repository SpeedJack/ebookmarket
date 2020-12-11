<?php declare(strict_types=1); ?>
<div id="modal-content">
<button id="modal-close"<?= $reload === true ? ' data-reload' : '' ?>>Close</button>
    <div>
        <h1>Order</h1>
        <ul>
            <li><b>Title: </b><?= $book->title?></li>
            <li><b>Author: </b><?= $book->author?></li>
            <li><b>Price: </b><?= $book->price?></li>
        </ul>
    </div>
    
    <form id="modal-form" action="<?= $app->buildLink("/purchase")?>" method="post">
        <label for=cc_number>Credit card number</label>
        <input type="text" name="cc_number" />
        <label for="cc_cv2">CV2</label>
        <input type="text" name="cc_cv2" />
        <label for="expiration">Expiration date</label>
        <input type="month" placeholder = "YYYY-MM" name="expiration" />
        <input type="hidden" name="steptoken" value="<?= $this->getCsrfToken() ?>">
        <input type="hidden" name="csrftoken" value="<?= $steptoken ?>">
        <button type="submit" >Buy!</button>
    </form>

</div>
