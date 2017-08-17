<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

use App\Helpers\EndNoteRefWorksParser;

$container = require __DIR__ . '/../bootstrap.php';


class EndNoteRefWorksParserTest extends Tester\TestCase
{

	function __construct()
	{
	}


	function setUp()
	{
	}


	function testEndNoteParser()
	{
            $parser = new EndNoteRefWorksParser("%0 Conference Paper
                %A John Doe
                %A Joseph Smith
                %T Theory of everything
                %B Very big and important conference
                %D 2009
                %P 621-624", "endnote");
            $parser->readLines();
            $fields = $parser->getFields();
            Assert::equal($fields['pub_type'], 'inproceedings');
            Assert::equal($fields['authors'], [['name' => 'John', 'middlename' => '', 'surname' => 'Doe'],
                                                ['name' => 'Joseph', 'middlename' => '', 'surname' => 'Smith']]);
            Assert::equal($fields['title'], 'Theory of everything');
            Assert::equal($fields['booktitle'], 'Very big and important conference');
            Assert::equal($fields['year'], '2009');
            Assert::equal($fields['pages'], '621-624');
        }
        
        function testRefWorksParser()
        {
            $parser = new EndNoteRefWorksParser("RT Conference Proceedings
                A1 Doe, John
                A1 Smith, Joseph
                T1 Theory of everything
                T2 Very big and important conference
                YR 2009
                SP 621
                OP 624", "refworks");
            $parser->readLines();
            $fields = $parser->getFields();
            Assert::equal($fields['pub_type'], 'inproceedings');
            Assert::equal($fields['authors'], [['name' => 'John', 'middlename' => '', 'surname' => 'Doe'],
                                                ['name' => 'Joseph', 'middlename' => '', 'surname' => 'Smith']]);
            Assert::equal($fields['title'], 'Theory of everything');
            Assert::equal($fields['booktitle'], 'Very big and important conference');
            Assert::equal($fields['year'], '2009');
            Assert::equal($fields['pages_start'], '621');
            Assert::equal($fields['pages_end'], '624');            
            
        }

}


id(new EndNoteRefWorksParserTest())->run();
