<!--

$params = book : {
    id: number,
    title: string,
    author: string,
    cover: path,
    price: number
}

-->

<article>
    <?php
    echo '
    <h2>'.$book->title.'</h2>
    <h3>'.$book->author.'</h3>
    <img src="'.$book->cover.'" alt="'.$book->title.'_cover" />
    <p><strong>€'.$book->price.'</strong></p>
    <button>Buy Ebook</button>
    ' ?>
</article>
