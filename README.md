BMagicSeo
=========

BTooLs PHP class for creating friendly links, page titles and meta tags for you website.
 
The functions takes cares of all the SEO problems that you may have while you are creating your own platform or eCommerce shop / blog.
 
## The main feature uses are : 
 * Generate your web-safe and friendly SEO links for your website !
 * Generate a limited list of keywords from a text
 * Create friendly and web safe meta tags for your pages, from your dinamically created content.
 
## This class will : 
 * cut off all double (or multiple) whitespaces, new lines and tabs
 * replace all the *diacritics (from Romanian, French, German, Spanish, Hungarian)* to regular Ascii letters
 * Remove preposition (from your own list)
 * Delete all non alpha-numerics characters, or transform them to html entities !
 * delete all non-ascii (hidden) characters
 * trim the text
 * lowercase your links (and smart lower case the texts, only the first letter from a sentence is uppercase)
 * delete all the slashes (stripslashes)
 * dynamic separator character (usual hyphen)
 * remove all PHP and HTML tags from your text
 * dinamycally limit the characters limit. The text will be *cut at Sentence level (or word)* so the description will still make sense.
 
 
 ### The example file will take you trough all features.
 
 ### Notes
 * The class and methods are very well documented, best used with an auto-complete PHP IDE.
 * The class was made for a custom eCommerce platform
 * new features will be made ONLY if more people requests them