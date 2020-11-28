<header>
    <nav>
        <img src="path/to/logo" alt="Logo"/>
        <h1><?= $this->title ?></h1>
        <ul>
            <li><a href="<?php $app->buildLink('books/browse', array('category' => 'all')) ?>"><?= __('Categories') ?></a></li>
            <ul>
                <!--Just an example suppes $categories retrieved from DB-->
                <?php
                $categories = array(
                                (object) array("id"=>"1", "name"=>"horror"),
                                (object) array("id"=>"2", "name"=>"fantasy"),
                                (object) array("id"=>"3", "name"=>"biography")
                ); 
                //-----------------------------------------------------------
                foreach($categories as $category) {
                    echo '
                    <li>
                        <a href="' . $app->buildLink('books/browse', array('category'=>$category->id)) . '">
                            '.__($category->name).'
                        </a>
                    </li>';
                }
                ?>
                </ul>
            <li><a href="<?php $app->buildLink('books/browse', array('sort'=>'date')) ?>"><?= __('New') ?></a></li>
            <li><a href="<?php $app->buildLink('books/browse', array('sort'=>'likes')) ?>"><?= __('Ranking') ?></a></li>
        </ul>
        <form>
            <input 
                type="input" 
                name="name" 
                placeholder="<?= __('Search yur favourite book') ?>"
            />
            <button type="submit" >
                <?= __('Search') ?>
            </button>
        </form>
        <p><a href =<?php $app->buildLink('users/view')?>><?=__("Profile")?></a></p>
    </nav>
</header>
