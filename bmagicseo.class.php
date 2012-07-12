<?php  
/**
 * BMagicSeo
 ********************************************************************************
 * 
 *   BTooLs class for creating friendly links, page titles and meta tags for you website.   
 * The functions takes cares of all the SEO problems that you may have : 
 * 
 *  The main features are : 
 *  - Generate your web-safe and friendly SEO links for your website !
 *  - Generate a limited list of keywords from a text
 *  - Create friendly and web safe meta tags for your pages, from your dinamically created content.
 * 
 *  This class will : 
 *  - cut off all double (or multiple) whitespaces, new lines and tabs
 *  - replace all the diacritics (from Romanian, French, German, Spanish, Hungarian) to regular Ascii letters
 *  - Remove preposition (from your own list)
 *  - Delete all non alpha-numerics characters, or transform them to html entities !
 *  - delete all non-ascii (hidden) characters
 *  - trim the text
 *  - lowercase your links (and smart lower case the texts, only the first letter from a sentence is uppercase)
 *  - delete all the slashes (stripslashes)
 *  - dynamic separator character (usual hyphen)
 *  - remove all PHP and HTML tags from your text
 *  - dinamycally limit the characters limit. The text will be cut at Sentence level (or word) so the description will still make sense.
 *  
 *  You can use BMagicSeo in 2 modes : 
 *  - dinamically : when you create an internal link use the function "on spot" 
 *  - stored : when you add/modify a page (product etc) store the function result somewhere in you DB.

 * @example $magic = new BMagicSeo();
 * 
 ********************************************************************************
 * @package    BTooLs
 * @copyright  Copyright (c) 2007 - 2012+, Georgescu Adrian (B3aT)
 * @license    http://www.gnu.org/licenses/gpl.txt     GPL 
 * @author     B. Georgescu Adrian (B3aT) <btools@gmail.com>
 * @version    1 2011-07-12 
 * @link       http://btools.eu/ 
 * @source	   https://github.com/BTooLs/BMagicSeo	
 */
class BMagicSeo{
    
    /** Array of prepositions and words that will be replaced from text (single letter words will be automatically replaced.) */
    private $prop ;
    
    /** Word delimitator used in links, usually hyphen or underscore. **/
    private $del = '-';    
    
    /** Class options */
    private $options = array(
    /** Trim the whitespaces from start and end of the text. (Recommended)*/
    'trim' => TRUE,
    /** Transform the text to lowercase. (Recommended). The link will completely be transformed to lowercase, 
    but the text will be "This style.", as in only the First letter uppercase, per sentence.*/
    'lower' => TRUE,
    /** Delete the words defined in prop array */
    'prep' => TRUE,
    /** Remove all non alpha numeric caracters from the text. If is false, in _link the caracters will be url-encoded, and in _text and _keys functions will
    * be transformed to html entities.*/
    'special' => TRUE ,
    /** Remove all the PHP and HTML tags (even images and JScripts). Keep the important (SEO) text clean and safe. (Recommended)*/
    'tags' => TRUE ,
    /** Deletes the double (and multiple) spaces and tabs. (Recommended)
    This option is Ignored in seo_magic_link, (it always delete them).*/
    'spaces' => TRUE,
    /** Replace the diacritics from text to regular ASCII letters. */
    'diacritics' => TRUE
    );
    
    
   /** 
   *  Change the default class options.
   * 
   * @param array $new_options Associative array with any of class options.
   * @example set_options(array('trim'=>false,'tags'=>false,
   * 'prop'=>array ( 'de la','cu','fara','in','intru','la','pe', 'de','pe la',
                            'de pe la','sub','sau','ori','orice','oricum','si', 'peste', 'printre', 'prin', 'dupa',
                            'din','de la','pana la', 'dintre', 'pentru')));
   */
   public function set_options($new_options=false)
   {
       if (isset($new_options['trim']))
            $this->options['trim'] = (bool)$new_options['trim'];
        if (isset($new_options['lower']))
            $this->options['lower'] = (bool)$new_options['lower'];
        if (isset($new_options['prep']))
            $this->options['prep'] = (bool)$new_options['prep'];
        if (isset($new_options['special']))
            $this->options['special'] = (bool)$new_options['special'];  
        if (isset($new_options['tags']))
            $this->options['tags'] = (bool)$new_options['tags'];
        if (isset($new_options['delimitator']))
            $this->del = (string)$new_options['delimitator'];
        if(isset($new_options['prop']))
            {
                $this->prop = $new_options['prop'];
        
        /** We need to tweak a bit the preposition list, to make them patterns. */
        foreach ($this->prop AS &$w)
            $w = '/\b'.$w.'\b/i';// /ei 
            }
   }
  
