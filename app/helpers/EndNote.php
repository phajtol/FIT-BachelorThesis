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
class EndNote extends Nette\Object {

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
        $this->definition .= '%0 Unused 1' . $this->newLine;
        break;
      case "book":
        $this->definition .= '%0 Book' . $this->newLine;
        break;
      case "article":
        $this->definition .= '%0 Journal Article' . $this->newLine;
        break;
      case "inproceedings":
        $this->definition .= '%0 Conference Paper' . $this->newLine;
        break;
      case "proceedings":
        $this->definition .= '%0 Conference Proceedings' . $this->newLine;
        break;
      case "incollection":
        $this->definition .= '%0 Book Section' . $this->newLine;
        break;
      case "inbook":
        $this->definition .= '%0 Book Section' . $this->newLine;
        break;
      case "booklet":
        $this->definition .= '%0 Pamphlet' . $this->newLine;
        break;
      case "manual":
        $this->definition .= '%0 Manuscript' . $this->newLine;
        break;
      case "techreport":
        $this->definition .= '%0 Report' . $this->newLine;
        break;
      case "mastersthesis":
        $this->definition .= '%0 Thesis' . $this->newLine;
        break;
      case "phdthesis":
        $this->definition .= '%0 Thesis' . $this->newLine;
        break;
      case "unpublished":
        $this->definition .= '%0 Unpublished Work' . $this->newLine;
        break;
    }

    if (isset($this->pub['author_array']) && count($this->pub['author_array'])) {
      foreach ($this->pub['author_array'] as $author) {
        $this->definition .= "%A " . $author . $this->newLine;
      }
    }

    // dodelat conference u booktitle nebo journal
    // place publisher je publisher ??
    $this->definition .= isset($this->pub['title']) && $this->pub['title'] ? "%T " . $this->pub['title'] . $this->newLine : '';
    $this->definition .= isset($this->pub['booktitle']) && $this->pub['booktitle'] ? "%B " . $this->pub['booktitle'] . $this->newLine : '';
    $this->definition .= isset($this->pub['year']) && $this->pub['year'] ? "%D " . $this->pub['year'] . $this->newLine : '';
    if (!empty($this->pub['issue_year'])) {
        $this->definition .= "%8 " . $this->pub['issue_year'];
        if (!empty($this->pub['issue_month'])) {
            $this->definition .= "-".str_pad($this->pub['issue_month'],2,'0',STR_PAD_LEFT);
        }
        $this->definition .= $this->newLine;
    }
    $this->definition .= isset($this->pub['editor']) && $this->pub['editor'] ? "%E " . $this->pub['editor'] . $this->newLine : '';
    $this->definition .= isset($this->pub['publisher']) && $this->pub['publisher'] ? "%I " . $this->pub['publisher'] . $this->newLine : '';
    $this->definition .= isset($this->pub['journal']) && $this->pub['journal'] ? "%J " . $this->pub['journal'] . $this->newLine : '';
    $this->definition .= isset($this->pub['number']) && $this->pub['number'] ? "%N " . $this->pub['number'] . $this->newLine : '';
    $this->definition .= isset($this->pub['pages']) && $this->pub['pages'] ? "%P " . $this->pub['pages'] . $this->newLine : '';

    $this->definition .= isset($this->pub['url']) && $this->pub['url'] ? "%U " . $this->pub['url'] . $this->newLine : '';
    $this->definition .= isset($this->pub['volume']) && $this->pub['volume'] ? "%V " . $this->pub['volume'] . $this->newLine : '';
    $this->definition .= isset($this->pub['abstract']) && $this->pub['abstract'] ? "%X " . $this->pub['abstract'] . $this->newLine : '';
    $this->definition .= isset($this->pub['note']) && $this->pub['note'] ? "%Z " . $this->pub['note'] . $this->newLine : '';
    $this->definition .= isset($this->pub['edition']) && $this->pub['edition'] ? "%7 " . $this->pub['edition'] . $this->newLine : '';

    $this->definition .= isset($this->pub['type_of_report']) && $this->pub['type_of_report'] ? "%9 " . $this->pub['type_of_report'] . $this->newLine : '';
    foreach ($this->pub['isbn'] as $isbn) {
      $this->definition .= !empty($isbn->isbn) ? "%@ " . $isbn->isbn . $this->newLine : '';
    }
    $this->definition .= isset($this->pub['address']) && $this->pub['address'] ? "%C " . $this->pub['address'] . $this->newLine : (isset($this->pub['publisher_address']) && $this->pub['publisher_address'] ? "%C " . $this->pub['publisher_address'] . $this->newLine : '');
    $this->definition .= isset($this->pub['school']) && $this->pub['school'] ? "%1 School: " . $this->pub['school'] . $this->newLine : '';
    $this->definition .= isset($this->pub['organization']) && $this->pub['organization'] ? "%2 Organization: " . $this->pub['organization'] . $this->newLine : '';
    $this->definition .= isset($this->pub['institution']) && $this->pub['institution'] ? "%3 Institution: " . $this->pub['institution'] . $this->newLine : '';
  }

  public function getDefinition() {
    return $this->definition;
  }

}
