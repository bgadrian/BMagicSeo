<?php
    
    //we need the class
    require('bmagicseo.class.php');
    
    //instance
    $bseo = new BMagicSeo();
    
    //change an option and declare the preposition list
    //I recommend that you don't disable any of the default options, only in special cases. 
    $my_setings = array(
            'trim'      => true,
            //a small set of Romanian prepositions and usual small words
            'prop'      => array('de la','cu','fara','in','intru','la','pe', 'de','pe la',
                            'de pe la','sub','sau','ori','orice','oricum','si', 'peste', 'printre', 'prin', 'dupa',
                            'din','de la','pana la', 'dintre', 'pentru')
    
    );
    $bseo->set_options($my_setings);
    
    //let's say you have the folloing product, read from your database
    $title = '<h1>My &New #25 inches product!</h1>'; //a messy possible title
    //worst case scenario, a HTML description with hidden (from copy paste) characters.
    $description = <<<DESC
    <div style="clear:both;padding-top:20px" class="kindle-desc"><img src="http://g-ecx.images-amazon.com/images/G/01/kindle/shasta/photos/h1._V188702449_.gif" width="941" align="left" alt="Revolutionary Wireless Reading Device" height="73" border="0" /></div><div style="clear:both;" class="kindle-desc"><table border="0" style="margin-left:20px; padding-right:60px; font-size: 16pt; margin-bottom:19px; margin-top:39px;" width="100%"><tr><td valign="top">
Kindle is our #1 bestselling item for two years running. It&#8217;s also the most-wished-for, most-gifted, and has the most 5-star reviews of any product on Amazon. <i>Now it's even better</i>.
      <ul><li><b>All-New, High-Contrast E Ink Screen</b> &#8211; 50% better contrast with latest E Ink Pearl technology        </li><li><b>Read in Bright Sunlight</b> &#8211; No glare</li></ul></div> 
         ¸¹º»¼½¾¿
DESC;
     
    //let/s presume that you have a web root and a specific link extension, also a product ID
    define('WEB_ROOT','http://my_site.com/');
    define('EXT','.html');
    $id = '45';
    
    //now let's play with it
    
    //generate the link and other needed HTML elements : 
    echo '<h1>BTooLs Magic Seo Class examples </h1><h2>Make your custom web safe and friendly links (articles, products, categories, pages, etc)</h2>'; 
    echo '<h3>Generate the title and link, from a products name.</h3>';
    echo '<p>Normal product title : <input type="text" size="40" value="'.$title.'" /></p>';
    echo '<p>My product link will be : <strong>'.(WEB_ROOT.$bseo->seo_magic_link($title,20).EXT).'</strong></p>';
    echo '<p>My product link WITH the ID : <strong>'.(WEB_ROOT.$bseo->seo_magic_link($title,20,$id).EXT).'</strong></p>';
    
    //if you want a special link style, use UNDERSCORES and with "id-name" style 
        $bseo->set_options(array('delimitator'=>'_'));
    echo '<p>My product link WITH the ID in front and underscor delimitator : <strong>'.
                                    (WEB_ROOT.$bseo->seo_magic_link($title,20,$id,true).EXT).'</strong></p>'; 
    
    //If you want a special delimitator on your ID, let's say double hyphen : 
     echo '<p>My product link with double hyphen ID delimitator : <strong>'.
                                    (WEB_ROOT.$bseo->seo_magic_link($title,20,$id,false,'--').EXT).'</strong></p>'; 
                                    
    //text functions : 
    echo '<p>HTML page title : <strong>'.$bseo->seo_magic_text($title,60,true).'</strong></p>';
    echo '<h3>Generate clean, web safe descriptions</h3>';
    echo '<p>Normal description : <textarea cols="50" rows="6">'.$description.'</textarea></p>';
    echo '<p>Clean description (meta tags, xml feeds, etc)  : <textarea cols="50" rows="6">'.$bseo->seo_magic_text($description,0,true).'</textarea></p>';
    echo '<p>Description meta tag (165 chars) : <textarea cols="50" rows="6">'.$bseo->seo_magic_text($description,165,true).'</textarea></p>';
    
    echo '<h3>Get the keywords from the description</h3>';
    echo '<p>Keywords 6 words, normal order : <strong>'.$bseo->seo_magic_keys($description,6,false).'</strong></p>';
    echo '<p>Keywords top 3 words : <strong>'.$bseo->seo_magic_keys($description,3,true).'</strong></p>';
    echo '<p>SEO density calculator top 4 words, in array : <strong>';
                print_r($bseo->seo_magic_keys($description,4,true,true));
                echo '</strong></p>'; 