  /**
  * Create a friendly and safe link from your content.
  * 
  * @param string $str The string from which you want to create the link, usually the product/article Name. DON'T insert your web root here !
  * @param int/false $limit  Limit the link character count (usually a full URL is smaller then 64 characters - your address + category + 3-5 words)
  * @param int/false $link_id The unique identifier for you link (ex product ID)
  * @param bool True if you want your ID at the start of the link, otherwise false.
  * @param string/false False or a string to delimit the ID from the rest of the link.
  * @return string A link created from the text given, safe and friendly.   
  * @example seo_magic_link('<h1>My &New #25 inches product!</h1>',30); //will result : "my-new-25-inches-product"
  * 
  */
  public function seo_magic_link($str,$limit = false,$link_id=false,$id_at_start=false,$special_id_del=false)
    {
         //make sure the string ..is a string
         if (!is_string($str))
                return FALSE;
        //other data verifications    
        $limit = (int)$limit;   
        $link_id = (int)$link_id;
        $id_at_start = (bool)$id_at_start;
        if ($special_id_del === false)
                $special_id_del = $this->del;
            
        //if the limit is very wrong ..
        if ($limit < 1 OR $limit > 2000) //2000 is smallest browser limitation of a URI  (in IE)
            return FALSE;     
        
        //let's make sure there aren't any slashes left, esspecially from DB extracted data
        $str = stripslashes($str);
        
        //first get rid of nasty tags
        if ($this->options['tags'])
                $str = strip_tags($str);
        
        //diacritics are no good in links
        $str = $this->replace_diacritics($str);

        //remove the new line, tab and non-ascii characters
         $str = str_replace(array("\n","\t","\r"),'',$str);
         $str = preg_replace('/[^(\x20-\x7F)]*/','', $str);  
       
      
       //lower case
       if ($this->options['lower']) $str = strtolower($str); 
       
       //prepositions and one letter words
       if ($this->options['prep']) 
        {
            //$str = str_ireplace($this->prop,'',$str);
            $str = preg_replace($this->prop,'',$str);
            
            $str = preg_replace("/\b[a-zA-Z]\b/i",'',$str); //for all 2 characters words add {2} after ]
        }
        
       //deal with special characters (non alpha numerics and spaces)
       if ($this->options['special'])
            {
                $str = preg_replace("/[^a-zA-Z0-9\s]/",'',$str);//"/[^a-zA-Z0-9]/"
                //multiple spaces and tabs
                $str = preg_replace("/\s+/", ' ',$str);
            }   
       
       //after replaces we could have multiple spaces at start and end
       if ($this->options['trim']) $str = trim($str); 
       
       
       //cut at limit, split by words (not characters)
       if ($limit AND strlen($str) > $limit)
        {  
            preg_match('/(.{' . $limit . '}.*?)\b/', $str, $matches);
            $str = rtrim($matches[1]);
        }       
       
       //replace space with delimitator
       $str = str_replace(' ',$this->del,$str);
       
       //add the link ID, if it's given
       if ($link_id)
       {
           if ($id_at_start)
                $str = $link_id.$special_id_del.$str;
           else
                $str .= $special_id_del.$link_id;
       }
       
       //if special characters will be in link, at least make them valid url
       if (!$this->options['special']) 
                $str = urlencode($str);
                
       //return the result
       return $str;
    }  
          
