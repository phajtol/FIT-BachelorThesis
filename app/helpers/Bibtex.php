<?php

namespace App\Helpers;

use Nette;

/**
 * Helping class for bibtex validation
 *
 */
abstract class Bibtex {

    use Nette\SmartObject;

  protected $pub_type;
  protected $report;
  protected $starmap;
  protected $pad = '&nbsp;&nbsp;&nbsp;'; //padding string

  /**
   * Factory method. Regarding to the type of publication creates appropriate successor class
   *
   * @param string $pub_type type of publication
   */

  public static function create($pub_type) {
    switch ($pub_type) {
      case 'article':
        return new Article();
      case 'book':
        return new Book();
      case 'booklet':
        return new Booklet();
      case 'inbook':
        return new Inbook();
      case 'incollection':
        return new Incollection();
      case 'inproceedings':
        return new Inproceedings();
      case 'manual':
        return new Manual();
      case 'mastersthesis':
        return new Mastersthesis();
      case 'misc':
        return new Misc();
      case 'phdthesis':
        return new Phdthesis();
      case 'proceedings':
        return new Proceedings();
      case 'techreport':
        return new Techreport();
      case 'unpublished':
        return new Unpublished();
      default:
       // throw new SLException("Publication of type `$pub_type` doesn't exist.");
    }
    return null;
  }

  /**
   * When implemented in successors, should validate input form data from the user
   * and return textual report if some mandatory parameters are missing or some
   * parameters are left over.
   *
   * @param array $data ('$param name' => '$value'); value must be trimmed
   * @return string report
   */
  public function validate($data) {
    extract($data);
    if (empty($title)) //title must be always given
     // throw new Exception('Title is missing');
    return $this->report;
  }

  /**
   * Writes to the report
   *
   * @param string $msg message
   */
  protected function report_write($msg) {
    $this->report .= "- $msg<br />";
  }

  /**
   * Prints char '*' if parameter arrtibute is mandatory or nothing if not
   *
   * @param string $attribute
   * @return char
   */
  public function star($attribute) {
    print($this->starmap[$attribute]);
  }
  
  public function valid_isbn($isbn) {
        return preg_match('/^[0-9]+-[0-9]+-[0-9]+-([0-9]+|X)$/', $isbn) && (strlen($isbn) == 13) || preg_match('/^[0-9]+-[0-9]+-[0-9]+-([0-9]+|X)$/', $isbn) && (strlen($isbn) == 17);
    }

  /**
   * When implemented by successor, should return
   * bibtex representation of the publication
   *
   * @param array $data
   * @return string bibtex data
   */
  public abstract function get_bibtex($data);

  /**
   * Formats output bibtex definition to html
   *
   * @param $attribute bibtex attribute value
   */
  protected function html_conv($definition) {
    return nl2br($definition); //str_replace(' ', '&nbsp;', $definition)
  }

  /**
   * Do some conversions of input data to preserve right format of bibtex fields
   *
   * @param array $data
   * @return array
   */
  protected function convert($data) {
    $pages = $data['pages'];
    if (strpos($pages, '-') !== false) {
      $pages = str_replace('-', '--', $pages);
      $data['pages'] = $pages;
    }
    return $data;
  }

  /**
   * Return abbreviation of citation
   *
   * @param array $data BibTeX fields
   */
  protected function abb($data) {
    return $data['year'];
  }

}

/**
 * Article
 *
 */
class Article extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '* (or fill editor)',
      'editor' => '* (or fill author)',
      'journal' => '*',
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);

    if (count($author) == 0)
      $this->report_write('Author is missing');
    if (!$journal)
      $this->report_write('Journal is missing');
    if (empty($year))
      $this->report_write('Year is missing');
    return $this->report;
  }

  /**
   * Return bibtex definition for Article
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",".
         (isset($journal) && $journal ? "$this->pad journal = \"$journal\"," : '').",
			$this->pad title = \"$title\",
			$this->pad year = \"$year\",\n"
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($volume) && $volume ? "$this->pad volume = \"$volume\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($number) && $number ? "$this->pad number = \"$number\",\n" : '')
        . (isset($pages) && $pages ? "$this->pad pages = \"$pages\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Book
 *
 */
class Book extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '* (or fill editor)',
      'editor' => '* (or fill author)',
      'publisher' => '*',
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (isset($author) && count($author) == 0 && empty($editor))
      $this->report_write('Both author and editor are missing');
    if (empty($publisher))
      $this->report_write('Publisher is missing');
    if (empty($year))
      $this->report_write('Year is missing');
    if (isset($isbn) && $isbn && !$this->valid_isbn($isbn))
      $this->report_write('ISBN format is wrong');
    return $this->report;
  }

  /**
   * Return bibtex definition for Book
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",
			$this->pad editor = \"$editor\",
			$this->pad publisher = \"$publisher\",
			$this->pad title = \"$title\",
			$this->pad year = \"$year\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($edition) && $edition ? "$this->pad edition = \"$edition\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($number) && $number ? "$this->pad number = \"$number\",\n" : '')
        . (isset($series) && $series ? "$this->pad series = \"$series\",\n" : '')
        . (isset($volume) && $volume ? "$this->pad volume = \"$volume\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Booklet
 *
 */
