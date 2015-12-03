<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 29.3.2015
 * Time: 17:42
 */

namespace App\Components\AlphabetFilter;

/**
 * @persistent
 */
class AlphabetFilterComponent extends \Nette\Application\UI\Control {

	/**
	 * @persistent
	 */
	public $filter = null;

	/** @var  string */
	protected $templateFile;

	/**
	 * @var boolean
	 */
	protected $ajaxRequest;

	/**
	 * @var Callback[]
	 */
	public $onFilter;

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->ajaxRequest = false;

		$reflection = $this->getReflection();
		$dir = dirname($reflection->getFileName());
		$this->templateFile = $dir . DIRECTORY_SEPARATOR . $reflection->getShortName() . '.latte';

		$this->onFilter = array();
	}


	protected function generateFilters() {
		$filters = array();
		$ch = ord('A');
		while($ch <= ord('Z')) {
			$filters[] = chr($ch);
			$ch++;
		}
		return $filters;
	}

	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @param bool $value
	 * @return AlphabetFilterComponent
	 */
	public function setAjaxRequest($value = TRUE)
	{
		$this->ajaxRequest = $value;
		return $this;
	}

	public function getAjaxRequest(){
		return $this->ajaxRequest;
	}

	public function render() {
		$this->template->ajaxRequest = $this->ajaxRequest;
		$this->template->setFile($this->templateFile);
		$this->template->filters = $this->generateFilters();
		$this->template->filter = $this->filter;
		$this->template->render();
	}

	public function handleFilter($filter) {
		if($filter && !in_array($filter, $this->generateFilters())) {
			new \Nette\InvalidArgumentException('Parameter "filter" has incorrent content!');
		}
		if(!$filter) $filter = null;

		$this->filter = $filter;
		$this->onFilter($filter);

		if($this->presenter->isAjax()) {
			$this->redrawControl('filter');
		}
	}


}