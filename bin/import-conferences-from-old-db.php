<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 5.5.2015
 * Time: 12:47
 */

$oldDbCredentials = array(
	'host'      =>  'localhost',
	'port'      =>  3306,
	'user'      =>  'root',
	'pass'      =>  '',
	'dbname'    =>  'ak'
);

use App\Model\Conference;
use App\Model\ConferenceHasAcmCategory;
use App\Model\ConferenceHasCategory;
use App\Model\ConferenceYear;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Structure;


class CSVReporter implements IReporter {
	protected $rows = array();
	protected $header = array();

	public function addCaption($text) {
		$this->rows[] = $text;
	}

	public function setHeader($headerData) {
		$this->header = $headerData;
	}

	public function addRow($data) {
		$this->rows[] = $data;
	}

	public function render(){
		$out = !empty($this->header) ? $this->generateRow($this->header) : '';
		foreach($this->rows as $row) {
			if(is_array($row)) $out .= $this->generateRow($row);
			else $out .= $this->transformValue($row) . "\n";
		}
		return $out;
	}

	protected function generateRow($data){
		foreach($data as $k => &$v) $v = $this->transformValue($v);
		return implode(',', $data) . "\n";
	}
	protected function transformValue($v) { return '"' . str_replace('"', '""', $v) . '"'; }

}

interface IReporter {
	public function addCaption($text);
	public function setHeader($headerData);
	public function addRow($data);
	public function render();
}




$container = require __DIR__ . '/../app/bootstrap.php';
/**
 * @var $container \Nette\DI\Container
 */

$db = $container->getByType('\Nette\Database\Context');
/**
 * @var $db Nette\Database\Context
 */

$conferenceYearModel = $container->getService("ConferenceYear");
/**
 * @var $conferenceYearModel ConferenceYear
 */

$conferenceModel = $container->getService("Conference");
/**
 * @var $conferenceModel Conference
 */

$conferenceHasAcmCategoryModel = $container->getService("ConferenceHasAcmCategory");
/**
 * @var $conferenceHasAcmCategoryModel ConferenceHasAcmCategory
 */

$conferenceHasCategoryModel = $container->getService("ConferenceHasCategory");
/**
 * @var $conferenceHasCategoryModel ConferenceHasCategory
 */

$oldDbConn = new \Nette\Database\Connection('mysql:host='. $oldDbCredentials['host'] .';dbname=test;port=' . $oldDbCredentials['port'] . ';dbname=' . $oldDbCredentials['dbname'],
	$oldDbCredentials['user'], $oldDbCredentials['pass']);

$oldDb = new Nette\Database\Context($oldDbConn, new Structure($oldDbConn, new DevNullStorage()));

$oldConferenceTable = $oldDb->table('konference');
/** @var $oldConferences \Nette\Database\Table\Selection **/

$existingConferences = $conferenceModel->fetchAll();
$existingConferencesByAbbr = array();
$existingConferencesById = array();
foreach($existingConferences as $existingConference) $existingConferencesByAbbr[$existingConference->abbreviation] = $existingConference;
foreach($existingConferences as $existingConference) $existingConferencesById[$existingConference->id] = $existingConference;

$existingConferenceYears = $conferenceYearModel->fetchAll();
$existingConferenceYearsByAbbrYear = array();
foreach($existingConferenceYears as $existingConferenceYear) $existingConferenceYearsByAbbrYear[$existingConferenceYear->abbreviation . ';' . $existingConferenceYear->w_year] = $existingConferenceYear;

$oldConferenceYears = $oldConferenceTable->order('zkratka ASC')->fetchAll();

// ---
$acmCategoriesByConferenceId = array();
$customCategoriesByConferenceId = array();
$fnImportCategories = function($newConferenceId, $oldConferenceYearId) use ($oldDb, &$acmCategoriesByConferenceId, &$customCategoriesByConferenceId) {
	if(!isset($acmCategoriesByConferenceId[$newConferenceId])) $acmCategoriesByConferenceId[$newConferenceId] = array();
	if(!isset($customCategoriesByConferenceId[$newConferenceId])) $customCategoriesByConferenceId[$newConferenceId] = array();

	$acmCategories = array();
	$customCategories = array();
	
	$sel = $oldDb->table('konftema')->where(array('id_konf' => $oldConferenceYearId))->fetchAll();
	foreach($sel as $r) $acmCategories[] = $r->id_tema;

	$sel = $oldDb->table('kategoriekonf')->where(array('id_konf' => $oldConferenceYearId))->fetchAll();
	foreach($sel as $r) $customCategories[] = $r->id_kategorie;

	$acmCategoriesByConferenceId[$newConferenceId] = array_merge($acmCategoriesByConferenceId[$newConferenceId], $acmCategories);
	$customCategoriesByConferenceId[$newConferenceId] = array_merge($customCategoriesByConferenceId[$newConferenceId], $customCategories);
};