class Booklet extends Bibtex {

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
  }

  /**
   * Return bibtex definition for Booklet
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type&lcub;
			$this->pad title = \"$title\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($author) && $author ? "$this->pad author = \"$author\",\n" : '')
        . (isset($howpublished) && $howpublished ? "$this->pad howpublished = \"$howpublished\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($year) && $year ? "$this->pad year = \"$year\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n &rcub;";
    return nl2br($bib);
  }

}

/**
 * Inbook
 *
 */
class Inbook extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '* (or fill editor)',
      'editor' => '* (or fill author)',
      'chapter' => '* (or fill pages range)',
      'pages' => '* (or fill chapter)',
      'publisher' => '*',
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (isset($author) && count($author) == 0 && empty($editor))
      $this->report_write('Author is missing ani není uveden editor');
    if (empty($chapter) && empty($pages))
      $this->report_write('Both chapter and pages are empty');
    if (empty($publisher))
      $this->report_write('Publisher is missing');
    if (empty($year))
      $this->report_write('Year is missing');

    if (isset($isbn) && $isbn && !$this->valid_isbn($isbn))
      $this->report_write('ISBN format is wrong');
    return $this->report;
  }

  /**
   * Return bibtex definition for Inbook
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",
			$this->pad editor = \"$editor\",
			$this->pad chapter = \"$$chapter\",
			$this->pad pages = \"$pages\",
			$this->pad publisher = \"$publisher\",
			$this->pad title = \"$title\",
			$this->pad year = \"$year\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($edition) && $edition ? "$this->pad edition = \"$edition\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($number) && $number ? "$this->pad number = \"$number\",\n" : '')
        . (isset($series) && $series ? "$this->pad series = \"$series\",\n" : '')
        . (isset($volume) && $volume ? "$this->pad volume = \"$volume\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Incollection
 *
 */
class Incollection extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '*',
      'booktitle' => '*',
      'publisher' => '*',
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (isset($author) && count($author) == 0)
      $this->report_write('Author is missing');
    if (empty($booktitle))
      $this->report_write('Booktitle is missing');
    if (empty($publisher))
      $this->report_write('Publisher is missing');
    if (empty($year))
      $this->report_write('Year is missing');

    if (isset($isbn) && $isbn && !$this->valid_isbn($isbn))
      $this->report_write('ISBN format is wrong');
    return $this->report;
  }

  /**
   * Return bibtex definition for Incollection
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",
			$this->pad booktitle = \"$booktitle\",
			$this->pad publisher = \"$publisher\",
			$this->pad title = \"$title\",
			$this->pad year = \"$year\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($chapter) && $chapter ? "$this->pad chapter = \"$chapter\",\n" : '')
        . (isset($edition) && $edition ? "$this->pad edition = \"$edition\",\n" : '')
        . (isset($editor) && $editor ? "$this->pad editor = \"$editor\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($number) && $number ? "$this->pad number = \"$number\",\n" : '')
        . (isset($pages) && $pages ? "$this->pad pages = \"$pages\",\n" : '')
        . (isset($series) && $series ? "$this->pad series = \"$series\",\n" : '')
        . (isset($volume) && $volume ? "$this->pad volume = \"$volume\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Inproceedings
 *
 */
class Inproceedings extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '*',
      'booktitle' => '*',
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (count($author) == 0)
      $this->report_write('Author is missing');
    //if (empty($conference) && empty($booktitle))
    //	$this->report_write('No conference was chosen');
    if (empty($conference2) && empty($booktitle))
      $this->report_write('No conference was chosen');
    if (empty($conference_year) && empty($booktitle))
      $this->report_write('No conference year was chosen');
    if (empty($year))
      $this->report_write('Year is missing');
    return $this->report;
  }

  /**
   * Return bibtex definition for Inproceedings
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",
			$this->pad booktitle = \"$booktitle\",
			$this->pad title = \"$title\",
			$this->pad year = \"$year\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($editor) && $editor ? "$this->pad editor = \"$editor\",\n" : '')
        . (isset($location) && $location ? "$this->pad location = \"$location\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($number) && $number ? "$this->pad number = \"$number\",\n" : '')
        . (isset($organization) && $organization ? "$this->pad organization = \"$organization\",\n" : '')
        . (isset($pages) && $pages ? "$this->pad pages = \"$pages\",\n" : '')
        . (isset($publisher) && $publisher ? "$this->pad publisher = \"$publisher\",\n" : '')
        . (isset($series) && $series ? "$this->pad series = \"$series\",\n" : '')
        . (isset($volume) && $volume ? "$this->pad volume = \"$volume\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Manual
 *
 */
