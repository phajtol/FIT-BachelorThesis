<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

use App\Helpers\ReferenceParser;

$container = require __DIR__ . '/../bootstrap.php';


class ReferenceParserTest extends Tester\TestCase
{

	function __construct()
	{
	}


	function setUp()
	{
	}


	function testParser()
	{
            $string = "L. O. Chua, “Memristor—The missing circuit element,” IEEE Trans. Circuit Theory, vol. 18, no. 5, pp. 507–519, Sep. 1971.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('L. O. Chua'));
            Assert::equal($parser->title, "Memristor The missing circuit element");
            Assert::equal($parser->year, 1971);
        }

        function testParser2() {
            $string = "L. O. Chua and S. M. Kang, “Memristive devices and systems,” Proc. IEEE, vol. 64, no. 2, pp. 209–223, Feb. 1976.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('L. O. Chua', 'S. M. Kang'));
            Assert::equal($parser->title, "Memristive devices and systems");
            Assert::equal($parser->year, 1976);
        }

        function testParser3() {
            $string = "E. Linn, R. Rosezin, C. Kugeler, and R. Waser, “Complementary resistive switches for passive nanocrossbar memories,” Nature Mater., vol. 9, no. 5, pp. 403–406, Apr. 2010.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('E. Linn','R. Rosezin','C. Kugeler', 'R. Waser'));
            Assert::equal($parser->title, "Complementary resistive switches for passive nanocrossbar memories");
            Assert::equal($parser->year, 2010);
        }

        function testParser4() {
            $string = "Simion, E.; Burciu, P., ”A view to SASEBO project,” in Electronics, Computers and Artificial Intelligence (ECAI), 2013 International Conference on , vol., no., pp.1-6, 27-29 June 2013 doi:10.1109/ECAI.2013.6636186";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('Simion, E.','Burciu, P.'));
            Assert::equal($parser->title, "A view to SASEBO project");
            Assert::equal($parser->year, 2013);
        }
        function testParser5() {
            $string = "Fischer, V.; Bernard, F.; Haddad, P., ”An open-source multi-FPGA modular system for fair benchmarking of True Random Number Generators,” in Field Programmable Logic and Applications (FPL), 201323rd International Conference on , vol., no., pp.1-4, 2-4 Sept. 2013 doi:10.1109/FPL.2013.6645570";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('Fischer, V.','Bernard, F.', 'Haddad, P.'));
            Assert::equal($parser->title, "An open-source multi-FPGA modular system for fair benchmarking of True Random Number Generators");
            Assert::equal($parser->year, 2013);
        }
        function testParser6() {
            $string = "Side-channel attack standard evaluation board (SASEBO), http://www.morita-tech.co.jp/SASEBO/en/index.html, Morita Tech. Co., Ltd. SASEBO Web Site.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array());
            Assert::equal($parser->title, "Side-channel attack standard evaluation board (SASEBO)");
            Assert::equal($parser->year, null);

        }
        function testParser7() {
            $string = "Developing Tamper Resistant Designs with Xilinx Virtex-6 and 7 Series FPGAs (XAPP1084), Xilinx [Online]. Available: http://tinyurl.com/p32ez9f";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array());
            Assert::equal($parser->title, "Developing Tamper Resistant Designs with Xilinx Virtex-6 and 7 Series FPGAs (XAPP1084)");
            Assert::equal($parser->year, null);
        }
        function testParser8() {
            $string = "Sugawara, T.; Homma, N.; Aoki, T.; Satoh, A., ”Differential power analysis of AES ASIC implementations with various S-box circuits,” in Circuit Theory and Design, 2009. ECCTD 2009. European Conference on , vol., no., pp.395-398, 23-27 Aug. 2009 doi: 10.1109/EC-CTD.2009.5275004";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('Sugawara, T.', 'Homma, N.', 'Aoki, T.', 'Satoh, A.'));
            Assert::equal($parser->title, "Differential power analysis of AES ASIC implementations with various S-box circuits");
            Assert::equal($parser->year, 2009);
        }
        function testParser9() {
            $string = "E. Ben-Sasson, A. Wigderson, Short proofs are narrow—resolution made simple, in: Proceedings of the 31st Annual ACM Symposium on Theory of Computing 1999, pp. 517–526.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('E. Ben-Sasson', 'A. Wigderson'));
            Assert::equal($parser->title, "Short proofs are narrow resolution made simple");
            Assert::equal($parser->year, 1999);
        }
        function testParser10() {
            $string = "J. V. Neumann, “Probabilistic logics and the synthesis of reliable organisms from unreliable components,” in Automata Studies, ed. C. E. Shannon and J. McCarthy, pp. 43–98, Princeton University Press, Princeton, 1956.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('J. V. Neumann'));
            Assert::equal($parser->title, "Probabilistic logics and the synthesis of reliable organisms from unreliable components");
            Assert::equal($parser->year, 1956);
        }

        function testParser11() {
            $string = "27. G.-J. Tromp \"Minimal test sets for combinational circuits\" Int. Test Conf. pp. 194-203 1991.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('G.-J. Tromp'));
            Assert::equal($parser->title, "Minimal test sets for combinational circuits");
            Assert::equal($parser->year, 1991);
        }

        function testParser12() {
            $string = "17. J. P. M. Silva J. C. Monteiro K. A. Sakallah \"Test Pattern Generation for Circuits Using Power Management Techniques\" Proceedings of the European Test Workshop 1997-May.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('J. P. M. Silva J. C. Monteiro K. A. Sakallah'));
            Assert::equal($parser->title, "Test Pattern Generation for Circuits Using Power Management Techniques");
            Assert::equal($parser->year, 1997);
        }

        function testParser13() {
            $string = "R. L. Ashenhurst. The decomposition of switching functions. Computation Lab Harvard University Vol. 29 pp.74-116 1959.";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('R. L. Ashenhurst'));
            Assert::equal($parser->title, "The decomposition of switching functions");
            Assert::equal($parser->year, 1959);
        }

        function testParser14() {
            $string = "Berkeley Logic Synthesis and Verification Group. ABC: A system for sequential synthesis and verification. http://www.eecs.berkeley.edu/alanmi/abc/";
            $parser = new \App\Helpers\ReferenceParser($string);
            $parser->parse();
            Assert::equal($parser->authors, array('Berkeley Logic Synthesis and Verification Group'));
            Assert::equal($parser->title, "ABC: A system for sequential synthesis and verification");
            Assert::equal($parser->year, null);
        }

}


id(new ReferenceParserTest())->run();