    /**
    * Transform your data in friendly and safe HTML and XML text, for meta tags and other strictly elements like feeds.
    *  
    * @param string $str The text to work with, example Product description.
    * @param int/false $limit  False or a number to limit the number of characters. Meta description must be under 160 chars, and title 60. The cut will be made at sentence level !
    * @param bool $lower Overwrite the class setting, lowercase the text, except the first letter of the sentence.
    * @return string
    */
    public function seo_magic_text($str,$limit=false,$lower=false)
    {
        
         //daca textul nu este de tip string
         if (!is_string($str))
                return FALSE;
            
        $limit = (int)$limit;        
    
        //let's make sure there aren't any slashes left, esspecially from DB extracted data        
        $str = stripslashes($str);
        
        //nasty unwanted tags
        if ($this->options['tags'])
                $str = strip_tags($str);
        
        //diacritics 
        $str = $this->replace_diacritics($str);

        //remove the new line, tab and non-ascii characters  
        $str = str_replace(array("\n","\t","\r"),'',$str);
        $str = preg_replace('/[^(\x20-\x7F)]*/','', $str);  
       
      
        //lower case
        if ($lower) $str = ucfirst($str); 
        //TO DO : sa se imparta textul in propozitii, aplicata functia pe ele, si apoi reunite in string.
       
       //se elimina si spatiile multiple si tab
       $str = preg_replace("/\s+/", ' ',$str);
                    
     
       //limit the text to max characters, and cut by sentences, or by words if is only one big sentence.
       if ($limit AND strlen($str) > $limit)
        {  
           //try to split by sentence
           preg_match('/(.{0,' .($limit - 1 ). '}[\.\!\?].*?)/', $str, $matches); //\b.*   +[.!?] [\.\!\?]
           $temp = rtrim($matches[1]);             
           
           
           if (strlen($temp) > $limit OR strlen($temp) == 0 OR empty($temp))
            {
                /** to do : make it work
                 preg_match('/(.{' . $limit . '}.*?)/', $str, $matches); //'/(.{' . $limit . '}.*?)\,/'
                 $temp = rtrim($matches[1]); 
                */
                //split by words
                if (strlen($temp) > $limit OR strlen($temp) == 0 OR empty($temp))
                {
                     preg_match('/(.{0,' . $limit . '}\b.*?)/', $str, $matches);
                     $temp = rtrim($matches[1]);
                }
                
            }
           //if the text is still too big, cut by charcter (when is only a big text w/o spaces and sentences, like codes)
            if (strlen($temp) > $limit)
                    $temp = substr($temp,0,$limit);

           
           if (!empty($temp)) 
                    $str = $temp;
           else //if something is wrong, and the text is empty ... return limit by char
                    $str = substr($str,0,$limit);
           }   
                
           //trimming
           if ($this->options['trim']) 
                $str = trim($str); 
       
      //done
      return $str;    
    }
    
          
     /**
     * Create a standard keywords list, usually for meta keyword tag, from a specific text. The function takes cares of extra characters,
     * one/two letter or standard preposition words and other unwanted text.                                         
     * @param string $str The text from which we'll retrive the words.
     * @param int $limit_words Return only the first x found words.
     * @param bool $order_by_density True : return only unique words, high density firsts. False : return the words in appearence order.
     * @param bool $return_array Only works if  $order_by_density is true. 
     * @return string A string with the keywords, separated by comma. The order of words is the appeareance order from the text.
     * If both $order_by_density and  $return_array params are true, the result is an asociative array('test'=>array('word'=>'test','count'=>3,'percent'=>2.4%)).
     */
    public function seo_magic_keys($str,$limit_words = 20,$order_by_density=true,$return_array=false)
    {
        $return = '';
        
        $str = $this->seo_magic_text($str,false,TRUE);//deal with diacritics, tags,slashes,uppercase
        
        $limit_words = (int)$limit_words;
        if ($limit_words < 1)
                return FALSE;
        $order_by_density = (bool)$order_by_density;
        $return_array = (bool)$return_array;
        
        //prepositions, one and two letter words ..are no good for keywords
       if ($this->options['prep']) 
        {
            //$str = str_ireplace($this->prop,'',$str);
            $str = preg_replace($this->prop,'',$str);
            $str = preg_replace("/\b[a-zA-Z]\b/i",'',$str);
            $str = preg_replace("/\b[a-zA-Z]{2}\b/i",'',$str);
        }
        
       //all non alpha numerics characters
       if ($this->options['special'])
        {
            $str = preg_replace("/[^a-zA-Z0-9\s]/",'',$str);//"/[^a-zA-Z0-9]/"
            //multiple spaces and tabs
            $str = preg_replace("/\s+/", ' ',$str);
        }
                        
       //if we need the words, in density order ...
       if ($order_by_density)
        {
              //let's find all the words
         $words_array = str_word_count($str, 1);
         $total_words = sizeof($words_array);   
         
         //we need the unique ones
         $unique_words_array = array_unique($words_array);
         
         //calculate the density and appereances   
         $popularity = array();
         foreach($unique_words_array as $key => $word)
        {
                preg_match_all('/\b'.$word.'\b/i', $str, $out);
 
                $count = count($out[0]);
 
                $percent = number_format((($count * 100) / $total_words), 2); 
 
                $popularity[$word]['word'] = $word;
                $popularity[$word]['count'] = (int)$count;
                $popularity[$word]['percent'] = (float)$percent;
        }
        
       
        uasort($popularity, "cmp");
        
            //make the string result, if necessary 
            if ($return_array === false)
                {
                    $count = 0;
                    $k = 0;
                    foreach ($popularity AS $val=>$details)
                        {
                        	$k++;
                            
                            if (!empty($val) AND ctype_alnum($val))
                                {
                                    if ($limit_words === false) //if the limit is disabled 
                                            $return .= strtolower($val).', ';
                                    elseif($count <= $limit_words) //if theres a limit ..
                                        {
                                            ++$count;
                                            $return .= strtolower($val).', ';  
                                        }
                                }
                        }
                   	//remove the last comma
                   	if (strlen($return) > 3) $return = substr($return,0,count($return) - 3);//3 = strlen(', ') + 1
                     return $return; 
                }

        //return the array result
        if ($limit_words)  
            {   
                return array_slice($popularity,0,$limit_words,true);   
            }
        else
            {                   
                return $popularity;  
            }  
            
        }
        else
        {
            //get all the words, in appearence order
             preg_match_all('/(\b[a-z]{0,40}\b.*?)/i', $str, $matches,PREG_PATTERN_ORDER); //[\b\.\,\!\?\:]

             $count = 0;
             foreach ($matches[0] AS $k=>$val)
             //for SEO purpose, get only the long words, that are alpha numeric
            if (!empty($val) AND ctype_alnum($val) AND $count <= $limit_words )     
                {
                    ++$count;
                    $return .= strtolower($val).(($k<>count($matches)-1)?', ':'');
                }
             //done
             return $return;
        }   
        
       
    }      
    
    
    /**
    * Replace the known diacritics with regular ASCII characters.
    * If you want to extend the function with other languages, please email me at btools@gmail.com
    * @param string $str
    * @return string The string w/o diacritics.
    */
    private function replace_diacritics($str)
    {
         
        // RO	 'A', 'a', 'Â', 'â', 'Î', 'î', 'S', 's', 'T', 't',	 'S', 's', 'T', 't' 
        $ro_in = array("\xC4\x82", "\xC4\x83", "\xC3\x82", "\xC3\xA2", "\xC3\x8E", "\xC3\xAE", "\xC8\x98", "\xC8\x99",
        "\xC8\x9A", "\xC8\x9B", "\xC5\x9E", "\xC5\x9F", "\xC5\xA2", "\xC5\xA3"); 
        $ro_out = array('A', 'a', 'A', 'a', 'I', 'i', 'S', 's', 'T', 't', 'S', 's', 'T', 't'); 
        $str = str_replace($ro_in,$ro_out,$str);
        
        // FR	 À	 à	 Â	 â	 Æ	 æ	 È	 è	 É	 é	 Ê	 ê	 Ë	 ë	 Î	 î	 Ï	 ï	 Ô	 ô	 Œ	 œ	 Ù	 ù	 Û	 û	 Ü	 ü	 Ÿ	 ÿ	 Ç	 ç 
        $fr_in = array("\xC3\x80", "\xC3\xA0", "\xC3\x82", "\xC3\xA2", "\xC3\x86", "\xC3\xA6", "\xC3\x88",
         "\xC3\xA8", "\xC3\x89", "\xC3\xA9", "\xC3\x8A", "\xC3\xAA", "\xC3\x8B", "\xC3\xAB", "\xC3\x8E", "\xC3\xAE",
          "\xC3\x8F", "\xC3\xAF", "\xC3\x94", "\xC3\xB4", "\xC5\x92", "\xC5\x93", "\xC3\x99", "\xC3\xB9", "\xC3\x9B", 
          "\xC3\xBB", "\xC3\x9C", "\xC3\xBC", "\xC5\xB8", "\xC3\xBF", "\xC3\x87", "\xC3\xA7"); 
        $fr_out = array('A',	 'a',	 'A', 'a', 'Ae', 'ae',	 'E',	 'e',	 'E',	 'e',	 'E',	 'e',	 'E',	 'e',	 'I',	 'i',
        	 'I',	 'i',	 'O',	 'o',	 'Oe',	 'oe',	 'U',	 'u',	 'U',	 'u',	 'U',	 'u',	 'Y',	 'y',	 'C',	 'c'); 
        $str = str_replace($fr_in,$fr_out,$str);
        
        // HU	 Á	 á	 É	 é	 Í	 í	 Ó	 ó	 Ö	 ö	 O	 o	 Ú	 ú	 Ü	 ü	 U	 u 
        $hu_in	= array("\xC3\x81", "\xC3\xA1", "\xC3\x89", "\xC3\xA9", "\xC3\x8D", "\xC3\xAD", "\xC3\x93", "\xC3\xB3", "\xC3\x96", "\xC3\xB6", "\xC5\x90", "\xC5\x91", "\xC3\x9A", "\xC3\xBA", "\xC3\x9C", "\xC3\xBC", "\xC5\xB0", "\xC5\xB1"); 
        $hu_out	= array('A',	 'a',	 'E',	 'e',	 'I',	 'i',	 'O',	 'o',	 'O',	 'o',	 'O',	 'o',	 'U',	 'u',	 'U',	 'u',	 'U',	 'u'); 
         $str = str_replace($hu_in,$hu_out,$str);
      
        // DE	 Ä	 ä	 Ö	 ö	 Ü	 ü	 ß 
        $de_in = array("\xC3\x84", "\xC3\xA4", "\xC3\x96", "\xC3\xB6", "\xC3\x9C", "\xC3\xBC", "\xC3\x9F"); 
        $de_out = array('Ae',	 'ae',	 'Oe', 'oe',	 'Ue',	 'ue',	 'ss'); 
        $str = str_replace($de_in,$de_out,$str);
      
        // ES Á á É é Í í Ó ó Ú ú Ñ ñ Ü ü 
        $es_in = array("\xC3\x81", "\xC3\xA1", "\xC3\x89", "\xC3\xA9", "\xC3\x8D", "\xC3\xAD", "\xC3\x93", "\xC3\xB3", "\xC3\x9A", "\xC3\xBA", "\xC3\x91", "\xC3\xB1", "\xC3\x9C", "\xC3\xBC"); 
        $es_out = array('A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u', 'N', 'n', 'U', 'u');	
        $str = str_replace($es_in,$es_out,$str);
      
        return $str;
    }
    
}

        //sort by number of appereances
        function cmp($a, $b)
        {
                return ($a['count'] < $b['count']) ? +1 : -1;
        }
        
return;