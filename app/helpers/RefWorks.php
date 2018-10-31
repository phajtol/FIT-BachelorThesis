<?php

namespace App\Helpers;

use Nette;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EndNote
 *
 * @author JOHNi
 */
class RefWorks {

    use Nette\SmartObject;

  private $pub;
  private $definition;
  private $newLine;

  public function __construct($publication) {
    $this->pub = $publication;
    $this->definition = '';
    $this->newLine = "<br />";
  }

  /*
    'misc' => 'Misc (other kinds of publication)',
    'book' => 'Book (a published book)',
    'article' => 'Article (an article from a magazine or a journal)',
    'inproceedings' => 'InProceedings (an article in a conference proceedings)',
    'proceedings' => 'Proceedings (the proceedings of a conference)',
    'incollection' => 'InCollection (a section of a book having its own title)',
    'inbook' => 'InBook (a section of a book)',
    'booklet' => 'Booklet (a bound work without a named publisher or sponsor)',
    'manual' => 'Manual (technical manual)',
    'techreport' => 'Techreport (a technical report from an institution)',
    'mastersthesis' => 'Mastersthesis (master thesis)',
    'phdthesis' => 'Phdthesis (Ph.D. thesis)',
    'unpublished' => 'Unpublished (an unpublished article, book, thesis, etc.)'
   */

  public function createDefinition() {
    switch ($this->pub['pub_type']) {
      case "misc":
        $this->definition .= 'RT Unknown' . $this->newLine;
        break;
      case "book":
        $this->definition .= 'RT Book, Whole' . $this->newLine;
        break;
      case "article":
        $this->definition .= 'RT Journal Article' . $this->newLine;
        break;
      case "inproceedings":
        $this->definition .= 'RT Conference Proceedings' . $this->newLine;
        break;
      case "proceedings":
        $this->definition .= 'RT Conference Proceedings' . $this->newLine;
        break;
      case "incollection":
        $this->definition .= 'RT Book, Section' . $this->newLine;
        break;
      case "inbook":
        $this->definition .= 'RT Book, Section' . $this->newLine;
        break;
      case "booklet":
        $this->definition .= 'RT Book, Whole' . $this->newLine;
        break;
      case "manual":
        $this->definition .= 'RT Unknown' . $this->newLine;
        break;
      case "techreport":
        $this->definition .= 'RT Report' . $this->newLine;
        break;
      case "mastersthesis":
        $this->definition .= 'RT Dissertation/Thesis' . $this->newLine;
        break;
      case "phdthesis":
        $this->definition .= 'RT Dissertation/Thesis' . $this->newLine;
        break;
      case "unpublished":
        $this->definition .= 'RT Unpublished Material' . $this->newLine;
        break;
    }
    // autori asi kazdej na jednu lajnu

    if (isset($this->pub['author_array']) && count($this->pub['author_array'])) {
      foreach ($this->pub['author_array'] as $author) {
        $this->definition .= "A1 " . $author . $this->newLine;
      }
    }

    $this->definition .= isset($this->pub['title']) && $this->pub['title'] ? "T1 " . $this->pub['title'] . $this->newLine : '';
    $this->definition .= isset($this->pub['booktitle']) && $this->pub['booktitle'] ? "T2 " . $this->pub['booktitle'] . $this->newLine : '';
    $this->definition .= isset($this->pub['year']) && $this->pub['year'] ? "YR " . $this->pub['year'] . $this->newLine : '';
    if (!empty($this->pub['issue_year'])) {
        $this->definition .= "FD " . $this->pub['issue_year'];
        if (!empty($this->pub['issue_month'])) {
            $this->definition .= "-".str_pad($this->pub['issue_month'],2,'0',STR_PAD_LEFT);
        }
        $this->definition .= $this->newLine;
    }    $this->definition .= isset($this->pub['editor']) && $this->pub['editor'] ? "A2 " . $this->pub['editor'] . $this->newLine : '';
    $this->definition .= isset($this->pub['publisher']) && $this->pub['publisher'] ? "PB " . $this->pub['publisher'] . $this->newLine : '';
    $this->definition .= isset($this->pub['journal']) && $this->pub['journal'] ? "T2 " . $this->pub['journal'] . $this->newLine : '';
    $this->definition .= isset($this->pub['number']) && $this->pub['number'] ? "AN " . $this->pub['number'] . $this->newLine : '';
    $this->definition .= isset($this->pub['pages_start']) && $this->pub['pages_start'] ? "SP " . $this->pub['pages_start'] . $this->newLine : '';
    $this->definition .= isset($this->pub['pages_end']) && $this->pub['pages_end'] ? "OP " . $this->pub['pages_end'] . $this->newLine : '';

    $this->definition .= isset($this->pub['url']) && $this->pub['url'] ? "UL " . $this->pub['url'] . $this->newLine : '';
    $this->definition .= isset($this->pub['volume']) && $this->pub['volume'] ? "VO " . $this->pub['volume'] . $this->newLine : '';
    $this->definition .= isset($this->pub['abstract']) && $this->pub['abstract'] ? "AB " . $this->pub['abstract'] . $this->newLine : '';
    $this->definition .= isset($this->pub['note']) && $this->pub['note'] ? "NO " . $this->pub['note'] . $this->newLine : '';
    $this->definition .= isset($this->pub['edition']) && $this->pub['edition'] ? "ED " . $this->pub['edition'] . $this->newLine : '';

    // $this->definition .= isset($this->pub['type_of_report']) && $this->pub['type_of_report'] ? "%9 " . $this->pub['type_of_report'] . $this->newLine : '';
    foreach ($this->pub['isbn'] as $isbn) {
      $this->definition .= !empty($isbn->isbn) ? "SN " . $isbn->isbn . $this->newLine : '';
    }

    $this->definition .= isset($this->pub['address']) && $this->pub['address'] ? "PP " . $this->pub['address'] . $this->newLine : (isset($this->pub['publisher_address']) && $this->pub['publisher_address'] ? "PP " . $this->pub['publisher_address'] . $this->newLine : '');
    // $this->definition .= isset($this->pub['school']) && $this->pub['school'] ? "%1 School: " . $this->pub['school'] . $this->newLine : '';
    // $this->definition .= isset($this->pub['organization']) && $this->pub['organization'] ? "%2 Organization: " . $this->pub['organization'] . $this->newLine : '';
    // $this->definition .= isset($this->pub['institution']) && $this->pub['institution'] ? "%3 Institution: " . $this->pub['institution'] . $this->newLine : '';
  }

  public function getDefinition() {
    return $this->definition;
  }

}
