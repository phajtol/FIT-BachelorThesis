<?php

namespace App\Helpers;

use Nette;

define('DEBUG', false);

class Parser extends Nette\Object {

    public $lexan;
    public $elem; //lexical element of type LexElem
    public $line_number;
    public $pub_type; //type of publication as an result of parse
    public $fields = array(); //array(bibtex_field_name => bibtex_field_value) a result of parse
    public $fns;

    /**
     * Construct
     *
     * @param string $text text to be parsed
     */
    public function __construct($text) {
        $this->fns = new Functions();
        $this->lexan = new Lexan($text);
        $this->read_step();
    }

    /**
     * Do some conversions to preserve right format of bibtex fields
     *
     * @param array $fields
     * @return array converted fields
     */
    private function convert($fields) {
        $pages = isset($fields['pages']) ? $fields['pages'] : '';
        $fields['pages'] = $pages;
        return $fields;
    }

    /**
     * Entry point of BibTeX parser, results will be passed by refference
     *
     * @param string $pub_type type of publication as an result of parse - out parameter
     * @param array $fields array(bibtex_field_name => bibtex_field_value) a result of parse - out parameter
     * @param array $authors author names array(array(name, middlename, surname), ...) - out parameter
     */
    public function parse(&$pub_type, &$fields, &$authors) {
        $this->bibtex(); //starting grammar rule
        $pub_type = $this->pub_type;
        $fields = $this->convert($this->fields);
        $authors = $this->parse_author_field();
    }

    /**
     * Parse BibTeX field 'author' and produce array of author names.
     *
     * @return array authors in format: array(array(name, middlename, surname), ...)
     */
    private function parse_author_field() {
        $authors = explode(' and ', $this->fields['author']);
        foreach ($authors as $author) {
            $author = trim($author);
            if (strpos($author, ', ') !== false) {//author name in format: 'surname, forename'
                $ns = explode(', ', $author);
                if (count($ns) != 2)
                   // throw new Exception("Author name parse error. Can't parse '$author' because it contains more than one commas.");
                $surname = $ns[0];
                if (strpos($ns[1], ' ') !== false) //if possible try to parse forename into first name and middlename
                    list($name, $middlename) = explode(' ', $ns[1]);
                else {
                    $name = trim($ns[1]);
                    $middlename = '';
                }
            } else { //author name in format: 'forename name'
                if (strpos($author, ' ') !== false) {
                    $ns = explode(' ', $author);
                    if (count($ns) == 2) {
                        $name = $ns[0];
                        $middlename = '';
                        $surname = $ns[1];
                    }
                    if (count($ns) > 2) {
                        $name = $ns[0];
                        $middlename = '';
                        $surname = $ns[count($ns) - 1];
                    }
                } else
                    $surname = $author;
            }
            $result[] = array('name' => $name, 'middlename' => $middlename, 'surname' => $surname);
        }
        return $result;
    }

    /**
     * Reads next lexical element
     *
     */
    private function read_step() {
        $this->elem = $this->lexan->read_symbol();
        $this->line_number = $this->lexan->line_number;
    }

    /**
     * Take-off lexical symbol from the input
     *
     * @param string $lexical_symbol
     * @return string element's syntetic attribute (if defined)
     */
    private function takeoff($lexical_symbol) {
        if ($this->elem->symbol == $lexical_symbol) {
            $attrib = $this->elem->sattribute;
            $this->read_step();
            return $attrib;
        } else {
           // throw new Exception("Line #$this->line_number: Syntax error1. Found " . ($this->elem->sattribute ? "'" . $this->elem->sattribute . "'" : $this->elem->symbol) . ", expected " . $lexical_symbol . ".");
        }
    }

    /**
     * Writes the name and value of BibTeX field to the result set
     *
     * @param string $name
     * @param string $value
     */
    private function decl_bib($name, $value) {
        $this->fields = array_merge($this->fields, array($name => $value));
    }

