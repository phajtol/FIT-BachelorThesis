<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

use App\Helpers\EndNoteRefWorksParser;

$container = require __DIR__ . '/../bootstrap.php';


class BibTexParserTest extends Tester\TestCase
{

	function __construct()
	{
	}


	function setUp()
	{
	}


	function testBibTexParser()
	{
            $parser = new \App\Helpers\BibTexParser('@inproceedings{2008,author = "Doe, John and Smith, Joseph",    booktitle = "Very big and important conference",    title = "Theory of everything",    year = "2009",    pages = "621--624"}');
            $parser->parse($pub_type, $fields, $authors);
            Assert::equal($pub_type, 'inproceedings');
            Assert::equal($authors, [['name' => 'John', 'middlename' => '', 'surname' => 'Doe'],
                                                ['name' => 'Joseph', 'middlename' => '', 'surname' => 'Smith']]);
            Assert::equal($fields['title'], 'Theory of everything');
            Assert::equal($fields['booktitle'], 'Very big and important conference');
            Assert::equal($fields['year'], '2009');
            Assert::equal($fields['pages'], '621-624');
        }

}


id(new BibTexParserTest())->run();

