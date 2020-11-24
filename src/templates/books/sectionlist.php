<!--

$params = sections : [
    {
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
    ],
    ...
]

-->


<main>
    <?php 
    //Example-------------------------------------------
        $sections = array(
            array(
                "name" => "section 1",
                "books" => 
                
                $books = array(
                    (object) array('id'=>'1',
                        'cover'=>'path/to/cover', 
                        'title'=>'La Divina Commedia',
                        'author'=>'Dante Alighieri',
                        'price'=> 9.99),
                    (object) array('id'=>'1',
                    'cover'=>'path/to/cover', 
                    'title'=>'La Divina Commedia',
                    'author'=>'Dante Alighieri',
                    'price'=> 9.99),
        
                    (object) array('id'=>'1',
                    'cover'=>'path/to/cover', 
                    'title'=>'La Divina Commedia',
                    'author'=>'Dante Alighieri',
                    'price'=> 9.99),
        
                    (object) array('id'=>'1',
                    'cover'=>'path/to/cover', 
                    'title'=>'La Divina Commedia',
                    'author'=>'Dante Alighieri',
                    'price'=> 9.99)
                )
            )  
        );
         //----------------------------------------------------------
        foreach($sections as $section)
            $this->loadTemplate("books/booklist", $section);
    ?>
</main>