// ---
$createdConferences = array();
$createdConferencesByAbbr = array();
$createdConferencesById = array();
$attachedConferenceYears = array();
$mergedConferenceYears = array();
$createdConferenceYears = array();

$db->beginTransaction();

foreach($oldConferenceYears as $oldCY) {

	// checks if conference exists
	if(isset($existingConferencesByAbbr[$oldCY->zkratka])) {
		// conference already exists, it's great!

		// checks if conference year exists
		if(isset($existingConferenceYearsByAbbrYear[$oldCY->zkratka . ';' . $oldCY->rok])){
			// conference year already exists, let's merge it
			$existingConferenceYear = $existingConferenceYearsByAbbrYear[$oldCY->zkratka . ';' . $oldCY->rok];

			$newValues = getNewValuesFromOldCY($oldCY, $existingConferenceYear->conference_id);
			$updValues = array();

			foreach($newValues as $k => $v) {
				if(!$existingConferenceYear->$k && $v) $updValues[$k] = $v;
			}

			$updValues['id'] = $existingConferenceYear->id;

			$conferenceYearModel->update($updValues);

			$fnImportCategories($existingConferenceYear->conference_id, $oldCY->id_konf);

			$mergedConferenceYears[] = array(
				'conferenceYear'    =>  $conferenceYearModel->find($existingConferenceYear->id),
				'updatedValues'     =>  $updValues
			);

		} else {
			// conference year doesn't already exist yet, let's create new one
			$relatedConference = $existingConferencesByAbbr[$oldCY->zkratka];
			// insert the conference year
			$createdConferenceYear = $conferenceYearModel->insert(
				getNewValuesFromOldCY($oldCY, $relatedConference->id)
			);

			$fnImportCategories($relatedConference->id, $oldCY->id_konf);

			$attachedConferenceYears[] = $createdConferenceYear;
		}

	} else {

		if(!isset($createdConferencesByAbbr[$oldCY->zkratka])) {
			// conference doesn't exist at all, let's create new one

			$createdConference = $conferenceModel->insert(array(
				'name' => $oldCY->nazev,
				'abbreviation' => $oldCY->zkratka,
				'submitter_id' => NULL,
				'description' => NULL,
				'first_year' => NULL,
				'state' => 'alive'
			));

			$createdConferences[] = $createdConference;
			$createdConferencesByAbbr[$createdConference->abbreviation] = $createdConference;
			$createdConferencesById[$createdConference->id] = $createdConference;

			$relatedConference = $createdConference;
		} else {
			// conference has been imported yet
			$relatedConference = $createdConferencesByAbbr[$oldCY->zkratka];
		}

		// insert the conference year
		$createdConferenceYear = $conferenceYearModel->insert(
			getNewValuesFromOldCY($oldCY, $relatedConference->id)
		);

		$fnImportCategories($relatedConference->id, $oldCY->id_konf);

		$createdConferenceYears[] = $createdConferenceYear;
	}

}

// persist categories
foreach($acmCategoriesByConferenceId as $conferenceId => $acmCategories) {
	$acmCategories = array_unique($acmCategories);
	$preparedMulti = array();
	foreach($acmCategories as $acmCategory) $preparedMulti[] = array('conference_id' => $conferenceId, 'acm_category_id' => $acmCategory);
	$conferenceHasAcmCategoryModel->insertMulti($preparedMulti);
}
foreach($customCategoriesByConferenceId as $conferenceId => $categories) {
	$categories = array_unique($categories);
	$preparedMulti = array();
	foreach($categories as $category) $preparedMulti[] = array('conference_id' => $conferenceId, 'conference_category_id' => $category);
	$conferenceHasCategoryModel->insertMulti($preparedMulti);
}

$db->query("UPDATE `conference_year` SET w_from = null WHERE w_from = '0000-00-00'");
$db->query("UPDATE `conference_year` SET w_to = null WHERE w_to = '0000-00-00'");
$db->query("UPDATE `conference_year` SET deadline = null WHERE deadline = '0000-00-00'");
$db->query("UPDATE `conference_year` SET notification = null WHERE notification = '0000-00-00'");
$db->query("UPDATE `conference_year` SET finalversion = null WHERE finalversion = '0000-00-00'");
$db->query("UPDATE `conference_year` SET w_year = null WHERE w_year = '0000'");

$db->commit();


// generate summary
$txtSummary =
		sprintf("%d conferences have been created.", count($createdConferences)) . "\n"
	.   sprintf("%d conference years have been created (its conference has been newly created).", count($createdConferenceYears)) . "\n"
	.   sprintf("%d conference years have been associated with an existing conference.", count($attachedConferenceYears)) . "\n"
	.   sprintf("%d conference years have been merged with the existing ones.", count($mergedConferenceYears)) . "\n"
;

file_put_contents('import_summary.txt', $txtSummary);

// generate created conferences report
$createdConferencesReporter = new CSVReporter();
$createdConferencesReporter->setHeader(array('id', 'name', 'abbreviation'));
foreach($createdConferences as $createdConference) $createdConferencesReporter->addRow(array($createdConference->id, $createdConference->name, $createdConference->abbreviation));
file_put_contents('import_conferences_created.csv', $createdConferencesReporter->render());

