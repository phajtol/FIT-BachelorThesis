<?php

namespace App\Model;

class Springer extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'springer';

    public function fetchData($id, $type, $pairs = true) {

        // 10.1007/11527695_15

        $radioArray = array();

        $generalSettings = $this->database->table('general_settings')->where(array('id' => 1))->fetch();

        $url = 'http://api.springer.com/metadata/json/' . $type . '/' . $id . '?api_key=' . $generalSettings->spring_token;
        // http://api.springer.com/metadata/json/doi/10.1007/11527695_15?api_key=318191e1dc228af0c36214a7b7aee84e

        $data = $this->curlDownload($url);

        if ($data) {
            $decodedData = json_decode($data);
            $decodedDataArr = json_decode($data, true);

            if (count($decodedData->records)) {

                if (!$pairs) {
                    return array('object' => $decodedData->records, 'array' => $decodedDataArr['records']);
                }
                foreach ($decodedData->records as $record) {

                    $radioArray[] = $record->title;
                    // 10.1007/BF01201962
                    /*
                      identifier => "doi:10.1007/11527695_15" (23)
                      url => array (1) [ ... ]
                      title => "Clause Form Conversions for Boolean Circuits" (44)
                      creators => array (2) [ ... ]
                      publicationName => "Theory and Applications of Satisfiability Testing" (49)
                      openaccess => "false" (5)
                      doi => "10.1007/11527695_15" (19)
                      printIsbn => "978-3-540-27829-0" (17)
                      electronicIsbn => "978-3-540-31580-3" (17)
                      isbn => "978-3-540-27829-0" (17)
                      publisher => "Springer" (8)
                      publicationDate => "2005-01-01" (10)
                      volume => ""
                      number => ""
                      startingPage => ""
                      copyright => "©2005 Springer-Verlag Berlin Heidelberg" (40)
                      genre => "OriginalPaper" (13)
                      abstract => "AbstractThe Boolean circuits is well established as a data structure for building propositional encodings of problems in preparation for satisfiabilit ... " (899)
                     */
                }
            }
            return $radioArray;
        }
        return false;
    }

    public function parseData($fields) {

        // $output['pub_type'] = $pub_type;

        if (isset($fields->genre)) {

            switch ($fields->genre) {
                case 'BookReview':
                    $output['pub_type'] = 'book';
                    break;
                case '':
                    break;
                case '':
                    break;
                default:
                    $output['pub_type'] = 'misc';
            }
        } else {
            $output['pub_type'] = 'misc';
        }

        if (isset($fields->title)) {
            $output['title'] = $fields->title;
        }

        if (isset($fields->booktitle)) {
            $output['booktitle'] = $fields->booktitle;
        }

        if (isset($fields->volume)) {
            $output['volume'] = $fields->volume;
        }

        if (isset($fields->number)) {
            $output['number'] = $fields->number;
        }

        if (isset($fields->chapter)) {
            $output['chapter'] = $fields->chapter;
        }

        if (isset($fields->pages)) {
            $output['pages'] = $fields->pages;
        }

        if (isset($fields->series)) {
            $output['series'] = $fields->series;
        }

        if (isset($fields->location)) {
            $output['location'] = $fields->location;
        }

        if (isset($fields->address)) { // neni to adresa publishera ????
            $output['address'] = $fields->address;
        }

        if (isset($fields->isbn)) {
            $output['isbn'] = $fields->isbn;
        }

        if (isset($fields->doi)) {
            $output['doi'] = $fields->doi;
        }

        if (isset($fields->note)) {
            $output['note'] = $fields->note;
        }

        if (isset($fields->url)) {
            $output['url'] = $fields->url[0]->value;
        }

        if (isset($fields->abstract)) {
            $output['abstract'] = $fields->abstract;
        }

        if (isset($fields->publicationDate)) {
            $output['issue_date'] = $fields->publicationDate;
        }

        if (isset($fields->publisher)) {
            $publisher_id = $this->database->table('publisher')->where('name', $fields->publisher)->fetch();
            if ($publisher_id) {
                $output['publisher_id'] = $publisher_id->id;
            }
        }

        if (isset($fields->creators)) {
            $output['creators'] = $fields->creators;
        }

        return $output;
    }

    public function curlDownload($Url) {

// is cURL installed yet?
        if (!function_exists('curl_init')) {
            die('Sorry cURL is not installed!');
        }

// OK cool - then let's create a new cURL resource handle
        $ch = curl_init();

// Now set some options (most are optional)
// Set URL to download
        curl_setopt($ch, CURLOPT_URL, $Url);

// Set a referer
        curl_setopt($ch, CURLOPT_REFERER, "http://www.example.org/yay.htm");

// User agent
        curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");

// Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);

// Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

// Download the given URL, and return output
        $output = curl_exec($ch);

// Close the cURL resource, and free system resources
        curl_close($ch);

        return $output;
    }

}
