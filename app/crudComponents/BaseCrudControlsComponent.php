<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 15.3.2015
 * Time: 20:30
 */

namespace App\CrudComponents;


class BaseCrudControlsComponent extends \Nette\Application\UI\Control {

	protected $recordId;
	protected $templateFile;
	protected $templateDeferredVars;
	protected $extraActionsAvailable;
	protected $uniqid;

	public function __construct($recordId, $uniqid, $templateFile, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->recordId = $recordId;
		$this->templateFile = $templateFile;

		$this->extraActionsAvailable = array();
		$this->templateDeferredVars = array();

		$this->uniqid = $uniqid;
	}

    /**
     * @param array|null $params
     */
	public function render(?array $params = []): void
    {
        foreach ($params as $key => $value) {
            $this->template->$key = $value;
        }

		$this->template->uniqid = $this->uniqid;
		$this->template->recordId = $this->recordId;
		$this->template->deleteLink = $this->getParent()->getParent()->link('delete!', array($this->recordId));
		$this->template->editLink = $this->getParent()->getParent()->link('edit!', array($this->recordId));

		foreach($this->templateDeferredVars as $k => $v){
			$this->template->$k = $v;
		}

		foreach($this->extraActionsAvailable as $action) {
			$tmp = $action . 'Link';
			$this->template->$tmp = $this->getParent()->getParent()->link($action . '!', array($this->recordId));
		}

		$this->template->setFile($this->templateFile);
		$this->template->render();
	}

	public function addActionAvailable($name){
		$this->extraActionsAvailable[] = $name;
	}

	public function addTemplateVars($vars) {
		$this->templateDeferredVars = array_merge($this->templateDeferredVars, $vars);
	}

	public function getRecordId() {
		return $this->recordId;
	}

}