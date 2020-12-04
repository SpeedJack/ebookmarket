<article>
<div>
<a href=<?= $app->buildLink("/view", ["id" => $book->id])?> >
    <?php
    $coverfile = "assets/covers/$book->filehandle";
    if(file_exists("$coverfile.png"))
        $coverfile = "$coverfile.png";
    if(file_exists("$coverfile.jpg"))
        $coverfile = "$coverfile.jpg";
    echo '
    <h2>'.$book->title.'</h2>
    <h3>'.$book->author.'</h3>
    <img src="'.$coverfile.'" alt="'.$book->title.'_cover" />
    <p><strong>€'.$book->price.'</strong></p>
    ' ?>
</a>
</div>
</article>
