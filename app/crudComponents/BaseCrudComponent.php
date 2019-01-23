<?php

namespace App\CrudComponents;

use App\Components\BaseControl;
use App\Components\ButtonToggle\ButtonGroupComponent;
use App\Components\StaticContentComponent;
use Nette\Application\UI\Multiplier;
use Nette\Reflection\ClassType;

abstract class BaseCrudComponent extends BaseControl implements IBaseCrudComponent {

	/** @var array of callback functions - arguments passed: $record - newly created record */
	public $onAdd;

	/** @var */
	public $onDelete;

	/** @var */
	public $onEdit;

	/** @var array */
	protected $onControlsCreate = array();

	/** @var  array */
	protected $actionsAllowed = array('edit', 'delete', 'add');

	/** @var array actions that results in changing model */
	protected $writeActions = array('edit', 'delete', 'add');


	public function render(?array $params = []): void
    {
		parent::render();

		foreach ($params as $key => $value) {
		    $this->template->$key = $value;
        }

		$this->template->control = $this;
		$this->template->uniqid = $this->getUniqueId();

		$this->template->CU = $this->isCU();
		$this->template->PU = $this->isPU();

		$this->fillTemplateWithAllowedActions($this->template);

		$reflection = new ClassType($this);
		$this->template->setFile(dirname($reflection->fileName)  . '/modals.latte');
		$this->template->render();
	}

	/**
     * Returns true if the component should render the conference/publication part (usually based on roles belonging to current users)
     */
	protected function isCU(): bool
    {
        return $this->getPresenter(true)->isCU();
    }

	protected function isPU(): bool
    {
        return $this->getPresenter(true)->isPU();
    }

	/**
     * Reduces mixture form - depending on which sections has user access to
     * @param \App\Forms\IMixtureForm
     */
	protected function reduceForm(\App\Forms\IMixtureForm $f) {
		if (!$this->isCU()) {
		    $f->removeConferencePart();
        }
		if (!$this->isPU()) {
		    $f->removePublicationPart();
        }
	}

	/**
	 * This functions returns a Conference-Publication view toggle component. Because this component can be used anywhere in the application, it's contained in basecrudcomponent.
	 * @return \App\Components\ButtonToggle\ButtonGroupComponent
	 */
	protected function createComponentCPToggle(): ButtonGroupComponent
    {
        return $this->getPresenter(true)->createComponentCPToggle();
    }

	protected function fillTemplateWithAllowedActions(&$template): void
    {
		foreach($this->actionsAllowed as $actionAllowed) {
			$tmp = $actionAllowed . "Allowed";
			$template->$tmp = true;
		}
	}

	public function createComponentAddButton(): StaticContentComponent
    {
		$sc = new StaticContentComponent(
		    dirname($this->getReflection()->getFileName()) . '/add.latte',
            $this, 'addButton');
		$sc->template->uniqid = $this->getUniqueId();
		$this->fillTemplateWithAllowedActions($sc->template);
		return $sc;
	}

	public function createComponentControls(): Multiplier
    {
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

			foreach ($callbacks as $callback) {
				$callback($c);
			}
			return $c;
		});
	}

	public function allowAction($action): void
    {
		if (!in_array($action, $this->actionsAllowed)) {
		    $this->actionsAllowed[] = $action;
        }
	}

	public function disallowAction($action): void
    {
		$key = array_search($action, $this->actionsAllowed);
		if ($key !== false) {
			unset($this->actionsAllowed[$key]);
		}
	}

	public function isActionAllowed($action): bool
    {
		return in_array($action, $this->actionsAllowed);
	}

    /**
     * @param bool $ro
     */
	public function setReadOnly(bool $ro = true): void
    {
		if ($ro) {
			foreach ($this->writeActions as $writeAction) {
			    $this->disallowAction($writeAction);
            }
		} else {
			foreach ($this->writeActions as $writeAction) {
			    $this->allowAction($writeAction);
            }
		}
	}
}