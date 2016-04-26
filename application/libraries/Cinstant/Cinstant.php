<?php 

require_once dirname(__FILE__) . '/HTML5/Parser.php';

class Cinstant
{
    /**
     * The DOMDocument/HTML5 object
     * @var object
     */
    private $doc;
    
    /**
     * Original HTML of the content
     * @var string
     */
    public $html;
    
    /**
     * Converted html to valid instant article
     * @var string
     */
    public $article;
    
    /**
     * The prefix to append to relative url of element.
     * @var string
     */
    public $localHost = '';
    
    /**
     * Constructor
     * @param string html The HTML content.
     * @param array options List of options.
     */
    function __construct($html=null, $options=null){
        if($html || $options)
            $this->convert($html, $options);
    }
    
    /**
     * Convert img parent to be figure
     * @return $this
     */
    private function _convertElParent($element, $figure_class=false){
        $parent = $element->parentNode;
        
        // in case the parent already figure
        if($parent->tagName == 'figure'){
            if($figure_class)
                $parent->setAttribute('class', $figure_class);
            return $this;
        }
        
        $figure = $this->doc->createElement('figure');
        if($figure_class)
            $figure->setAttribute('class', $figure_class);
        
        $replaceParent = true;
        $figcaption_text = [];
        $inline_tags = array(
            '#text',
            'abbr',
            'br',
            'em',
            'span',
            'strong',
            'sub',
            'sup'
        );
        
        if($parent->tagName == 'body'){
            $replaceParent = false;
        }else{
            if($parent->hasChildNodes()){
                $parentChildLength = $parent->childNodes->length;
                
                for($i=0; $i<$parentChildLength; $i++){
                    $maybe_caption = $parent->childNodes->item($i);
                    
                    if(in_array($maybe_caption->nodeName, $inline_tags))
                        $figcaption_text[] = $maybe_caption;
                    elseif($maybe_caption->nodeName == $element->nodeName){
                        continue;
                        
                    }else{
                        $replaceParent = false;
                        break;
                    }
                }
            }
        }
        
        if($replaceParent){
            $figure->appendChild($element);
            if(count($figcaption_text)){
                $figcaption = $this->doc->createElement('figcaption');
                foreach($figcaption_text as $el)
                    $figcaption->appendChild($el);
                $figure->appendChild($figcaption);
            }
            
            $parent->parentNode->replaceChild($figure, $parent);
        }else{
            $parent->insertBefore($figure, $element);
            $figure->appendChild($element);
        }
        
        return $this;
    }
    
    /**
     * Convert img tag 
     * @return $this
     */
    private function _convertImg(){
        $imgs = $this->doc->getElementsByTagName('img');
        if(!$imgs->length)
            return $this;
        
        for($i=0; $i<$imgs->length; $i++){
            $img = $imgs->item($i);
            
            // make the image to be absolute url
            $src = $img->getAttribute('src');
            if(substr($src,0,4) != 'http'){
                $src = rtrim($this->localHost, '/') . '/' . ltrim($src, '/');
                $img->setAttribute('src', $src);
            }
            
            // convert parent to figure
            if($img->parentNode->tagName != 'figure')
                $this->_convertElParent($img);
        }
        
        return $this;
    }
    
    /**
     * Convert iframe tag 
     * @return $this
     */
    private function _convertIframe(){
        $iframes = $this->doc->getElementsByTagName('iframe');
        if(!$iframes->length)
            return $this;
        
        for($i=0; $i<$iframes->length; $i++){
            $iframe = $iframes->item($i);
            $parentClass = 'op-interactive';
            
            // make the image to be absolute url
            $src = $iframe->getAttribute('src');
            if(substr($src,0,4) != 'http'){
                $src = rtrim($this->localHost, '/') . '/' . ltrim($src, '/');
                $iframe->setAttribute('src', $src);
            }elseif(preg_match('!facebook|instagram|twitter|vine|youtu!', $src)){
                $parentClass = 'op-social';
            }
            
            // convert parent to figure
            $this->_convertElParent($iframe, $parentClass);
        }
        
        return $this;
    }
    
    /**
     * Convert div tag 
     * @return $this
     */
    private function _convertDiv(){
        $divs = $this->doc->getElementsByTagName('div');
        if(!$divs->length)
            return $this;
        
        for($i=0; $i<$divs->length; $i++){
            $div = $divs->item($i);
            
            // convert parent to figure
            if($div->getAttribute('class') == 'fb-video'){
                $iframe = $this->doc->createElement('iframe');
                $div->parentNode->insertBefore($iframe, $div);
                
                // we also need to append fb script code
                $iframe->appendChild($div);
                
                $script = $this->doc->createElement('script');
                $scriptValue = $this->doc->createTextNode('(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0];  if (d.getElementById(id)) return;  js = d.createElement(s); js.id = id;  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";  fjs.parentNode.insertBefore(js, fjs);}(document, \'script\', \'facebook-jssdk\'));');
                $script->appendChild($scriptValue);
                
                $iframe->appendChild($script);
                
                $this->_convertElParent($iframe, 'op-social');
            }
        }
        
        return $this;
    }
    
    /**
     * Parse the HTML text.
     * @param string html The html content to convert.
     * @param array options List of convertion 
     * @return $this
     */
    public function convert($html=null, $options=null){
        if($options)
            $this->setOptions($options);
        if($html)
            $this->html = $html;
        
        if(!$this->html)
            return $this;
            
        $html = '<!DOCTYPE html><html><body>' . $this->html . '</body></html>';
        $this->doc = HTML5_Parser::parse($html);
        
        $this
            ->_convertImg()
            ->_convertIframe()
            ->_convertDiv();
        
        $cimp = $this->doc->saveHTML();
        preg_match('!^.+<body>(.+)</body>.+$!s', $cimp, $m);
        $this->article = $m[1];
        
        // clean empty html
        $noempty_tags = ['p'];
        foreach($noempty_tags as $tag)
            $this->article = preg_replace('!<' . $tag . '> *<\/' . $tag . '>!', '', $this->article);
        
        return $this;
    }
    
    /**
     * Set option(s)
     * @param string|array key The options key or list of option-value pair
     * @param mixed value The option value, only if $key is string
     * @return $this
     */
    public function setOptions($key, $value=null){
        if(!is_array($key))
            $key = array($key=>$value);
        
        foreach($key as $name => $value)
            $this->$name = $value;
    }
}