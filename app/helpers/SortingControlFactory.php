<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 13.3.2015
 * Time: 19:26
 */

namespace App\Helpers;


use Nette\Reflection\ClassType;
use Nette\SmartObject;

class SortingControlFactory implements \NasExt\Controls\ISortingControlFactory {

    use SmartObject;

	/**
	 * @var \Nette\Http\IRequest
	 */
	protected $httpRequest;

	/**
	 * @var \Nette\Http\IResponse
	 */
	protected $httpResponse;

	function __construct(\Nette\Http\IRequest $httpRequest, \Nette\Http\IResponse $httpResponse)
	{
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
	}


	/**
	 * @param array $columns list of urlColumnName => originalColumnName
	 * @param string $defaultColumn
	 * @param string $defaultSort
	 * @return \NasExt\Controls\SortingControl
	 */
	public function create(array $columns, $defaultColumn, $defaultSort)
	{
		$sc = new \NasExt\Controls\SortingControl($columns, $defaultColumn, $defaultSort, $this->httpRequest, $this->httpResponse);
		$reflection = new ClassType($this);
		$sc->templateFile =  dirname($reflection->fileName) . "/../templates/helpers/CustomSortingControl.latte";
		return $sc;
	}


}