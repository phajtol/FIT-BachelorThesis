<?php

namespace App\Helpers;

use Nette;


class ReferenceParser {

    use Nette\SmartObject;

    private $text;
    private $authors;
    private $title;
    private $year;

    private $status;


    public function __construct($text) {
        $this->text = $text;
    }


    public function parse() {
        $matches = preg_split("/(,|;)/", $this->text);
        $this->status = 0;
        $this->authors = [];


        if (count($matches)>1) {
            $this->parseMatches($matches);
        } elseif (strstr($this->text, "\"")) {
            $parts = explode("\"", $this->text);
            if (!empty($parts[0])) {
                $this->authors[] = trim(preg_replace("/[0-9\\.]{2,}/", "", $parts[0]));

            }
            if (!empty($parts[1])) {
                $this->title = trim($parts[1]);
            }
        } elseif (strstr($this->text, ".")) {
            $parts = explode(".", $this->text);
            $longest = "";
            $length = 0;
            foreach ($parts as $i => $part) {
                if (strlen($part)>$length) {
                    $longest = $part;
                    $length = strlen($part);
                }
            }
            $this->title = trim($longest);
            $this->authors[] = strstr($this->text, ".".$longest, true);
        }

        preg_match('|\d\d\d\d|', $this->text, $years);

        foreach ($years as $year) {
            if ($this->isYear($year)) {
                $this->year = intval($year);
            }
        }
        $authorsnew = [];
        $i = 0;
        while ($i<count($this->authors)) {
            $author = $this->authors[$i];
            if (strstr($author, " ")) {
                $authorsnew[] = $author;
                $i++;
                continue;
            } else {
                if (empty($this->authors[$i+1]) || strstr($this->authors[$i+1]," ")) {
                    $authorsnew[] = $author;
                    $i++;
                } else {
                    $authorsnew[] = $author.", ".$this->authors[$i+1];
                    $i += 2;
                }
            }
        }
        $this->authors = $authorsnew;
    }

    private function parseMatches($matches) {
        foreach ($matches as $match) {
            $str = $match;
            $str = preg_replace('|\[.*\]|',"", $str);
            $str = preg_replace('|[^a-zA-Z0-9\.\-\(\) ]|',' ',$str);
            $str = preg_replace('|\s+|',' ',$str);
            $str = trim($str);
            if (empty($str)) {
                continue;
            }
            if ($this->status==0) {
                if (substr($str, 0, 3)=="and") {
                    $str = trim(str_replace("and","", $str));
                }
                if (substr_count($str, " ")>2) {
                    if (strstr($str, " and ")) {
                        $matches2 = preg_split("/and/", $str);
                        $noname = false;
                        foreach ($matches2 as $match2) {
                            if (!$this->isName($match2)) {
                                $noname = true;
                            }
                        }
                        if (!$noname) {
                            foreach ($matches2 as $match2) {
                                $this->authors[] = trim($match2);
                            }
                        } else {
                            $this->status++;
                        }
                    } else {
                        $this->status++;
                    }
                } else {
                    if (!$this->isName($str)) {
                        $this->status++;
                    } else {
                        $this->authors[] = $str;
                        continue;
                    }
                }
            }
            if ($this->status==1) {
                $this->title = $str;
            }
            if (preg_match('|^\d\d\d\d$|',$str)) {
                $this->year = $str;
            }
            $this->status++;
        }
    }

    private function isName($str) {
        $names = preg_split("|\s+|",$str);
        $longwords=0;
        foreach ($names as $name) {
            if (strlen($name)>2) {
                $longwords++;
            }
        }
        if ($longwords>1) {
            return false;
        } else {
            return true;
        }
    }

    private function isYear($year) {
        $year = intval($year);
        if (($year>1900) && $year<date("Y")) {
            return true;
        }
        return false;

    }

    public function getAuthors() {
        return $this->authors   ;
    }


    public function getYear() {
        return $this->year;
    }

    public function getTitle() {
        return $this->title;
    }
}
