<!--

$params = {
    name, : string, 
    books : [
    {
        id: number,
        title: string,
        author: string,
        cover: path,
        price: number
    }, 
    ...
    ]
}

-->

<section>
    <h1><?= __($name)?></h1>
    <ul>    
        <?php        
        foreach ($books as $book){
           echo '<li>';
           $this->loadTemplate("books/book", array('book' => $book));
           echo '<p>
                    <a href="'.$app->buildLink('books/view',array('id'=>$book->id)).'">
                    '.__('View More').'
                    </a>
                </p>
            </li>';
        }
        ?>
    </ul>
</section>