class Manual extends Bibtex {

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    return $this->report;
  }

  /**
   * Return bibtex definition for Manual
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad title = \"$title\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($author) && $author ? "$this->pad author = \"$author\",\n" : '')
        . (isset($edition) && $edition ? "$this->pad edition = \"$edition\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($organization) && $organization ? "$this->pad organization = \"$organization\",\n" : '')
        . (isset($year) && $year ? "$this->pad year = \"$year\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Mastersthesis
 *
 */
class Mastersthesis extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '*',
      'school' => '*',
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (count($author) == 0)
      $this->report_write('Author is missing');
    if (empty($school))
      $this->report_write('School is missing');
    if (empty($year))
      $this->report_write('Year is missing');
    return $this->report;
  }

  /**
   * Return bibtex definition for Mastersthesis
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",
			$this->pad school = \"$school\",
			$this->pad title = \"$title\",
			$this->pad year = \"$year\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Misc
 *
 */
class Misc extends Bibtex {

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
  }

  /**
   * Return bibtex definition for Misc
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad title = \"$title\",
			$this->pad author = \"$author\",\n"
        . (isset($editor) && $editor ? "$this->pad editor = \"$editor\",\n" : '')
        . (isset($howpublished) && $howpublished ? "$this->pad howpublished = \"$howpublished\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($year) && $year ? "$this->pad year = \"$year\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Phdthesis
 *
 */
class Phdthesis extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '*',
      'school' => '*',
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (count($author) == 0)
      $this->report_write('Author is missing');
    if (empty($school))
      $this->report_write('School is missing');
    if (empty($year))
      $this->report_write('Year is missing');
    return $this->report;
  }

  /**
   * Return bibtex definition for Phdthesis
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",
			$this->pad school = \"$school\",
			$this->pad title = \"$title\",
			$this->pad year = \"$year\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Proceedings
 *
 */
class Proceedings extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (empty($year))
      $this->report_write('Year is missing');
    return $this->report;
  }

  /**
   * Return bibtex definition for Proceedings
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad title = \"$title\,\n"
        . (isset($year) && $year ? "$this->pad year = \"$year\",\n" : '')
        . (isset($conference_year_w_year) && $conference_year_w_year ? "$this->pad year = \"$conference_year_w_year\",\n" : '')
        . (isset($editor) && $editor ? "$this->pad editor = \"$editor\",\n" : '')
        . (isset($location) && $location ? "$this->pad location = \"$location\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($number) && $number ? "$this->pad number = \"$number\",\n" : '')
        . (isset($organization) && $organization ? "$this->pad organization = \"$organization\",\n" : '')
        . (isset($publisher) && $publisher ? "$this->pad publisher = \"$publisher\",\n" : '')
        . (isset($series) && $series ? "$this->pad series = \"$series\",\n" : '')
        . (isset($volume) && $volume ? "$this->pad volume = \"$volume\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Techreport
 *
 */
class Techreport extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '*',
      'institution' => '*',
      'year' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (count($author) == 0)
      $this->report_write('Author is missing');
    if (empty($institution))
      $this->report_write('Institution is missing');
    if (empty($year))
      $this->report_write('Year is missing');
    return $this->report;
  }

  /**
   * Return bibtex definition for Techreport
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",
			$this->pad institution = \"$institution\",
			$this->pad title = \"$title\",
			$this->pad year = \"$year\",\n"
        . (isset($address) && $address ? "$this->pad address = \"$address\",\n" : '')
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($note) && $note ? "$this->pad note = \"$note\",\n" : '')
        . (isset($number) && $number ? "$this->pad number = \"$number\",\n" : '')
        . (isset($type) && $type ? "$this->pad type = \"$type\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

/**
 * Unpublished
 *
 */
class Unpublished extends Bibtex {

  function __construct() {
    $this->starmap = array(
      'author' => '*',
      'note' => '*'
    );
  }

  /**
   * Check if all mandatory BibTeX fields are given
   *
   * @param array $data
   * @return string validation report
   */
  public function validate($data) {
    parent::validate($data);
    extract($data);
    if (count($author))
      $this->report_write('Author is missing');
    if (empty($note))
      $this->report_write('Note is missing');
    if (empty($year))
      $this->report_write('Year is missing');
    return $this->report;
  }

  /**
   * Return bibtex definition for Unpublished
   *
   * @param array $data
   * @return string
   */
  public function get_bibtex($data) {
    $data = parent::convert($data);
    extract($data);
    $bib = "@$pub_type{" . $this->abb($data) . ",
			$this->pad author = \"$author\",
			$this->pad note = \"$note\",
			$this->pad title = \"$title\",\n"
        . (isset($month) && $month ? "$this->pad month = \"$month\",\n" : '')
        . (isset($year) && $year ? "$this->pad year = \"$year\",\n" : '');
    if ($bib[strlen($bib) - 2] == ',')
      $bib = substr($bib, 0, strlen($bib) - 2);
    $bib .= "\n}";
    return nl2br($bib);
  }

}

?>