// generate created conference years for created conferences
$createdCYsReporter = new CSVReporter();
$createdCYsReporter->setHeader(array('conference_id', 'conference_name', 'conference_abbreviation', 'cy_id', 'cy_name', 'cy_abbreviation', 'cy_year', 'cy_location', 'cy_from', 'cy_to', 'cy_web', 'cy_state'));
foreach($createdConferenceYears as $ctCY) {
	$createdCYsReporter->addRow(array(
		$createdConferencesById[$ctCY->conference_id]->id, $createdConferencesById[$ctCY->conference_id]->name, $createdConferencesById[$ctCY->conference_id]->abbreviation,
		$ctCY->id, $ctCY->name, $ctCY->abbreviation, $ctCY->w_year, $ctCY->location, $ctCY->w_from, $ctCY->w_to, $ctCY->web, $ctCY->state
	));
}
file_put_contents('import_conference_years_created.csv', $createdCYsReporter->render());


// generate associated conference
$associatedCYsReporter = new CSVReporter();
$associatedCYsReporter->setHeader(array('conference_id', 'conference_name', 'conference_abbreviation', 'cy_id', 'cy_name', 'cy_abbreviation', 'cy_year', 'cy_location', 'cy_from', 'cy_to', 'cy_web', 'cy_state'));
foreach($attachedConferenceYears as $atCY) {
	$associatedCYsReporter->addRow(array(
		$existingConferencesById[$atCY->conference_id]->id, $existingConferencesById[$atCY->conference_id]->name, $existingConferencesById[$atCY->conference_id]->abbreviation,
		$atCY->id, $atCY->name, $atCY->abbreviation, $atCY->w_year, $atCY->location, $atCY->w_from, $atCY->w_to, $atCY->web, $atCY->state
	));
}
file_put_contents('import_conference_years_attached.csv', $associatedCYsReporter->render());

// generate merged conference years report
$mergedCYsReporter = new CSVReporter();
$mergedCYsReporter->setHeader(array('conference_id', 'conference_name', 'conference_abbreviation', 'cy_id', 'cy_name', 'cy_abbreviation', 'cy_year', 'cy_location', 'cy_from', 'cy_to', 'cy_web', 'cy_state', 'transferred_columns'));
foreach($mergedConferenceYears as $mtRecord) {
	$mtCY = $mtRecord['conferenceYear'];
	$mtValues = array_keys($mtRecord['updatedValues']);
	$mergedCYsReporter->addRow(array(
		$existingConferencesById[$mtCY->conference_id]->id, $existingConferencesById[$mtCY->conference_id]->name, $existingConferencesById[$mtCY->conference_id]->abbreviation,
		$mtCY->id, $mtCY->name, $mtCY->abbreviation, $mtCY->w_year, $mtCY->location, $mtCY->w_from, $mtCY->w_to, $mtCY->web, $mtCY->state,
		implode(', ', $mtValues)
	));
}
file_put_contents('import_conference_years_merged.csv', $mergedCYsReporter->render());

echo $txtSummary;
exit;

function getNewValuesFromOldCY($oldCY, $conferenceYearId) {
	return array(
		'conference_id'     =>  $conferenceYearId,
		'parent_id'         =>  NULL,
		'submitter_id'      =>  NULL,
		'name'              =>  $oldCY->nazev,
		'abbreviation'      =>  $oldCY->zkratka,
		'w_year'            =>  $oldCY->rok,
		'w_from'            =>  ($oldCY->dat_zacatek && $oldCY->dat_zacatek != '0000-00-00') ? $oldCY->dat_zacatek : NULL,
		'w_to'              =>  ($oldCY->dat_konec && $oldCY->dat_konec != '0000-00-00') ? $oldCY->dat_konec : NULL,
		'deadline'          =>  ($oldCY->deadline && $oldCY->deadline != '0000-00-00') ? $oldCY->deadline : NULL,
		'notification'      =>  ($oldCY->notification && $oldCY->notification != '0000-00-00') ? $oldCY->notification : NULL,
		'finalversion'      =>  ($oldCY->finalversion && $oldCY->finalversion != '0000-00-00') ? $oldCY->finalversion : NULL,
		'location'          =>  ($oldCY->stat || $oldCY->mesto) ? ($oldCY->mesto . ($oldCY->stat && $oldCY->mesto ? ', ' : '') . $oldCY->stat) : NULL,
		'isbn'              =>  NULL,
		'description'       =>  $oldCY->popis ? $oldCY->popis : NULL,
		'publisher_id'      =>  NULL,
		'state'             =>  $oldCY->archiv ? 'archived' : 'alive',
		'doi'               =>  NULL,
		'issn'              =>  NULL,
		'web'               =>  $oldCY->homepage ? $oldCY->homepage : NULL
	);
}




?>