    /**
     * Rule 1, Bibtex -> pub_type lpar Def rpar
     *
     */
    private function bibtex() {
        if (DEBUG)
            print "in bibtex, " . $this->elem->symbol . "<br />";
        switch ($this->elem->symbol) {
            case 'PUB_TYPE':
                $this->pub_type = $this->takeoff('PUB_TYPE');
                $this->takeoff('LPAR');
                $this->def();
                $this->takeoff('RPAR');
                break;
            default:
               // throw new Exception("Line #$this->line_number: Unexpected token " . $this->elem->symbol . " " . $this->elem->sattribute . ". Expected PUB_TYPE. Try to start definition with @. ");
        }
    }

    /**
     * Rule 2, Def -> Equation RestDef
     * Rule 3, Def -> abb Equation RestDef
     *
     */
    private function def() {
        if (DEBUG)
            print "in def, " . $this->elem->symbol . "<br />";
        switch ($this->elem->symbol) {
            case 'ABB':
                $this->takeoff('ABB');
                $this->takeoff('COMMA');
                $this->equation();
                $this->rest_def();
                break;
            case 'NUMBER':
                $this->takeoff('NUMBER');
                $this->takeoff('COMMA');
                $this->equation();
                $this->rest_def();
                break;
            case 'KEYWORD':
                $this->equation();
                $this->rest_def();
                break;

            // čárka za otevírací závorkou
            case 'COMMA':
                $this->takeoff('COMMA');
                $this->def();
                break;

            default:
               // throw new Exception("Line #$this->line_number: Unexpected token " . $this->elem->symbol . " " . $this->elem->sattribute . ". Expected ABB, NUMBER, KEYWORD or COMMA.");
        }
    }

    /**
     * Rule 4, Equation -> type eq RestEq
     *
     */
    private function equation() {
        if (DEBUG)
            print "in equation, " . $this->elem->symbol . "<br />";
        switch ($this->elem->symbol) {
            case 'KEYWORD':
                $name = $this->takeoff('KEYWORD');
                $this->takeoff('ASSIGN');
                $this->rest_eq($name);
                break;

            // končí-li čárkou a uzavírací závorkou
            case 'EOF': case 'RPAR':
                break;

            // ma se ignorovat neznamy KEYWORD
            case 'ABB':
                $name = $this->takeoff('ABB');
                $this->takeoff('ASSIGN');
                $this->rest_eq($name);
                break;



            default:
                echo ("Line #$this->line_number: Unexpected token " . $this->elem->symbol . " " . $this->elem->sattribute . ". Expected KEYWORD.");
        }
    }

    /**
     * Rule 5, RestDef -> comma Equation RestDef
     * Rule 6, RestDef -> EOF
     *
     */
    private function rest_def() {
        if (DEBUG)
            print "in rest_def, " . $this->elem->symbol . "<br />";
        switch ($this->elem->symbol) {
            case 'COMMA':
                $this->takeoff('COMMA');
                $this->equation();
                $this->rest_def();
                break;
            case 'EOF': case 'RPAR':
                break;
            default:
               // throw new Exception("Line #$this->line_number: Unexpected token " . $this->elem->symbol . " " . $this->elem->sattribute . ". Expected COMMA or RPAR. Perhaps you forgot type comma between definition attributs.");
        }
    }

    /**
     * Rule 7, RestEq -> number DeclBib
     * Rule 8, RestEq -> strval DeclBib
     *
     * @param string $ident_name name of BibTeX field
     */
    private function rest_eq($ident_name) {
        if (DEBUG)
            print "in rest_eq, " . $this->elem->symbol . "<br />";
        switch ($this->elem->symbol) {
            case 'NUMBER':
                $value = $this->takeoff('NUMBER');
                $this->decl_bib($ident_name, $value);
                break;
            case 'STRVAL':
                $value = $this->takeoff('STRVAL');
                $this->decl_bib($ident_name, $value);
                break;
            default:
               // throw new Exception("Line #$this->line_number: Unexpected token " . $this->elem->symbol . " " . $this->elem->sattribute . ". Expected NUMBER or STRVAL.");
        }
    }

}
