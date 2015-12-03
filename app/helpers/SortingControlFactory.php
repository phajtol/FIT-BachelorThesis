<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 13.3.2015
 * Time: 19:26
 */

namespace App\Helpers;


class SortingControlFactory extends \Nette\Object implements \NasExt\Controls\ISortingControlFactory {

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
		$sc->templateFile =  dirname($this->getReflection()->getFileName()) . "/../templates/helpers/CustomSortingControl.latte";
		return $sc;
	}


}