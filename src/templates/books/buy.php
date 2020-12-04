<section>
    <div>
        <h1>Order</h1>
        <ul>
            <li><b>Order ID: </b><?= $orderid?></li>
            <li><b>Title: </b><?= $book->title?></li>
            <li><b>Author: </b><?= $book->author?></li>
            <li><b>Price: </b><?= $book->price?></li>
        </ul>
    </div>
    
    <form action="<?= $app->buildLink("/buy")?>" method="post">
        <label for=cc_number>Credit card number</label>
        <input type="text" name="cc_number" />
        <label for="cc_cv2">CV2</label>
        <input type="text" name="cc_cv2" />
        <label for="expiration">Expiration date</label>
        <input type="text" name="expiration" />
        <input type="hidden" name="csrftoken" value="<?= $this->getCsrfToken() ?>">
        <input type="hidden" name="orderid" value="<?= $orderid ?>">
        <button type="submit" >Buy!</button>
    </form>

</section>
