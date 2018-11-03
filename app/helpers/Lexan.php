<?php

namespace App\Helpers;

use Nette;

class Lexan {

    use Nette\SmartObject;

    public $text;
    public $symbol; //lexical symbol
    private $input_symbol; //imput symbol
    private $input_char;
    private $pos = 0; // position in text
    public $line_number = 1;
    public $fns;
    public $error;

    /**
     * Constructor - prepares input text
     *
     * @param string $text BibTeX definition
     */
    public function __construct($text) {
        $this->error = 0;
        $this->fns = new Functions();
        $this->text = $text;
        $lp = strpos($this->text, '{');
        $rp = strrpos($this->text, '}');
        if (!$lp || !$rp) {
            $this->error = 1;
        } else {
            // throw new Exception('There are no {} brackets');
            $this->text[$lp] = '(';
            $this->text[$rp] = ')'; //replace with rounded brackets

            $this->read_input();
        }
    }

    /**
     * Read input
     *
     */
    private function read_input() {
        if ($this->pos < strlen($this->text))
            $this->input_char = $this->text[$this->pos++];
        else
            $this->input_char = null;

        if ($this->input_char >= 'a' && $this->input_char <= 'z' || $this->input_char >= 'A' && $this->input_char <= 'Z')
            $this->input_symbol = 'c'; //char
        elseif ($this->input_char >= '0' && $this->input_char <= '9')
            $this->input_symbol = 'n'; //number
        elseif ($this->input_char == null)
            $this->input_symbol = 'e'; //end of file
        elseif ($this->input_char == "\n")
            $this->line_number++; //increase line counter
        elseif ($this->input_char <= ' ')
            $this->input_symbol = 's'; //white chars
        else
            $this->input_symbol = $this->input_char;
    }

    public function is_error() {
        return $this->error;
    }

    /**
     * Read symbol
     *
     * @return lexical element
     */
    public function read_symbol() {
        $strval = '';
        while ($this->input_symbol == 's')
            $this->read_input();
        switch ($this->input_symbol) {
            case 'e':
                $this->symbol = 'EOF';
                return new LexElem($this->symbol, null);
            case '@':
                $this->symbol = 'PUB_TYPE';
                $pub_type = '';
                $this->read_input();
                while ($this->input_symbol == 'c') {
                    $pub_type .= $this->input_char;
                    $this->read_input();
                }
                $pub_type = strtolower($pub_type);
                if (!in_array($pub_type, $this->fns->pub_types())) {
                    // throw new Exception("Publication type `$pub_type` is not defined.");
                    $this->error = 1;
                }
                return new LexElem($this->symbol, $pub_type);
            case 'c':
                $str = $this->input_char;
                $this->read_input();
                while ($this->input_symbol != 's' && $this->input_symbol != '=' && $this->input_symbol != ',') {
                    $str .= $this->input_char;
                    $this->read_input();
                }
                if (in_array(strtolower($str), $this->fns->bibtex_fields()))
                    $this->symbol = 'KEYWORD';
                else
                    $this->symbol = 'ABB';
                return new LexElem($this->symbol, $str);
            case 'n':
                $number = $this->input_char;
                $this->read_input();
                while ($this->input_symbol == 'n') {
                    $number .= $this->input_char;
                    $this->read_input();
                }
                $this->symbol = 'NUMBER';
                return new LexElem($this->symbol, $number);
            case '"':
                $this->symbol = 'STRVAL';
                $this->read_input();
                while ($this->input_symbol != '"') {
                    if ($this->input_char == '{') { //curly bracket
                        $lpar_count = 1;
                        $rpar_count = 0;
                        $this->symbol = 'STRVAL';
                        while ($lpar_count != $rpar_count) {
                            $this->read_input();
                            if ($this->input_char == '{')
                                $lpar_count++;
                            elseif ($this->input_char == '}')
                                $rpar_count++;
                            else
                                $strval .= $this->input_char; //if none of '{' or ')' was read, we can consider this char
                        }
                    } else
                        $strval .= $this->input_char;
                    $this->read_input();
                }
                $this->read_input();
                return new LexElem($this->symbol, $strval);
            case '{': //curly bracket
                $lpar_count = 1;
                $rpar_count = 0;
                $this->symbol = 'STRVAL';
                while ($lpar_count != $rpar_count) {
                    $this->read_input();
                    if ($this->input_char == '{')
                        $lpar_count++;
                    elseif ($this->input_char == '}')
                        $rpar_count++;
                    else
                        $strval .= $this->input_char; //if none of '{' or ')' was read, we can consider this char
                }
                $this->read_input();
                return new LexElem($this->symbol, $strval);
            case '(':
                $this->symbol = 'LPAR';
                $this->read_input();
                return new LexElem($this->symbol, null);
            case ')':
                $this->symbol = 'RPAR';
                $this->read_input();
                return new LexElem($this->symbol, null);
            case ',':
                $this->symbol = 'COMMA';
                $this->read_input();
                return new LexElem($this->symbol, null);
            case '=':
                $this->symbol = 'ASSIGN';
                $this->read_input();
                return new LexElem($this->symbol, null);
            default:
                $this->error = 1;
            // throw new Exception("Line #" . $this->line_number . ":Unexpected input char `$this->input_char`");
        }
    }

}
