<?php

namespace App\Helpers;

use Nette;

class EndNoteRefWorksParser extends Nette\Object {

  public $inputText;
  public $defType;
  public $parsedItems;
  public $lastItem;

  /**
   * Construct
   *
   * @param string $text text to be parsed
   */
  public function __construct($inputText, $defType) {
    $this->inputText = $inputText;
    $this->defType = $defType;
    $this->parsedItems = array();
    $this->lastItem = '';
  }

  public function readLines() {
    foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->inputText) as $line) {
      $this->parseLine($line, true);
    }

    foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->inputText) as $line) {
      $this->parseLine($line, false);
    }
  }

  public function parseLine($line, $pubTypeSearch) {
    $trimmedLine = trim($line);
    $firstTwoChars = substr($trimmedLine, 0, 2);
    $restChars = substr($trimmedLine, 2);
    if ($pubTypeSearch) {
      $this->findPubType($firstTwoChars, trim($restChars));
    }
    else {
      $this->findTags($firstTwoChars, trim($restChars));
    }
  }

  public function findPubType($firstTwoChars, $restChars) {
    if ((preg_match('/^%.$/', $firstTwoChars) && $firstTwoChars == "%0") || (preg_match('/[A-Z]{1,}[A-Z0-9]{1,}/', $firstTwoChars) && $firstTwoChars == "RT")) {
      $pubType = $this->matchPubType($restChars);
      $this->parse('pub_type', $pubType);
    }
  }

  public function matchPubType($restChars) {
    $pubType = '';
    $restChars = strtolower($restChars);
    if ($this->defType == "endnote") {
      switch (true) {
        case strstr($restChars, "unused"):
          $pubType = 'misc';
          break;
        case strstr($restChars, "book"):
          $pubType = 'book';
          break;
        case (strstr($restChars, "journal") && strstr($restChars, "article")):
          $pubType = 'article';
          break;
        case (strstr($restChars, "conference") && strstr($restChars, "paper")):
          $pubType = 'inproceedings';
          break;
        case (strstr($restChars, "conference") && strstr($restChars, "proceedings")):
          $pubType = 'proceedings';
          break;
        case (strstr($restChars, "book") && strstr($restChars, "section")):
          $pubType = 'incollection';
          break;
        case (strstr($restChars, "book") && strstr($restChars, "section")):
          $pubType = 'inbook';
          break;
        case strstr($restChars, "pamphlet"):
          $pubType = 'booklet';
          break;
        case strstr($restChars, "manuscript"):
          $pubType = 'manual';
          break;
        case strstr($restChars, "report"):
          $pubType = 'techreport';
          break;
        case strstr($restChars, "thesis"):
          $pubType = 'mastersthesis';
          break;
        case strstr($restChars, "thesis"):
          $pubType = 'phdthesis';
          break;
        case (strstr($restChars, "unpublished") && strstr($restChars, "work")):
          $pubType = 'unpublished';
          break;
        default:
          $pubType = 'misc';
          break;
      }
    }
    elseif ($this->defType == "refworks") {

      switch (true) {
        case strstr($restChars, "unknown"):
          $pubType = 'misc';
          break;
        case (strstr($restChars, "book") && strstr($restChars, "whole")):
          $pubType = 'book';
          break;
        case (strstr($restChars, "journal") && strstr($restChars, "article")):
          $pubType = 'article';
          break;
        case (strstr($restChars, "conference") && strstr($restChars, "proceedings")):
          $pubType = 'inproceedings';
          break;
        case (strstr($restChars, "conference") && strstr($restChars, "proceedings")):
          $pubType = 'proceedings';
          break;
        case (strstr($restChars, "book") && strstr($restChars, "section")):
          $pubType = 'incollection';
          break;
        case (strstr($restChars, "book") && strstr($restChars, "section")):
          $pubType = 'inbook';
          break;
        case (strstr($restChars, "book") && strstr($restChars, "whole")):
          $pubType = 'booklet';
          break;
        case strstr($restChars, "unknown"):
          $pubType = 'manual';
          break;
        case strstr($restChars, "report"):
          $pubType = 'techreport';
          break;
        case strstr($restChars, "thesis"):
          $pubType = 'mastersthesis';
          break;
        case strstr($restChars, "thesis"):
          $pubType = 'phdthesis';
          break;
        case (strstr($restChars, "unpublished") && strstr($restChars, "material")):
          $pubType = 'unpublished';
          break;
        default:
          $pubType = 'misc';
          break;
      }
    }
    return $pubType;
  }

  public function findTags($firstTwoChars, $restChars) {
//if ((preg_match('/^%.$/', $firstTwoChars) && $firstTwoChars == "%0") || (preg_match('[A-Z][A-Z0-9]', $firstTwoChars) && $firstTwoChars == "RT"))
    if (preg_match('/^%.$/', $firstTwoChars) || (preg_match('/[A-Z]{1,}[A-Z0-9]{1,}/', $firstTwoChars))) {

      if ($this->defType == "endnote") {
        switch ($firstTwoChars) {
          // Author
          case '%A':
            $this->parse('authors', $restChars);
            break;

          // Secondary Title (of a Book or Conference Name)
          case '%B':
            if ((isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "inproceedings") || (isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "proceedings")) {
              // $this->parse('conference2.name', $restChars);
              $this->parse('booktitle', $restChars);
            }
            else {
              $this->parse('booktitle', $restChars);
            }
            break;

          // Place Published
          case '%C':
            $this->parse('address', $restChars);
            break;

          // Year
          case '%D':
            $this->parse('year', $restChars);
            break;

          // Editor /Secondary Author
          case '%E':
            $this->parse('editor', $restChars);
            break;

          // Publisher
          case '%I':
            $this->parse('publisher', $restChars);
            break;

          // Secondary Title (Journal Name)
          case '%J':
            if (isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "article") {
              $this->parse('journal', $restChars);
            }
            else {
              $this->parse('booktitle', $restChars);
            }
            break;

          // Number (Issue)
          case '%N':
            $this->parse('number', $restChars);
            break;

          // Pages
          case '%P':
            $this->parse('pages', $restChars);
            break;

          // Title
          case '%T':
            $this->parse('title', $restChars);
            break;

          // URL
          case '%U':
            $this->parse('url', $restChars);
            break;

          // Volume
          case '%V':
            $this->parse('volume', $restChars);
            break;

          // Abstract
          case '%X':
            $this->parse('abstract', $restChars);
            break;

          // Notes
          case '%Z':
            $this->parse('notes', $restChars);
            break;

          // Reference Type
          case '%0':
            // $this->parse('pub_type', $restChars);
            break;

          // Edition
          case '%7':
            $this->parse('edition', $restChars);
            break;

          // Date
          case '%8':
            $this->parse('issue_date', $restChars);
            break;

          // Type
          case '%9':
            $this->parse('type_of_report', $restChars);
            break;

          /*
            // Subsidiary Author
            case '%?':
            $this->parse('authors', $restChars);
            break;
           */

          // ISBN/ISSN
          case '%@':
            if (isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "article") {
              $this->parse('issn', $restChars);
            }
            else {
              $this->parse('isbn', $restChars);
            }
            break;

          default:
            break;
        }
      }
      elseif ($this->defType == "refworks") {
        switch ($firstTwoChars) {
          // Author
          case 'A1':
            $this->parse('authors', $restChars);
            break;

          // Secondary Title (of a Book or Conference Name)
          case 'T2':
            if ((isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "inproceedings") || (isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "proceedings")) {
              // $this->parse('conference2.name', $restChars);
              $this->parse('booktitle', $restChars);
            }
            elseif (isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "article") {
              $this->parse('journal', $restChars);
            }
            else {
              $this->parse('booktitle', $restChars);
            }
            break;

          // Place Published
          case 'PP':
            $this->parse('address', $restChars);
            break;

          // Year
          case 'YR':
            $this->parse('year', $restChars);
            break;

          // Editor /Secondary Author
          case 'A2':
            $this->parse('editor', $restChars);
            break;

          // Publisher
          case 'PB':
            $this->parse('publisher', $restChars);
            break;

          // Secondary Title (Journal Name)
          /*
            case '%J':
            if (isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "article") {
            $this->parse('journal', $restChars);
            }
            else {
            $this->parse('booktitle', $restChars);
            }
            break;
           */
          // Number (Issue)
          case 'AN':
            $this->parse('number', $restChars);
            break;

          // Pages start
          case 'SP':
            $this->parse('pages_start', $restChars);
            break;

          // Pages end
          case 'OP':
            $this->parse('pages_end', $restChars);
            break;

          // Title
          case 'T1':
            $this->parse('title', $restChars);
            break;

          // URL
          case 'UL':
            $this->parse('url', $restChars);
            break;

          // Volume
          case 'VO':
            $this->parse('volume', $restChars);
            break;

          // Abstract
          case 'AB':
            $this->parse('abstract', $restChars);
            break;

          // Notes
          case 'NO':
            $this->parse('notes', $restChars);
            break;

          // Reference Type
          case 'RT':
            // $this->parse('pub_type', $restChars);
            break;

          // Edition
          case 'ED':
            $this->parse('edition', $restChars);
            break;

          // Date
          case 'FD':
            $this->parse('issue_date', $restChars);
            break;

          /*
            // Type
            case '%9':
            $this->parse('type_of_report', $restChars);
            break;
           */

          /*
            // Subsidiary Author
            case '%?':
            $this->parse('authors', $restChars);
            break;
           */

          // ISBN/ISSN
          case 'SN':
            if (isset($this->parsedItems['pub_type']) && $this->parsedItems['pub_type'] == "article") {
              $this->parse('issn', $restChars);
            }
            else {
              $this->parse('isbn', $restChars);
            }
            break;

          default:
            break;
        }
      }
    }
    elseif ($this->lastItem != '' && $firstTwoChars != '') {
      if ($this->lastItem == 'authors') {
        $authorNames = $this->parseAuthors($restChars);
        if (count($authorNames)) {
          $this->parsedItems[$this->lastItem][] = $authorNames;
        }
      }
      else {
        $delim = ' ';
        $this->parsedItems[$this->lastItem] .= $delim . $restChars;
      }
    }
  }

  public function parse($type, $restChars) {
    if (isset($this->parsedItems[$type])) {
      if ($type == 'authors') {
        $authorNames = $this->parseAuthors($restChars);
        $this->parsedItems[$type][] = $authorNames;
      }
      else {
        $delim = ' ';
        $this->parsedItems[$type] .= $delim . $restChars;
      }
    }
    else {
      if ($type == 'authors') {
        $authorNames = $this->parseAuthors($restChars);
        $this->parsedItems[$type][] = $authorNames;
      }
      else {
        $this->parsedItems[$type] = $restChars;
      }
    }
    $this->lastItem = $type;
  }

  public function parseAuthors($name) {
    $name = trim($name);
    $parts = explode(",", $name);
    if (count($parts) == 2) {
        $name = trim($parts[1].' '.$parts[0]);
    }
    $name = str_replace(",", " ", $name);
    $name = preg_replace('/\s\s+/', ' ', $name);
    $name = explode(" ", $name);

    if (count($name) == 2) {
      $result = array('name' => $name[0], 'middlename' => '', 'surname' => $name[1]);
    }
    elseif (count($name) == 3) {
      $result = array('name' => $name[0], 'middlename' => $name[1], 'surname' => $name[2]);
    }
    else {
      $result = array('name' => '', 'middlename' => '', 'surname' => '');
    }

    return $result;
  }

  public function getFields() {
    return $this->parsedItems;
  }

}
