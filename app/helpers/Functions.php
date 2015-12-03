<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Helpers;

use Nette;

class Functions {

    /**
     * Returns textual representation of month
     *
     * @param string $month month as 2 digits
     * @return string
     */
    public function month_cze($month) {
        switch ($month) {
            case '01': return 'Leden';
            case '02': return 'Únor';
            case '03': return 'Březen';
            case '04': return 'Duben';
            case '05': return 'Květen';
            case '06': return 'Červen';
            case '07': return 'Červenec';
            case '08': return 'Srpen';
            case '09': return 'Září';
            case '10': return 'Říjen';
            case '11': return 'Listopad';
            case '12': return 'Prosinec';
            default: return '';
        }
    }

    /**
     * Returns textual representation of month
     *
     * @param string $month month as 2 digits
     * @return string
     */
    public function month_eng($month) {
        switch ($month) {
            case '01': return 'January';
            case '02': return 'February';
            case '03': return 'March';
            case '04': return 'April';
            case '05': return 'May';
            case '06': return 'June';
            case '07': return 'July';
            case '08': return 'August';
            case '09': return 'September';
            case '10': return 'October';
            case '11': return 'November';
            case '12': return 'December';
            default: return '';
        }
    }

    /**
     * Converts textual representation of month to digital
     *
     * @param string $month
     * @return string digital month
     */
    public function strmonth2nummonth($month) {
        $m = trim(strtolower($month));
        switch ($m) {
            case 'january': return '01';
            case 'february': return '02';
            case 'march': return '03';
            case 'april': return '04';
            case 'may': return '05';
            case 'june': return '06';
            case 'july': return '07';
            case 'august': return '08';
            case 'september': return '09';
            case 'october': return '10';
            case 'november': return '11';
            case 'december': return '12';
            //abbreviations
            case 'jan': return '01';
            case 'feb': return '02';
            case 'mar': return '03';
            case 'apr': return '04';
            case 'may': return '05';
            case 'jun': return '06';
            case 'jul': return '07';
            case 'aug': return '08';
            case 'sep': return '09';
            case 'oct': return '10';
            case 'nov': return '11';
            case 'dec': return '12';
            default: return '00';
        }
    }

    /**
     * Returns true if publication exists
     *
     * @param PEAR::DB $db
     * @param string $title
     */
    public function publiation_exists(&$db, $title) {
        $c = $db->getOne("SELECT count(*) FROM publication WHERE title = '$title'");
        if (PEAR::isError($c))
            throw new Exception($c->getMessage());
        if ($c > 0)
            return true;
        else
            return false;
    }

    /**
     * Returns validation message if some of pronounced counter records are missing.
     * Is usualy called before insert new publication to check whether or not
     * someone accidentaly deleted a counter record.
     *
     * @param PEAR::DB Db object
     * @param array $authors array of author ids
     * @param array $categories array of categories ids
     * @param int $publisher_id
     * @param int $journal_id
     * @param int $conference2_id
     * @param int $conference_year_id
     */
    public function validate_counters(&$db, $authors, $categories, $publisher_id, $journal_id, $conference2_id, $conference_year_id) {
        $report = '';
        //authors
        if ($authors)
            foreach ($authors as $a_id) {
                $is = $db->getOne("SELECT count(*) FROM `author` WHERE id=?", array($a_id));
                if (PEAR::isError($is))
                    throw new Exception($is->getMessage);
                if (!$is)
                    $report .= "One of the authors, you've selected are no longer in the database.\n";
            }
        //categories
        if ($categories)
            foreach ($categories as $c_id) {
                $is = $db->getOne("SELECT count(*) FROM `categories` WHERE id=?", array($c_id));
                if (PEAR::isError($is))
                    throw new Exception($is->getMessage);
                if (!$is)
                    $report .= "One of the categories, you've selected are no longer in the database.\n";
            }
        //publisher
        if ($publisher_id) {
            $is = $db->getOne("SELECT count(*) FROM `publisher` WHERE id=?", array($publisher_id));
            if (PEAR::isError($is))
                throw new Exception($is->getMessage);
            if (!$is)
                $report .= "Publisher you've selected is no longer in the database.\n";
        }
        //journal
        if ($journal_id) {
            $is = $db->getOne("SELECT count(*) FROM `journal` WHERE id=?", array($journal_id));
            if (PEAR::isError($is))
                throw new Exception($is->getMessage);
            if (!$is)
                $report .= "Journal you've selected is no longer in the database.\n";
        }
        //conference2
        if ($conference2_id) {
            $is = $db->getOne("SELECT count(*) FROM `conference2` WHERE id=?", array($conference2_id));
            if (PEAR::isError($is))
                throw new Exception($is->getMessage);
            if (!$is)
                $report .= "Conference2 you've selected is no longer in the database.\n";
        }
        //conference_year
        if ($conference_year_id) {
            $is = $db->getOne("SELECT count(*) FROM `conference_year` WHERE id=?", array($conference_year_id));
            if (PEAR::isError($is))
                throw new Exception($is->getMessage);
            if (!$is)
                $report .= "Conference_year you've selected is no longer in the database.\n";
        }
        //report finish
        if ($report)
            $report .= "\n Someone must have deleted it from the database. Please go back and resolve the situation.";
        return $report;
    }

