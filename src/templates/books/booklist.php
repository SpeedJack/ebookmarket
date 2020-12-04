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
        <?php        
        foreach ($books as $book){
           $this->loadTemplate("books/book", array('book' => $book));
        }
        ?>
</section>
