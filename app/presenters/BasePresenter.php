<?php

namespace App\Presenters;

use App\Components\ButtonToggle\ButtonGroupComponent;
use Nette\Utils\Paginator;


class BasePresenter extends BasePresenterOld {

	/**
	 * global params used in application (contact email etc)
	 * @var \App\Model\GlobalParams @inject
	 */
	public $globalParams;

	/** @var \App\Model\Acl @inject */
	public $aclModel;

	/** @var \App\Model\Submitter @inject */
	public $submitterModel;

	/** @var \App\Model\UserRole */
	protected $userRoleModel;

	/** @var  \NasExt\Controls\ISortingControlFactory @inject */
	public $sortingControlFactory;

	/** @var \App\Model\Help @inject */
	public $helpModel;

	/** @var  bool */
	private $isConferencePartVisible;
	/** @var  bool */
	private $isPublicationPartVisible;


    /**
     *
     */
	protected function startup(): void
    {
		parent::startup();

		$this->template->records = null;
		$this->isConferencePartVisible = $this->user->isLoggedIn() ? $this->user->isAllowed('CU') : false;
		$this->isPublicationPartVisible = $this->user->isLoggedIn() ? $this->user->isAllowed('PU') : false;
		$this->template->CU = $this->isConferencePartVisible;
		$this->template->PU = $this->isPublicationPartVisible;

		$this->template->gp = $this->globalParams;

		$this->template->help = $this->helpModel->getHelp();
	}

    /**
     * @return bool
     */
	public function isCU(): bool
    {
        return $this->isConferencePartVisible;
    }

    /**
     * @return bool
     */
	public function isPU(): bool
    {
        return $this->isPublicationPartVisible;
    }

    /**
     * @return ButtonGroupComponent
     */
	public function createComponentCPToggle(): ButtonGroupComponent
    {
		$c = new ButtonGroupComponent([
			'all'   =>  [
				'caption'   =>  'All info',
				'icon'      =>  'asterisk'
			],
			'p'   =>  [
				'caption'   =>  'Publication-related info',
				'icon'      =>  'book'
			],
			'c'   =>  [
				'caption'   =>  'Conference info',
				'icon'      =>  'flag'
			]
		], 'all');
		$c->setAjaxRequest(true);
		$c->onActiveButtonChanged[] = function($buttonName) {
			$this->template->CPToggleState = $buttonName;
			$this->redrawControl('CPToggleHandler');
		};

		return $c;
	}

    /**
     * @param int $itemCount
     * @return \Nette\Utils\Paginator
     */
	public function setupPaginator(int $itemCount): Paginator
    {
		/** @var $vp \VisualPaginator */
		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = $this->itemsPerPageDB;
		$paginator->itemCount = $itemCount;
		return $paginator;
	}

    /**
     * @return \VisualPaginator
     */
	public function createComponentVp(): \VisualPaginator
    {
		return new \VisualPaginator();
	}

    /**
     * @return Paginator|null
     */
	public function setupRecordsPaginator(): ?Paginator
    {
		if (isset($this->records)) {
			$paginator = $this->setupPaginator($this->records->count("*"));
			$this->records = $this->records->limit($paginator->getLength(), $paginator->getOffset());
			return $paginator;
		}
		return null;
	}

    /**
     *
     */
	protected function resetPagination(): void
    {
		$this['vp']->page = 1;
		$this['vp']->getPaginator()->page = 1;
	}

    /**
     * @param string $message
     * @param string $type
     * @throws \Nette\Application\AbortException
     */
	public function finalFlashMessage(string $message, $type = 'info'): void
    {
		$this->flashMessage($message, $type);

		if ($this->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('flashMessages');
		}
	}

    /**
     * @param null $snippet
     * @param $redraw
     */
	public function redrawControl( $snippet = NULL, $redraw = TRUE)
    {
		parent::redrawControl('common');	// redraw common parts - block definitions etc.
		parent::redrawControl($snippet, $redraw);
	}

    /**
     * @param string $message
     * @param string $add_class
     * @return \stdClass
     */
	protected function successFlashMessage(string $message, string $add_class = ''): \stdClass
    {
        return $this->easyFlashMessage($message, 'alert-success', $add_class);
    }

    /**
     * @param string $message
     * @param string $add_class
     * @return \stdClass
     */
	protected function errorFlashMessage(string $message, string $add_class = ''): \stdClass
    {
        return $this->easyFlashMessage($message, 'alert-danger', $add_class);
    }

    /**
     * @param string $message
     * @param string $class
     * @param string $add_class
     * @return \stdClass
     */
	private function easyFlashMessage(string $message, string $class, string $add_class): \stdClass
    {
        $fm = $this->flashMessage($message, $class . ($add_class ? ' '.$add_class : ''));
        $this->redrawControl("flashMessages");
        return $fm;
    }

}