    /**
     * Selects publication and merges it with all additional entities
     * such as publisher, journal, conference and authors.
     * Resulting array is sanitized and contains keys with BibTeX attribute names.
     *
     * @param PEAR::DB $db
     * @param int $publication_id
     * @return array associative array where keys are names of BibTeX fields
     */
    public function get_all_publication_data(&$db, $publication_id) {
        $res = $db->query("SELECT *
		FROM `publication` WHERE id = '$publication_id'");
        if (PEAR::isError($res))
            throw new Exception($sth->getMessage());
        $row = null;
        if (!$row = $res->fetchRow(DB_FETCHMODE_ASSOC))
            throw new Exception("Requested publication not found (id=$publication_id)");
        $author_arr = null;
        $author_surnames = null;
        $authors = get_authors($db, $publication_id, $author_surnames, $author_arr); //$author_arr is filled by reference
        $all_data = array_merge($row, array('author' => $authors));
        $all_data = array_merge($all_data, array('author_arr' => $author_arr));
        $publisher_data = get_publisher_data($db, $row['publisher_id']);
        $conference2_data = get_conference2_data($db, $row['conference_year_id']);
        $conference_year_data = get_conference_year_data($db, $row['conference_year_id']);
        $journal_data = get_journal_data($db, $row['journal_id']);


        if (count($publisher_data) != 0) {
            array_diff_key($all_data, array('address' => 0)); //we will take into account only publisher address, not the common address from `publication` table
            $all_data = array_merge($all_data, $publisher_data);
        }
        if (count($conference_data) != 0) { //publication is conference and therefore needs cinference data from conference table
            array_diff_key($all_data, array(
                'isbn' => 0, 'issue_date' => 0, 'booktitle' => 0)); //remove items which may not stay in array $all_data because otherwise they would colide with $conference_data items
            $from = $conference_data['w_from'] == '0000-00-00' ? '' : $conference_data['w_from'];
            $to = $conference_data['w_to'] == '0000-00-00' ? '' : $conference_data['w_to'];
            $month = substr($conference_data['w_from'], 5, 2) == '00' ? '' : substr($conference_data['w_from'], 5, 2);
            $month_cze = month_cze($month);
            $month_eng = month_eng($month);
            $ym = array(
                'booktitle' => $conference_data['conference'],
                'year' => $conference_data['year'] == '0000' ? '' : $conference_data['year'],
                'issue_date' => $conference_data['year'] == '0000' ? '' : $conference_data['year'],
                'month' => $month,
                'month_eng' => $month_eng,
                'month_cze' => $month_cze,
                'from' => $from,
                'to' => $to);
            $all_data = array_merge($all_data, $conference_data);
        }
        if (count($conference2_data) != 0) { //publication is conference2 and therefore needs cinference data from conference2 table
            $all_data = array_merge($all_data, $conference2_data);
        }
        if (count($conference_year_data) != 0) { //publication is conference and therefore needs conference_year data from conference_year table
            array_diff_key($all_data, array('isbn' => 0, 'issue_date' => 0, 'booktitle' => 0)); //remove items which may not stay in array $all_data because otherwise they would colide with $conference_data items
            $from = $conference_year_data['conference_year_w_from'] == '0000-00-00' ? '' : $conference_year_data['conference_year_w_from'];
            $to = $conference_year_data['conference_year_w_to'] == '0000-00-00' ? '' : $conference_year_data['conference_year_w_to'];
            $month = substr($conference_year_data['conference_year_w_from'], 5, 2) == '00' ? '' : substr($conference_year_data['conference_year_w_from'], 5, 2);
            $month_cze = month_cze($month);
            $month_eng = month_eng($month);
            $ym = array(
                'booktitle' => $conference_year_data['conference_year'],
                'year' => $conference_year_data['conference_year_w_year'] == '0000' ? '' : $conference_year_data['conference_year_w_year'],
                'issue_date' => $conference_year_data['conference_year_w_year'] == '0000' ? '' : $conference_year_data['conference_year_w_year'],
                'month' => $month,
                'month_eng' => $month_eng,
                'month_cze' => $month_cze,
                'from' => $from,
                'to' => $to,
                'location' => $conference_year_data['conference_year_location']);
            $all_data = array_merge($all_data, $conference_year_data, $ym);
        } else { //publication doesn't refferer any conference
            $year = substr($row['issue_date'], 0, 4) == '0000' ? '' : substr($row['issue_date'], 0, 4);
            $month = substr($row['issue_date'], 5, 2) == '00' ? '' : substr($row['issue_date'], 5, 2);
            $month_cze = month_cze($month);
            $month_eng = month_eng($month);

            $ym = array(
                'year' => $year,
                'month' => $month,
                'month_cze' => $month_cze,
                'month_eng' => $month_eng,);
            $all_data = array_merge($all_data, $ym);
        }
        if (count($journal_data) != 0) //conference refferences journal data
            $all_data = array_merge($all_data, $journal_data);
        if ($row['pub_type'] == 'mastersthesis' || $row['pub_type'] == 'phdthesis')
            $all_data['address'] = $all_data['school_address'];
        if ($row['pub_type'] == 'techreport')
            $all_data['type'] = $all_data['type_of_report'];
        $issue_date = $all_data['issue_date'];
        if ($issue_date == '0000-00-00')
            $all_data['issue_date'] = '';
        $all_data['issue_date'] = substr($issue_date, 0, 7);

        return htmlspecialchars_rec($all_data);
    }

    /**
     * Returns row of publisher
     *
     * @param PEAR::DB db object
     * @param int $publisher_id
     * @return array
     */
    public function get_publisher_data(&$db, $publisher_id) {
        if (!is_numeric($publisher_id))
            return;
        $res = $db->query("SELECT name as publisher, address as publisher_address
			FROM `publisher`
			WHERE id = ?", $publisher_id);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        $row_publisher = $res->fetchRow(DB_FETCHMODE_ASSOC);
        return $row_publisher;
    }

    /**
     * Returns row of conference2
     *
     * @param PEAR::DB db object
     * @param int $conference2_id
     * @return array
     */
    public function get_conference2_data(&$db, $conference_year_id) {
        if (!is_numeric($conference_year_id))
            return;
        $res2 = $db->query(" SELECT name as conference2, id, abbreviation as conference2_abbreviation, description as conference2_description, first_year as conference2_first_year
		FROM `conference2`
		WHERE id=
     			(SELECT conference2_id
	       		FROM `conference_year`
        			WHERE id = ?)", $conference_year_id);


        if (PEAR::isError($res2))
            throw new Exception($res2->getMessage());
        $row_conference2 = $res2->fetchRow(DB_FETCHMODE_ASSOC);
        return $row_conference2;
    }

    /**
     * Returns row of conference_year
     *
     * @param PEAR::DB db object
     * @param int $conference_year_id
     * @return array
     */
    public function get_conference_year_data(&$db, $conference_year_id) {
        if (!is_numeric($conference_year_id))
            return;
        $res = $db->query("SELECT name as conference_year,conference2_id,  w_year as conference_year_w_year, w_from as conference_year_w_from, w_to as conference_year_w_to, location as conference_year_location, isbn as conference_year_isbn, description as conference_year_description, publisher_id as conference_year_publisher_id 
	       	FROM `conference_year`
        	WHERE id = ?", $conference_year_id);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        $row_conference_year = $res->fetchRow(DB_FETCHMODE_ASSOC);
        return $row_conference_year;
    }

    /**
     * Returns row of journal
     *
     * @param PEAR::DB db object
     * @param int $journal_id
     * @return array
     */
    public function get_journal_data(&$db, $journal_id) {
        if (!is_numeric($journal_id))
            return;
        $res = $db->query("SELECT name as journal, issn
		FROM `journal` WHERE id = '$journal_id'");
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        $row_journal = $res->fetchRow(DB_FETCHMODE_ASSOC);
        return $row_journal;
    }

    /**
     * Returns publication's extended attributes
     *
     * @param PEAR::DB $db
     * @param int $publication_id
     * @return array (name => value)
     */
    public function get_publication_attributes(&$db, $publication_id) {
        $sql = "SELECT DISTINCT name, value
				FROM attributes JOIN attrib_storage
				ON attrib_storage.attributes_id=attributes.id
				AND attributes.id=attrib_storage.attributes_id
				AND attrib_storage.attributes_id=attrib_storage.attributes_id
				WHERE attrib_storage.publication_id='$publication_id'
				AND attrib_storage.publication_id='$publication_id'
				AND confirmed=1";
        $attributes = $db->getAssoc($sql);
        return $attributes;
    }

    /**
     * Check an email address is valid
     *
     * @param string $address email adress
     * @return bool
     */
    public function valid_email($address) {
        if (ereg("^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$", $address))
            return true;
        else
            return false;
    }

    /**
     * Returns true if ISSN in valid formar, else returns false
     *
     * @param string $issn
     * @return bool
     */
    public function valid_issn($issn) {
        return ereg('^[0-9]{4}-([0-9]{4}|[0-9]{3}X)$', $issn);
    }

    /**
     * Returns true if pages range is in a valid format
     *
     * @param string $pp
     * @return bool
     */
    public function valid_pages($pp) {
        return ereg('^[0-9]{1,5}-[0-9]{1,5}$', $pp);
    }

    /**
     * Returns true if date is in a valid format (YYYY-MM-DD)
     *
     * @param string $date
     * @return bool
     */
    public function valid_date($date) {
        return ereg('^[0-9]{4}-[0-9]{2}-[0-9]{2}$', $date);
    }

    /**
     * Returns true if year is in a valid format (YYYY)
     *
     * @param string $date
     * @return bool
     */
    public function valid_year($date) {
        return ereg('^[0-9]{4}$', $date);
    }

    /**
     * Returns true if ISBN in valid format, else returns false
     *
     * @param string $isbn
     * @return bool
     */
    public function valid_isbn($isbn) {
        return ereg('^[0-9]+-[0-9]+-[0-9]+-([0-9]+|X)$', $isbn) && (strlen($isbn) == 13) || ereg('^[0-9]+-[0-9]+-[0-9]+-([0-9]+|X)$', $isbn) && (strlen($isbn) == 17);
    }

    /**
     * Highlights words in string with <strong> tags, words must be at least 3 chars long
     *
     * @param string $str string to highlight
     * @param string[] $words array with words to be highlighted
     * @return string highlighted string
     */
    public function highlight_str($str, $words) {
        foreach ($words as $word) {
            if (strlen(trim($word)) < 3)
                continue;
            $word = preg_quote($word);
            $str = eregi_replace("(.$word.)|(.$word$)|(^$word.)", " <strong>$word</strong> ", $str);
        }
        return $str;
    }

    /**
     * returns array with publication types
     *
     * @return array of pub_type_number => pub_type_name
     */
    public function pub_types() {
        return array(
            0 => 'misc',
            1 => 'book',
            2 => 'article',
            3 => 'inproceedings',
            4 => 'proceedings',
            5 => 'incollection',
            6 => 'inbook',
            7 => 'booklet',
            8 => 'manual',
            9 => 'techreport',
            10 => 'mastersthesis',
            11 => 'phdthesis',
            12 => 'unpublished'
        );
    }

    /**
     * returns array with publication types in Czech lang.
     *
     * @return array of pub_type_number => pub_type_name
     */
    public function pub_types_full() {
        return array(
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
        );
    }

    public function bibtex_fields() {
        return array(
            /* standard */
            'address',
            'annote',
            'author',
            'booktitle',
            'chapter',
            'crossref',
            'edition',
            'editor',
            'howpublished',
            'institution',
            'journal',
            'key',
            'month',
            'note',
            'number',
            'organization',
            'pages',
            'publisher',
            'school',
            'series',
            'title',
            'type',
            'volume',
            'year',
            /* advanced */
            'affiliation',
            'abstract',
            'contents',
            'copyright',
            'isbn',
            'issn',
            'keywords',
            'language',
            'location',
            'lccn',
            'mrnumber',
            'url',
            'doi'
        );
    }

    /**
     * Returns string with names of categories
     *
     * @param PEAR:DB db obect
     * @param int $publication_id
     * @return string
     */
    public function get_categories(&$db, $publication_id) {
        $sth = $db->prepare("SELECT name
								FROM `categories` JOIN `categories_has_publication`
								ON categories.id=categories_has_publication.categories_id
								WHERE publication_id=?");
        if (PEAR::isError($sth))
            throw new SException($sth2->getMessage());
        $categories_res = & $db->execute($sth, $publication_id);
        if (PEAR::isError($categories_res))
            throw new SException($categories_res->getMessage());
        $num_categories = $categories_res->numRows();
        $count = 1;
        $categories = '';
        while ($row_cat = & $categories_res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $categories .= $row_cat['name'] . ($count++ < $num_categories ? ', ' : '');
        }
        return $categories;
    }

    /**
     * Returns a textual string with full names of authors
     * assigned to a particular publication.
     *
     * @param PEAR:DB $db db object
     * @param int $publication_id
     * @param string $authors_surnames optional out parameter - contains string with surnames of each author divided by '+'
     * @param array $out_author_arr optional out parameter - contains array of associative arrays which have 4 keys - name, middlename, surname and initials
     * @param string optional separator between authors
     *
     * @return string
     */
    public function get_authors(&$db, $publication_id, &$author_surnames = null, &$out_author_arr = null, $separator = ', ') {
        $sth = $db->prepare("SELECT name, middlename, surname
							FROM `author` JOIN `author_has_publication`
							ON author.id = author_has_publication.author_id
							WHERE author_has_publication.publication_id = ?
							ORDER BY priority");
        if (PEAR::isError($sth))
            throw new SException($sth->getMessage());
        $authors_res = & $db->execute($sth, $publication_id);
        if (PEAR::isError($authors_res))
            throw new SException($authors_res->getMessage());
        $num_authors = $authors_res->numRows();
        $count = 1;
        $authors = '';
        $author_surnames = '';
        while ($row2 = & $authors_res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $initials = $row2['name'][0] . '.' . ($row2['middlename'] ? ' ' . $row2['middlename'][0] . '.' : '');
            $out_author_arr[] = array_merge($row2, array('initials' => $initials));
            $authors .= $row2['surname'] . ' ' . ($row2['middlename'] ? $row2['middlename'] . ' ' : '') . $row2['name'] . ($count++ < $num_authors ? $separator : '');
            $author_surnames .= $row2['surname'] . '+';
        }
        $author_surnames = substr($author_surnames, 0, strlen($author_surnames) - 1);
        return $authors;
    }

    /**
     * According to types of annotations assigned to the publication returns:
     * 0 - if no annotation is assigned
     * 1 - if only global annotations are assigned and no annotation is owned by the current user
     * 2 - if at least one annotation assigned is owned by current user
     *
     * @param PEAR::DB $db
     * @param int $publication_id
     * @param int $submitter_id current user
     * @return int
     */
    public function annotation_level(&$db, $publication_id, $submitter_id) {
        $my = $db->getOne("SELECT COUNT(*)
		FROM `annotation` a JOIN `publication` p ON p.id = a.publication_id
		WHERE p.id=? and a.submitter_id=?", array($publication_id, $submitter_id));
        if (PEAR::isError($private))
            throw new SException($private->getMessage());
        if ($my != 0)
            return 2;
        $global = $db->getOne("SELECT COUNT(*)
		FROM `annotation` a JOIN `publication` p ON p.id = a.publication_id
		WHERE a.global_scope=1 and p.id=?", $publication_id);
        if (PEAR::isError($global))
            throw new SException($global->getMessage());
        if ($global != 0)
            return 1;
        return 0;
    }

    /**
     * Returns html representaion of the annotation
     *
     * @param PEAR::DB $db
     * @param int $publication_id
     * @param int $submitter_id current user
     * @return int
     */
    public function annotation_tag(&$db, $publication_id, $submitter_id) {
        $annot_level = annotation_level($db, $publication_id, $submitter_id);
        switch ($annot_level) {
            case 1: $annot = '<a href="index.php?pid=' . $publication_id . '&amp;tab=3"><img src="graphics/notes_g.gif" border="0" alt="annots" title="annotations" /></a>';
                break;
            case 2: $annot = '<a href="index.php?pid=' . $publication_id . '&amp;tab=3"><img src="graphics/notes_p.gif" border="0" alt="annots" title="my annotations" /></a>';
                break;
            default: $annot = '';
        }
        return $annot;
    }

    public function in_string($needle, $haystack, $insensitive = 0) {
        if ($insensitive) {
            return (false !== stristr($haystack, $needle)) ? true : false;
        } else {
            return (false !== strpos($haystack, $needle)) ? true : false;
        }
    }

    /**
     * Creates a string from array of integers.
     * Example: $arr = {1, 2, 3} will return string "(1, 2, 3)"
     *
     * @param int[] $arr array of integers
     * @return string
     */
    public function ids_clause($arr) {
        $ids = '';
        $count = count($arr) - 1;
        foreach ($arr as $v) {
            $ids .= (is_array($v) ? $v[0] : $v) . ($count-- ? ', ' : ''); //insert commas only between numbers
        }
        return $ids;
    }

    /**
     * Returns sql where condition from array column and operator OR
     *
     * @param array $arr array of ids
     * @param string $column name of database column

     * @return string
     */
    public function arr_condition($arr, $column) {
        $condition = '(';
        $count = count($arr) - 1;
        foreach ($arr as $v) {
            $condition .= "$column = '$v'" . ($count-- ? " OR " : '');
        }
        return $condition . ')';
    }

    /**
     * Return true if string ends with another string
     *
     * @param string $str main string
     * @param string $sub
     * @return bool
     */
    public function ends_with($str, $sub) {
        return (substr($str, strlen($str) - strlen($sub)) === $sub);
    }

    /**
     * Replace diacritic marks
     *
     * @param string $text
     * @return string
     */
    public function remove_diac($text) {
        $search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
        $replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
        $vt = str_replace($search, $replace, $text);
        return $vt;
    }

    /**
     * Escape strings if magic_quotes_gpc is off. If parameter $value is an array,
     * perform it recursive
     *
     * @param mixed $value
     * @return mixed cleaned object
     */
    public function clean_rec($value) {
        $value = is_array($value) ?
                array_map('clean_rec', $value) :
                trim(get_magic_quotes_gpc() ? $value : addslashes($value));
        return $value;
    }

    /**
     * Perform htmlspecialchars() on strings, if parameter $value is an array,
     * perform it recursive
     *
     * @param mixed $value
     * @return mixed cleaned object
     */
    public function htmlspecialchars_rec($value) {
        $value = is_array($value) ?
                array_map('htmlspecialchars_rec', $value) :
                trim(htmlspecialchars($value));
        return $value;
    }

    /**
     * Perform stripslashes() on strings, if parameter $value is an array,
     * perform it recursive
     *
     * @param mixed $value
     * @return mixed cleaned object
     */
    public function stripslashes_rec($value) {
        $value = is_array($value) ?
                array_map('stripslashes_rec', $value) :
                trim(stripslashes($value));
        return $value;
    }

    /**
     * Perform addslashes() on strings, if parameter $value is an array,
     * perform it recursive
     *
     * @param mixed $value
     * @return mixed cleaned object
     */
    public function addslashes_rec($value) {
        $value = is_array($value) ?
                array_map('addslashes_rec', $value) :
                trim(addslashes($value));
        return $value;
    }

    /**
     * returns ip of the current user
     *
     * @return string IP
     */
    public function get_ip() {
        global $_SERVER;
        $ret = $_SERVER["REMOTE_ADDR"];
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            if (($_SERVER["HTTP_X_FORWARDED_FOR"] != '') && ($_SERVER["HTTP_X_FORWARDED_FOR"] != '127.0.0.1'))
                $ret .='@' . $_SERVER["HTTP_X_FORWARDED_FOR"];
        return $ret;
    }

    /**
     * Delete author - deletion will succeed only if author is
     * not binded to a publication.
     *
     * @param PEAR::DB $db db object
     * @param int $id
     * @return int count of affected rows
     */
    public function delete_author(&$db, $id) {
        $sql = "DELETE FROM `author`
				WHERE id = '$id' AND '$id' NOT IN
				(SELECT DISTINCT author_id FROM `author_has_publication`)";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        return $db->affectedRows();
    }

    /**
     * Delete unbinded publisher
     *
     * @param PEAR::DB $db db object
     * @param int $id publisher id
     * @return int count of affected rows
     */
    public function delete_publisher(&$db, $id) {
        $sql = "DELETE FROM `publisher`
				WHERE id = '$id' AND '$id' NOT IN
				(SELECT DISTINCT publisher_id FROM `publication`)";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        return $db->affectedRows();
    }

    /**
     * Delete unbinded journal
     *
     * @param PEAR::DB $db db object
     * @param int $id journal id
     * @return int count of affected rows
     */
    public function delete_journal(&$db, $id) {
        $sql = "DELETE FROM `journal`
				WHERE id = '$id' AND '$id' NOT IN
				(SELECT DISTINCT journal_id FROM `publication`)";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        return $db->affectedRows();
    }

    /**
     * Delete unbinded conference2
     *
     * @param PEAR::DB $db db object
     * @param int $id conference id
     * @return int count of affected rows
     */
    public function delete_conference2(&$db, $id) {
        $sql = "DELETE FROM `conference2`
				WHERE id = '$id' ";

        $sql2 = "DELETE FROM `conference_year`
				WHERE conference2_id = '$id' ";

        $res = $db->query($sql);
        $res2 = $db->query($sql2);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        if (PEAR::isError($res2))
            throw new Exception($res2->getMessage());
        return $db->affectedRows();
    }

    /**
     * Delete unbinded conference2
     *
     * @param PEAR::DB $db db object
     * @param int $id conference id
     * @return int count of affected rows
     */
    public function delete_conference2_check(&$db, $id) {
        $sql = "DELETE FROM `conference2`
				WHERE id = '$id' AND '$id' NOT IN
				(SELECT DISTINCT conference2_id FROM `conference_year`)";

        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        return $db->affectedRows();
    }

    /**
     * Delete unbinded conference year
     *
     * @param PEAR::DB $db db object
     * @param int $id conference_year id
     * @return int count of affected rows
     */
    public function delete_conference_year(&$db, $id) {
        $sql = "DELETE FROM `conference_year`
				WHERE id = '$id'  ";


        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        return $db->affectedRows();
    }

    public function delete_conference_year_check(&$db, $id) {
        $sql = "DELETE FROM `conference_year`
				WHERE id = '$id'  AND '$id' NOT IN
				(SELECT DISTINCT conference_year_id FROM `publication` where conference_year_id IS NOT NULL)";


        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        return $db->affectedRows();
    }

    /**
     * Delete annotation
     *
     * @param PEAR::DB $db db object
     * @param int $id annotation id
     * @param int $submitter_id if this non-mandatory parameter is present, allow to delete only those
     * annotations which are owned by the submitter.
     * @return int count of affected rows
     */
    public function delete_annotation(&$db, $id, $submitter_id = null) {
        $sql = "DELETE FROM `annotation`
				WHERE id = '$id' " . (is_numeric($submitter_id) ?
                        "AND submitter_id = '$submitter_id'" : '');
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
        return $db->affectedRows();
    }

    /**
     * Add new author
     *
     * @param PEAR::DB $db db object
     * @param string $name
     * @param string $middlename
     * @param string $surname
     * @param int $submitter_id
     *
     * @return int author id
     */
    public function add_author(&$db, $name, $middlename, $surname, $submitter_id) {
        if (strlen($name) > 20 || !$name)
            throw new Exception("Length of author's name should be at least 1 and at most 20 characters");
        if (strlen($middlename) > 20)
            throw new Exception("Length of author's middlename should be at most 20 characters");
        if (strlen($surname) > 30 || !$surname)
            throw new Exception("Length of author's surname should be at least 1 and at most 30 characters");
        if (author_exists($db, $name, $middlename, $surname))
            throw new Exception("Author already exists" . $ppp);
        $sql = "INSERT INTO `author` VALUES ('', '$submitter_id', '$name', '$surname', '$middlename')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new SLException($res->getMessage());
        return last_insert_id($db);
    }

    /**
     * Edit author
     *
     * @param PEAR::DB $db db object
     * @param string $name
     * @param string $middlename
     * @param string $surname
     * @param int $submitter_id
     *
     * @return int author id
     */
    public function edit_author(&$db, $name, $middlename, $surname, $submitter_id) {
        if (strlen($name) > 20 || !$name)
            throw new Exception("Length of author's name should be at least 1 and at most 20 characters");
        if (strlen($middlename) > 20)
            throw new Exception("Length of author's middlename should be at most 20 characters");
        if (strlen($surname) > 20 || !$surname)
            throw new Exception("Length of author's surname should be at least 1 and at most 20 characters");
        if (author_exists($db, $name, $middlename, $surname))
            throw new Exception("Author already exists" . $ppp);
        $sql = "UPDATE `author` SET (name='') where (name='')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new SLException($res->getMessage());
        return last_insert_id($db);
    }

    /**
     * Checks if author exists
     *
     * @param PEAR::DB $db
     * @param string $name
     * @param string $middlename
     * @param string $surname
     * @return mixed id of author or false
     */
    public function author_exists(&$db, $name, $middlename, $surname) {
        if ($id = $db->getOne("SELECT id FROM `author`
		WHERE name='$name' AND middlename='$middlename' AND surname='$surname'"))
            return $id;
        else
            return false;
    }

    /**
     * Add new publisher
     *
     * @param PEAR::DB $db db object
     * @param string $name name of publisher
     * @param string $address address of publisher
     * @param int $submitter_id
     *
     * @return int publisher id
     */
    public function add_publisher(&$db, $name, $address, $submitter_id) {
        if (!$name)
            throw new Exception('Name of publisher is missing');
        if (strlen($name) > 500)
            throw new Exception('Name of publisher should have at most 500 characters');
        if (strlen($address) > 500)
            throw new Exception('Field address should have at most 500 chars');
        if (publisher_exists($db, $name))
            throw new Exception('Publisher already exists');

        $sql = "INSERT INTO `publisher` VALUES ('', '$submitter_id', '$name', '$address')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new SLException($res->getMessage());
        return last_insert_id($db);
    }

    /**
     * Update publisher
     *
     * @param PEAR::DB $db db object
     * @param int $publisher_id
     * @param string $name name of publisher
     * @param string $address address of publisher
     * @param int $submitter_id
     * @param bool $called_from_admin optional parameter indicates whether to allow changes of all fields including name and year regardless on the fact that this record is referenced
     * @param $publication_id id of currently edited publication [optional]
     *
     */
    public function update_publisher(&$db, $publisher_id, $name, $address, $submitter_id, $called_form_admin = false, $publication_id = null) {
        if (strlen($name) > 500)
            throw new Exception('Name of publisher should have at most 500 characters');
        if (strlen($address) > 500)
            throw new Exception('Field `address` should have at most 500 chars');
        if ($db->getOne("SELECT id FROM `publisher` WHERE name='$name' AND id<>'$publisher_id'"))
            throw new Exception('Publisher ' . $name . ' already exists');

        $used = $db->getOne("SELECT count(*)
		FROM `publication`
		WHERE publisher_id=?", $publisher_id);

        $puid = $db->getOne("SELECT publisher_id FROM publication WHERE id='$publication_id'");
        if (PEAR::isError($puid))
            throw new Exception($puid->getMessage());
        if ($used == 0 || ($used == 1 && $puid == $publisher_id))
            $allowed = true;

        if (!$used || $called_form_admin || $allowed)
            $an = "name='$name',";
        $res = & $db->query("UPDATE `publisher` SET
	$an
	address='$address',
	submitter_id='$submitter_id'
	WHERE id='$publisher_id'");
        if (PEAR::isError($res))
            throw new SAException($res->getMessage());
    }

    /**
     * Checks if publisher exists
     *
     * @param PEAR::DB $db
     * @param string $name
     * @return mixed id of publisher or false
     */
    public function publisher_exists(&$db, $name) {
        if ($id = $db->getOne("SELECT id FROM `publisher` WHERE name='$name'"))
            return $id;
        else
            return false;
    }

    /**
     * Add new conference
     *
     * @param PEAR::DB $db db object
     * @param string $name  name of the new conference
     * @param string $abbreviation
     * @param string $year
     * @param string $w_from
     * @param string $w_to
     * @param string $location location where the conference took place
     * @param string $isbn
     * @param int $submitter_id
     *
     * @return int conference id
     */
    public function add_conference(&$db, $name, $abbreviation, $year, $from, $to, $location, $isbn, $submitter_id) {
        if (!$name)
            throw new Exception('Conference name is missing!');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference name is 500 chars');
        if ($year && !valid_year($year))
            throw new Exception('Invalid year');
        if ($from && !valid_date($from))
            throw new Exception('Invalid `from` date');
        if ($to && !valid_date($to))
            throw new Exception('Invalid `to` date');
        if (!$year && $from)
            $year = substr($from, 0, 4);
        if (!$year && $to)
            $year = substr($from, 0, 4);
        //we must preserve the incomplete and also complete conferences to be checked for their existence in the db
        if (conference_exists($db, $name, $year, $submitter_id))
            throw new Exception('Conference already exists');
        $sql = "INSERT INTO `conference` (id, submitter_id, name, abbreviation, w_from, w_to, w_year, location, isbn)
		VALUES ('', '$submitter_id', '$name', '$abbreviation', '$from', '$to', '$year', '$location', '$isbn')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            die($res->getMessage());
        return last_insert_id($db);
    }

    /**
     * Add new conference2
     *
     * @param PEAR::DB $db db object
     * @param string $name  name of the new conference
     * @param string $abbreviation
     * @param int $submitter_id
     *
     * @return int conference2 id
     */
    public function add_conference2(&$db, $name, $abbreviation, $submitter_id, $description, $first_year) {
        if (!$name)
            throw new Exception('Conference2 name is missing!');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference2 name is 500 chars');
        //if (!$abbreviation)
        //	throw new Exception('Conference2 abbreviation is missing!');
        //if (!$description)
        //	throw new Exception('Conference2 description is missing!');
        //if (!$first_year)
        //	throw new Exception('Conference2 first_year is missing!');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference name is 500 chars');
        if (strlen($description) > 1000)
            throw new Exception('Maximal length of conference description is 1000 chars');

        if ($first_year && !valid_year($first_year))
            throw new Exception('Invalid year');

        //we must preserve the incomplete and also complete conferences to be checked for their existence in the db
        if (conference2_exists($db, $name, $abbreviation, $submitter_id))
            throw new Exception('Conference2 already exists');
        $sql = "INSERT INTO `conference2` (id, name, abbreviation, submitter_id, description, first_year)
		VALUES ('', '$name', '$abbreviation', '$submitter_id', '$description', '$first_year')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            die($res->getMessage());
        return last_insert_id($db);
    }

    /**
     * Add new conference year
     *
     * @param PEAR::DB $db db object
     * @param string $name  name of the new conference year
     * @param string $year
     * @param string $year_from
     * @param string $year_to
     * @param string $location
     * @param string $isbn
     * @param string $description
     * @param int $submitter_id
     *
     * @return int conference_year id
     */
    public function add_conference_year(&$db, $name, $conference2, $year, $year_from, $year_to, $location, $isbn, $description, $submitter_id) {
        if (!$name)
            throw new Exception('Conference year name is missing!');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference name is 500 chars');
        if (!$conference2)
            throw new Exception('Conference year conference2 id is missing!');
        if (!$year)
            throw new Exception('Conference year year is missing!');
        if (!$year && $from)
            $year = substr($from, 0, 4);
        if ($year && !valid_year($year))
            throw new Exception('Invalid year');
        //if (!$year_from)
        //	throw new Exception('Conference year year_from is missing!');
        if ($year_from && !valid_date($year_from))
            throw new Exception('Invalid `from` date');
        //if (!$year_to)
        //	throw new Exception('Conference year year_to is missing!');
        if ($year_to && !valid_date($year_to))
            throw new Exception('Invalid `to` date');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference name is 500 chars');
        if (strlen($description) > 1000)
            throw new Exception('Maximal length of conference description is 1000 chars');
        if ($year && $year_from && $year != substr($year_from, 0, 4))
            throw new Exception('Different year and year_from ');


        //we must preserve the incomplete and also complete conferences to be checked for their existence in the db
        if (conference_year_exists($db, $conference2, $name, $year, $submitter_id))
            throw new Exception('Conference_year already exists');
        $sql = "INSERT INTO `conference_year` (id, name, conference2_id, submitter_id,  w_year, w_from, w_to, location, isbn, description)
		VALUES ('', '$name', '$conference2', '$submitter_id', '$year',  '$year_from',  '$year_to',  '$location',  '$isbn',  '$description')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            die($res->getMessage());
        return last_insert_id($db);
    }

    public function add_conference_year_c(&$db, $name, $conference2, $year, $year_from, $year_to, $location, $isbn, $description, $publisher, $submitter_id) {
        if (!$name)
            throw new Exception('Conference year name is missing!');
        //if (!$publisher)
        //	throw new Exception('Publisher is missing!');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference name is 500 chars');
        if (!$conference2)
            throw new Exception('Conference year conference2 id is missing!');
        if (!$year)
            throw new Exception('Conference year year is missing!');
        if (!$year && $from)
            $year = substr($from, 0, 4);
        if ($year && !valid_year($year))
            throw new Exception('Invalid year');
        //if (!$year_from)
        //	throw new Exception('Conference year year_from is missing!');
        if ($year_from && !valid_date($year_from))
            throw new Exception('Invalid `from` date');
        //if (!$year_to)
        //	throw new Exception('Conference year year_to is missing!');
        if ($year_to && !valid_date($year_to))
            throw new Exception('Invalid `to` date');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference name is 500 chars');
        if (strlen($description) > 1000)
            throw new Exception('Maximal length of conference description is 1000 chars');
        if ($year && $year_from && $year != substr($year_from, 0, 4))
            throw new Exception('Different year and year_from ');


        //we must preserve the incomplete and also complete conferences to be checked for their existence in the db
        if (conference_year_exists($db, $conference2, $name, $year, $submitter_id))
            throw new Exception('Conference_year already exists');
        $sql = "INSERT INTO `conference_year` (id, name, conference2_id, submitter_id,  w_year, w_from, w_to, location, isbn, description, publisher_id)
		VALUES ('', '$name', '$conference2', '$submitter_id', '$year',  '$year_from',  '$year_to',  '$location',  '$isbn',  '$description', '$publisher')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            die($res->getMessage());
        return last_insert_id($db);
    }

    /**
     * Update conference 2
     *
     * @param PEAR::DB $db db object
     * @param int $conference2_id
     * @param string $name  name of the new conference
     * @param string $abbreviation
     * @param string $first_year
     * @param string $description description of the conference 
     * @param int $submitter_id
     * @param bool $called_from_admin optional parameter indicates whether to allow changes of all fields including name and year regardless on result of check_conference_editability public function
     * @param int $publication_id id of currently edited publication
     */
    public function update_conference2(&$db, $conference2_id, $name, $abbreviation, $first_year, $description, $submitter_id, $called_from_admin = false, $publication_id = null) {
        if (!$name)
            throw new Exception('Conference  name is missing!');
        if ($first_year && !valid_year($first_year))
            throw new Exception('Invalid year');

        //if (!$abbreviation)
        //	throw new Exception('Conference  year abbreviation is missing!');
        //if (!$first_year)
        //	throw new Exception('Conference  year first_year is missing!');
        //if (!$description)
        //	throw new Exception('Conference  year description is missing!');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference year name is 500 chars');
        if ($first_year && !valid_year($first_year))
            throw new Exception('Invalid year');
        //according to the level of editability permit or disable updationg of name and year
        $allow = check_conference2_editability($db, $conference2_id, $submitter_id, $publication_id);
        if ($called_from_admin) {
            $an = "name='$name',";
            $ay = "w_year='$first_year',";
        } else {
            switch ($allow) {
                //case 0: $an = $ay = ''; break;
                case 0: $an = "name='$name',";
                    ;
                    $ay = "first_year='$first_year',";
                    break;
                case 1: $an = "name='$name',";
                    ;
                    $ay = "first_year='$first_year',";
                    break;
                case 2:
                    if (!$name)
                        throw new Exception('Conference_year name is missing!');

                    $an = "name='$name',";
                    $ay = "first_year='$first_year',";
                    break;
            }
        }
        if ($an && $db->getOne("SELECT id FROM `conference2` WHERE name='$name' AND first_year='$first_year' AND id<>'$conference2_id'"))
            throw new Exception('Conference2 with name ' . $name . ' (' . $first_year . ') already exists');
        $res = & $db->query("UPDATE `conference2` SET
	$an
	abbreviation='$abbreviation',
	$ay
	description='$description',
	submitter_id='$submitter_id'
	WHERE id='$conference2_id'");
        if (PEAR::isError($res)) {
            throw new SAException($res->getMessage());
        }
    }

    /**
     * Update conference year
     *
     * @param PEAR::DB $db db object
     * @param int $conference_year_id
     * @param string $name  name of the new conference year
     * @param string $w_year
     * @param string $w_from
     * @param string $w_to
     * @param string $location
     * @param string $isbn
     * @param string $description description of the conference year 
     * @param int $submitter_id
     * @param bool $called_from_admin optional parameter indicates whether to allow changes of all fields including name and year regardless on result of check_conference_editability public function
     * @param int $publication_id id of currently edited publication
     */
    public function update_conference_year(&$db, $conference_year_id, $name, $w_year, $w_from, $w_to, $location, $isbn, $description, $submitter_id, $called_from_admin = false, $publication_id = null) {
        if (!$name)
            throw new Exception('Conference  year name is missing!');
        if (!$w_year)
            throw new Exception('Conference  year year is missing!');
        if ($w_year && !valid_year($w_year))
            throw new Exception('Invalid year');
        if ($w_from && !valid_date($w_from))
            throw new Exception('Invalid `from` date');
        if ($w_to && !valid_date($w_to))
            throw new Exception('Invalid `to` date');

        if ($w_from != '0000-00-00')
            if ($w_year && $w_from && $w_year != substr($w_from, 0, 4))
                throw new Exception('Different year and year_from ');
        if ($w_to != '0000-00-00')
            if ($w_year && $w_to && $w_year != substr($w_to, 0, 4))
                throw new Exception('Different year and year_to ');



        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference year name is 500 chars');
        //according to the level of editability permit or disable updationg of name and year
        $allow = check_conference_year_editability($db, $conference_year_id, $submitter_id, $publication_id);
        if ($called_from_admin) {
            $an = "name='$name',";
            $ay = "w_year='$w_year',";
        } else {
            switch ($allow) {
                case 0: $an = $ay = '';
                    break;
                case 1: $an = '';
                    $ay = "w_year='$w_year',";
                    break;
                case 2:
                    if (!$name)
                        throw new Exception('Conference_year name is missing!');
                    $an = "name='$name',";
                    $ay = "w_year='$w_year',";
                    break;
            }
        }
        if ($an && $db->getOne("SELECT id FROM `conference_year` WHERE name='$name' AND w_year='$w_year' AND id<>'$conference_year_id'"))
            throw new Exception('Conference year with name ' . $name . ' (' . $w_year . ') already exists');

        $res = & $db->query("UPDATE `conference_year` SET
	name='$name',
	w_year='$w_year',
	w_from='$w_from',
	w_to='$w_to',
	location='$location',
	isbn='$isbn',
	description='$description',

	submitter_id='$submitter_id'
	WHERE id='$conference_year_id'");
        if (PEAR::isError($res)) {
            throw new SAException($res->getMessage());
        }
    }

// update conference_year with publisher
    public function update_conference_year_p(&$db, $conference_year_id, $name, $w_year, $w_from, $w_to, $location, $isbn, $description, $publisher_id, $submitter_id, $called_from_admin = false, $publication_id = null) {
        if (!$name)
            throw new Exception('Conference  year name is missing!');
        if (!$w_year)
            throw new Exception('Conference  year year is missing!');
        if ($w_year && !valid_year($w_year))
            throw new Exception('Invalid year');
        if ($w_from && !valid_date($w_from))
            throw new Exception('Invalid `from` date');
        if ($w_to && !valid_date($w_to))
            throw new Exception('Invalid `to` date');

        if ($w_from != '0000-00-00')
            if ($w_year && $w_from && $w_year != substr($w_from, 0, 4))
                throw new Exception('Different year and year_from ');
        if ($w_to != '0000-00-00')
            if ($w_year && $w_to && $w_year != substr($w_to, 0, 4))
                throw new Exception('Different year and year_to ');



        if (strlen($name) > 500)
            throw new Exception('Maximal length of conference year name is 500 chars');
        //according to the level of editability permit or disable updationg of name and year
        $allow = check_conference_year_editability($db, $conference_year_id, $submitter_id, $publication_id);
        if ($called_from_admin) {
            $an = "name='$name',";
            $ay = "w_year='$w_year',";
        } else {
            switch ($allow) {
                case 0: $an = $ay = '';
                    break;
                case 1: $an = '';
                    $ay = "w_year='$w_year',";
                    break;
                case 2:
                    if (!$name)
                        throw new Exception('Conference_year name is missing!');
                    $an = "name='$name',";
                    $ay = "w_year='$w_year',";
                    break;
            }
        }
        if (!$publisher_id) {
            $publisher_id = 0;
        }

        if ($an && $db->getOne("SELECT id FROM `conference_year` WHERE name='$name' AND w_year='$w_year' AND id<>'$conference_year_id'"))
            throw new Exception('Conference year with name ' . $name . ' (' . $w_year . ') already exists');

        $res = & $db->query("UPDATE `conference_year` SET
	name='$name',
	w_year='$w_year',
	w_from='$w_from',
	w_to='$w_to',
	location='$location',
	isbn='$isbn',
	description='$description',
	publisher_id='$publisher_id',

	submitter_id='$submitter_id'
	WHERE id='$conference_year_id'");
        if (PEAR::isError($res)) {
            throw new SAException($res->getMessage());
        }

        //update publication with this conference_year
        $res = & $db->query("UPDATE `publication` SET
	isbn='$isbn',
	publisher_id='$publisher_id'
	WHERE conference_year_id='$conference_year_id'");
        if (PEAR::isError($res)) {
            throw new SAException($res->getMessage());
        }
    }

    /**
     * Checks if conference exists
     *
     * @param PEAR::DB $db
     * @param string $name
     * @param string $year
     * @param int $submitter_id
     * @return mixed id of conference or false
     */
    public function conference_exists(&$db, $name, $year, $submitter_id) {
        $p1 = $db->getOne("SELECT id FROM `conference`
			WHERE name='$name' AND w_year='' AND submitter_id='$submitter_id'");
        if (PEAR::isError($p1))
            die($p1->getMessage());
        $p2 = $db->getOne("SELECT id FROM `conference` WHERE name='$name' AND w_year='$year'");
        if (PEAR::isError($p2))
            die($p2->getMessage());
        if ($year && $p2)
            return $p2;
        if (!$year && $p1)
            return $p1;
        return false;
    }

    /**
     * Checks if conference2 exists
     *
     * @param PEAR::DB $db
     * @param string $name
     * @param string $first_year
     * @param int $submitter_id
     * @return mixed id of conference2 or false
     */
    public function conference2_exists(&$db, $name, $abbreviation, $submitter_id) {
        $p1 = $db->getOne("SELECT id FROM `conference2`
			WHERE name='$name' AND abbreviation=''");
        if (PEAR::isError($p1))
            die($p1->getMessage());
        $p2 = $db->getOne("SELECT id FROM `conference2` WHERE name='$name' AND abbreviation='$abbreviation'");
        if (PEAR::isError($p2))
            die($p2->getMessage());
        if ($abbreviation && $p2)
            return $p2;
        if (!$abbreviation && $p1)
            return $p1;
        return false;
    }

    /**
     * Checks if conference_year exists
     *
     * @param PEAR::DB $db
     * @param string $name
     * @param string $year
     * @param int $submitter_id
     * @return mixed id of conference_year or false
     */
    public function conference_year_exists(&$db, $conference2, $name, $year, $submitter_id) {
        $p1 = $db->getOne("SELECT id FROM `conference_year`
			WHERE (name='$name' AND submitter_id='$submitter_id' AND w_year='$year' AND conference2_id='$conference2')");

        if (PEAR::isError($p1))
            die($p1->getMessage());
        $p2 = $db->getOne("SELECT id FROM `conference_year` 
			WHERE name='$name'  AND conference2_id='$conference2'  AND  w_year='$year' ");
        if (PEAR::isError($p2))
            die($p2->getMessage());
        if ($year && $p2 && $conference2)
            return $p2;
        if ($p1 && $conference2)
            return $p1;
        return false;
    }

    /**
     * Returns level of conference2 editability:
     * level 0 - disallow editing of name and first_year
     * level 1 - disallow editing of name
     * level 2 - allow editing of all fields
     *
     * @param PEAR::DB $db
     * @param int $conference2_id
     * @param int $submitter_id
     * @param int $publication_id id of currently edited publication [optional]
     * @return int
     */
    public function check_conference2_editability(&$db, $conference2_id, $submitter_id, $publication_id = null) {
        $used = $db->getOne("SELECT count(*)
		FROM `conference_year`
	   	WHERE conference2_id=?", $conference2_id);

        //pokud neexistuje zadny rocnik teto konference, muzeme ji upravovat
        if ($used == 0)
            $allowed = 2; //allow editiong all including name and year

            
//pokud existuje nejaky rocnik teto konference, nemuzeme upravovat nazev ani prvni rocnik
        else {
            $allowed = 0;
        }
        return $allowed;
    }

    /**
     * Returns level of conference_year editability:
     * level 0 - disallow editing of name and year
     * level 1 - disallow editing of name
     * level 2 - allow editing of all fields
     *
     * @param PEAR::DB $db
     * @param int $conference_year_id
     * @param int $submitter_id
     * @param int $publication_id id of currently edited publication [optional]
     * @return int
     */
    public function check_conference_year_editability(&$db, $conference_year_id, $submitter_id, $publication_id = null) {
        $used = $db->getOne("SELECT count(*)
		FROM `publication`
	   	WHERE conference_year_id=?", $conference_year_id);

        // pokud neni prirazena zadne publikaci, muzeme upravovat
        if ($used == 0)
            $allowed = 2; //allow editiong all including name and year
        elseif ($used == 1 && is_numeric($publication_id)) {
            $cid = $db->getOne("SELECT conference_year_id FROM publication WHERE id='$publication_id'");
            if (PEAR::isError($cid))
                throw new Exception($cid->getMessage());
            if ($cid == $conference_year_id)
            //$allowed = 2;  //allow editiong all including name and year
                $allowed = 1;
        }
        else {
            //select conference_year identified by id only if it's non-complete and belongs to the current user
            $noncomplete_and_my = $db->getOne("SELECT id
			FROM `conference_year`
			WHERE id=? AND w_year='0000' AND submitter_id=?
			", array($conference_year_id, $submitter_id));
            if ($noncomplete_and_my)
                $allowed = 1; //allow editing year, but not name


                
// je-li prirazena publikaci a ja ji nevytvorila, nesmim menit jmeno ani prvni rok konference
            else
                $allowed = 0; //forbidd editing of name and year
        }
        return $allowed;
    }

    /**
     * Add new journal
     *
     * @param PEAR::DB $db db object
     * @param string $name name of journal
     * @param string $issn
     * @param int $submitter_id
     *
     * @return int journal id
     */
    public function add_journal(&$db, $name, $issn, $submitter_id) {
        if (!$name)
            throw new Exception('Name of journal is missing!');
        if ($issn && !valid_issn($issn))
            throw new Exception('ISSN has invalid format');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of journal name is 500 characters');
        if (journal_exists($db, $name, $issn))
            throw new Exception('Journal already exists');
        $sql = "INSERT INTO `journal` (id, submitter_id, name, issn)
		VALUES ('', '$submitter_id', '$name', '$issn')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            die($res->getMessage());
        return last_insert_id($db);
    }

    /**
     * Update journal
     *
     * @param PEAR::DB $db db object
     * @param $journal_id
     * @param string $name name of journal
     * @param string $issn
     * @param int $submitter_id
     * @param bool $called_from_admin optional parameter indicates whether to allow changes of all fields including name and year regardless on the fact that this record is referenced
     * @param int $publication_id id of currently edited publ.
     */
    public function update_journal(&$db, $journal_id, $name, $issn, $submitter_id, $called_form_admin = false, $publication_id = null) {
        if ($issn && !valid_issn($issn))
            throw new Exception('ISSN has invalid format');
        if (strlen($name) > 500)
            throw new Exception('Maximal length of journal name is 500 characters');
        if ($db->getOne("SELECT id FROM `journal` WHERE name='$name' AND issn='$issn' AND id<>'$journal_id'"))
            throw new Exception('Journal `' . $name . '` already exists');

        $used = $db->getOne("SELECT count(*)
		FROM `publication`
		WHERE journal_id=?", $journal_id);

        $jid = $db->getOne("SELECT journal_id FROM publication WHERE id='$publication_id'");
        if (PEAR::isError($cid))
            throw new Exception($jid->getMessage());
        if ($used == 0 || ($used == 1 && $jid == $journal_id))
            $allowed = true;

        if (!$used || $called_form_admin || $allowed)
            $an = "name='$name',";
        $res = & $db->query("UPDATE `journal` SET
	$an
	issn='$issn',
	submitter_id='$submitter_id'
	WHERE id='$journal_id'");
        if (PEAR::isError($res))
            throw new SAException($res->getMessage());
    }

    /**
     * Checks if journal exists
     *
     * @param PEAR::DB $db
     * @param string $name
     * @param string $issn
     * @return mixed id of journal or false
     */
    public function journal_exists(&$db, $name, $issn) {
        if ($id = $db->getOne("SELECT id FROM `journal` WHERE name='$name' OR (issn='$issn' AND issn <> '')")) {
            return $id;
        } else
            return false;
    }

    /**
     * Add new annotation to a publication
     *
     * @param PEAR::DB $db db object
     * @param int $publication_id
     * @param string $annotation
     * @param bool $scope
     * @param int $submitter_id
     */
    public function add_annotation(&$db, $publication_id, $annotation, $scope, $submitter_id) {
        $sql = "INSERT INTO `annotation` (id, publication_id, submitter_id, text, global_scope)
	VALUES ('', '$publication_id', '$submitter_id', '$annotation', '$scope')";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
    }

    /**
     * Creates a temporary information about new category
     *
     * @param string $name  name of the new added attribute
     * @param string $description  description of the new added attribute
     * @param string $value  text value of the attribute
     */
    public function add_tmp_category(&$db, $name) {
        if (strlen($name) > 255)
            throw new Exception('Category name is too long (max. 40 chars)');
        elseif (!$name)
            throw new Exception('Category name is missing');
        if ($db->getOne("SELECT id FROM `categories` WHERE name='$name'"))
            throw new Exception('Category ' . $name . ' already exists');
        if (!is_array($_SESSION['tmp_cat']))
            $_SESSION['tmp_cat'][] = $name;
        else
        if (!in_array($name, $_SESSION['tmp_cat']))
            $_SESSION['tmp_cat'][] = $name;
    }

    /**
     * Store (pdf, ps, ...) documents. Save their text content
     * into the database
     *
     * @param PEAR::DB
     * @param int $publication_id
     * @param string $title title of the publication
     * @return string info about document conversion
     */
    public function save_documents(&$db, $publication_id, $title) {
        $convertor = new Convertor($db, $publication_id, $title);
        foreach ($_FILES['documents']['error'] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                if (!file_exists("../storage/$publication_id"))
                    mkdir("../storage/$publication_id");
                $file = & $_FILES['documents']['tmp_name'][$key];
                $name = & $_FILES['documents']['name'][$key];
                $dst_filename = "../storage/$publication_id/$name";

                //save document file to filesystem
                $success = move_uploaded_file($file, $dst_filename);
                if (!$success)
                    throw new SLException('can not move uploaded file');
                $convertor->add_file($name);
            }
        }
        return $convertor->finish(); //return info about document conversion
    }

    /**
     * Updates annotation
     *
     * @param PEAR::DB $db db object
     * @param int $annotation_id
     * @param string $text
     * @param bool $scope
     * @param int $submitter_id
     */
    public function update_annotation(&$db, $annotation_id, $text, $submitter_id) {
        $sql = "UPDATE `annotation` SET
	text = '$text',
	submitter_id = '$submitter_id'
	WHERE id = '$annotation_id'";
        $res = $db->query($sql);
        if (PEAR::isError($res))
            throw new Exception($res->getMessage());
    }

    /**
     * Returns true if the annotation is owned by the user
     *
     * @param PEAR::DB $db
     * @param int $annotation_id
     * @param int $submitter_id
     * @return bool
     */
    public function annotation_is_owned(&$db, $annotation_id, $submitter_id) {
        $res = $db->getOne("SELECT id FROM `annotation` WHERE id = ? AND submitter_id = ?", array($annotation_id, $submitter_id));
        if ($res)
            return true;
        else
            return false;
    }

}
