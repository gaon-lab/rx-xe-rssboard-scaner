<?php
class XMLParser {
    var $parser;
    var $out;
    var $encoding = 'ISO-8859-1';
 
    function XMLParser() {
        $this->_create();
    }
 
    function _create() {
        $this->parser = @xml_parser_create($this->encoding);
 
        if (is_resource($this->parser)) {
            //xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
            xml_set_object($this->parser, $this);
            xml_set_element_handler($this->parser, 'startHandler', 'endHandler');
            xml_set_character_data_handler($this->parser, 'cdataHandler');
            return true;
        }
        return false;
    }
 
    function free() {
        if (is_resource($this->parser)) {
            xml_parser_free($this->parser);
            unset($this->parser);
        }
        return null;
    }
 
    function startHandler($parser, $element, $attr) {
        $this->tag = $element;
    }
 
    function endHandler($parser, $element){
    }
 
    function cdataHandler($parser, $cdata) {
        if (($cdata = trim($cdata)) == '') return;
 
        switch($this->tag) {
                case 'COURT':
                case 'PRONOUNCEDATE':
                case 'PRONOUNCE':
                case 'CASENUM':
                case 'DECISIONTYPE':
                case 'UNDERLINE':
                case 'LawRef':
                case 'SECTIONTITLE':
                    print $cdata;
                    break;
 
                case "CASENAME":
                    print '【'.$cdata.'】';
                    break;
 
                case 'PARA':
                    print '<span style="size: 2pt;">'.$cdata.'</span>';
                    break;
 
                default:
                    break;
        }
    }
 
    function parse($data) {
        xml_parse($this->parser, $data);
        return true;
    }
}
?> 


출처: https://duellist.tistory.com/29 [Return]