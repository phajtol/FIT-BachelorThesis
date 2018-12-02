<?php

namespace App\Components\AlphabetFilter;

use Nette\Reflection\ClassType;

/**
 * @persistent
 */
class AlphabetFilterComponent extends \Nette\Application\UI\Control {

	/** @persistent */
	public $filter = null;

	/** @var  string */
	protected $templateFile;

	/** @var bool */
	protected $ajaxRequest;

	/** @var Callback[] */
	public $onFilter;



    /**
     * AlphabetFilterComponent constructor.
     * @param \Nette\ComponentModel\IContainer|null $parent
     * @param null|string $name
     * @throws \ReflectionException
     */
    public function __construct(?\Nette\ComponentModel\IContainer $parent = NULL, ?string $name = NULL)
    {
		parent::__construct();

		$this->ajaxRequest = false;

		$reflection = new ClassType($this);
		$dir = dirname($reflection->fileName);
		$this->templateFile = $dir . DIRECTORY_SEPARATOR . $reflection->shortName . '.latte';

		$this->onFilter = [];
	}

    /**
     * @return array
     */
	protected function generateFilters(): array
    {
		$filters = [];
		$ch = ord('A');

		while($ch <= ord('Z')) {
			$filters[] = chr($ch);
			$ch++;
		}

		return $filters;
	}

    /**
     * @return null
     */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @param bool $value
	 * @return AlphabetFilterComponent
	 */
	public function setAjaxRequest(bool $value = TRUE): AlphabetFilterComponent
	{
		$this->ajaxRequest = $value;
		return $this;
	}

    /**
     * @return bool
     */
	public function getAjaxRequest(): bool
    {
		return $this->ajaxRequest;
	}

    /**
     *
     */
	public function render(): void
    {
		$this->template->ajaxRequest = $this->ajaxRequest;
		$this->template->setFile($this->templateFile);
		$this->template->filters = $this->generateFilters();
		$this->template->filter = $this->filter;
		$this->template->render();
	}

    /**
     * @param $filter
     */
	public function handleFilter($filter): void
    {
		if ($filter && !in_array($filter, $this->generateFilters())) {
			new \Nette\InvalidArgumentException('Parameter "filter" has incorrent content!');
		}
		if (!$filter) {
		    $filter = null;
        }

		$this->filter = $filter;
		$this->onFilter($filter);

		if ($this->presenter->isAjax()) {
			$this->redrawControl('filter');
		}
	}


}