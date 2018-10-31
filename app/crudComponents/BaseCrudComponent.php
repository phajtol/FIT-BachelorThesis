<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 14.3.2015
 * Time: 23:03
 */

namespace App\CrudComponents;


use App\Components\BaseControl;
use Nette\Application\UI\Multiplier;
use Nette\Reflection\ClassType;

abstract class BaseCrudComponent extends BaseControl implements IBaseCrudComponent {

	/** @var array of callback functions - arguments passed: $record - newly created record */
	public $onAdd;

	/** @var array */
	public $onDelete;

	/** @var array */
	public $onEdit;

	/** @var array */
	protected $onControlsCreate = array();

	/** @var  array */
	protected $actionsAllowed = array('edit', 'delete', 'add');

	/** @var array actions that results in changing model */
	protected $writeActions = array('edit', 'delete', 'add');

	public function render(){
		parent::render();

		$this->template->control = $this;
		$this->template->uniqid = $this->getUniqueId();

		$this->template->CU = $this->isCU();
		$this->template->PU = $this->isPU();

		$this->fillTemplateWithAllowedActions($this->template);

		$reflection = new ClassType($this);
		$this->template->setFile(dirname($reflection->fileName)  . '/modals.latte');
		$this->template->render();
	}

	/** Returns true if the component should render the conference/publication part (usually based on roles belonging to current users) */
	protected function isCU() { return $this->getPresenter(true)->isCU(); }
	protected function isPU() { return $this->getPresenter(true)->isPU(); }
	/** Reduces mixture form - depending on which sections has user access to */
	protected function reduceForm(\App\Forms\IMixtureForm $f) {
		if(!$this->isCU()) $f->removeConferencePart();
		if(!$this->isPU()) $f->removePublicationPart();
	}

	/**
	 * This functions returns a Conference-Publication view toggle component. Because this component can be used anywhere in the application, it's contained in basecrudcomponent.
	 * @return \App\Components\ButtonToggle\ButtonGroupComponent
	 */
	protected function createComponentCPToggle() { return $this->getPresenter(true)->createComponentCPToggle(); }

	protected function fillTemplateWithAllowedActions(&$template) {
		foreach($this->actionsAllowed as $actionAllowed) {
			$tmp = $actionAllowed . "Allowed";
			$template->$tmp = true;
		}
	}

	public function createComponentAddButton(){
		$sc = new \App\Components\StaticContentComponent(
			dirname($this->getReflection()->getFileName()) . '/add.latte',
			$this, 'addButton'
		);
		$sc->template->uniqid = $this->getUniqueId();
		$this->fillTemplateWithAllowedActions($sc->template);
		return $sc;
	}

	public function createComponentControls(){
		$parent = $this;
		$reflection = new ClassType($this);
		$templateFile = dirname($reflection->fileName) . '/controls.latte';

		$callbacks = $this->onControlsCreate;

		return new Multiplier(function ($recordId) use ($parent, $templateFile, $callbacks) {
			$c = new BaseCrudControlsComponent($recordId, $this->getUniqueId(), $templateFile);

			// fill controls template with actions allowed
			$tmp = new \stdClass();
			$this->fillTemplateWithAllowedActions($tmp);
			$c->addTemplateVars(get_object_vars($tmp));

			foreach($callbacks as $callback) {
				$callback($c);
			}
			return $c;
		});
	}

	public function allowAction($action) {
		if(!in_array($action, $this->actionsAllowed)) $this->actionsAllowed[] = $action;
	}

	public function disallowAction($action) {
		$key = array_search($action,$this->actionsAllowed);
		if($key!==false){
			unset($this->actionsAllowed[$key]);
		}
	}

	public function isActionAllowed($action) {
		return in_array($action, $this->actionsAllowed);
	}

	public function setReadOnly($ro = true) {
		if($ro) {
			foreach($this->writeActions as $writeAction) $this->disallowAction($writeAction);
		} else {
			foreach($this->writeActions as $writeAction) $this->allowAction($writeAction);
		}
	}
}