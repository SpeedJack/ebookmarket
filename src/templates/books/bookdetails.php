<article>
    <?php
    $coverfile = "assets/covers/$book->filehandle";
    if(file_exists("$coverfile.png"))
        $coverfile = "$coverfile.png";
    if(file_exists("$coverfile.jpg"))
        $coverfile = "$coverfile.jpg";
    ?>
    <h2><?= $book->title ?></h2>
    <h3><?= $book->author ?></h3>
    <img src="<?= '/'.$coverfile ?>" alt="<?= $book->title.'_cover' ?>" />
    <p><strong>€ <?= $book->price ?></strong></p>
    <?php if($bought) : ?> <p><a class= "button" download = "<?= $book->filehandle ?>.pdf" href = "<?= $app->buildLink("/download", ["id" => $book->id]) ?>">Download</a></p>
    <?php else : ?>  <p><a class= "button" href = "<?= $app->buildLink("/buy", ["id" => $book->id]) ?>">Buy</a></p>
    <?php endif; ?>
</article>
