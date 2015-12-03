<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 12.3.2015
 * Time: 16:48
 */

namespace App\Presenters;


class BasePresenter extends BasePresenterOld {

	/**
	 * global params used in application (contact email etc)
	 * @var \App\Model\GlobalParams
	 */
	protected $globalParams;

	/** @var \App\Model\Acl */
	protected $aclModel;

	/** @var \App\Model\Submitter */
	protected $submitterModel;

	/** @var \App\Model\UserRole */
	protected $userRoleModel;

	/** @var  \NasExt\Controls\ISortingControlFactory */
	protected $sortingControlFactory;

	/** @var \App\Model\Help */
	protected $helpModel;

	/** @var  bool */
	private $isConferencePartVisible;
	/** @var  bool */
	private $isPublicationPartVisible;

	/** @param \App\Model\GlobalParams $globalParams */
	public function injectGlobalParams(\App\Model\GlobalParams $globalParams) { $this->globalParams = $globalParams; }

	/** @param \App\Model\Acl $aclModel */
	public function injectAclModel(\App\Model\Acl $aclModel) { $this->aclModel = $aclModel; }

	/** @param \App\Model\Submitter $submitterModel */
	public function injectSubmitterModel(\App\Model\Submitter $submitterModel) { $this->submitterModel = $submitterModel; }

	/** @param \App\Model\UserRole $userRoleModel */
	public function injectUserRoleModel(\App\Model\UserRole $userRoleModel) { $this->userRoleModel = $userRoleModel; }

	/** @param \NasExt\Controls\ISortingControlFactory $sortingControlFactory  */
	public function injectItemsPerPageFactory(\NasExt\Controls\ISortingControlFactory $sortingControlFactory) { $this->sortingControlFactory = $sortingControlFactory; }

	/** @param \App\Model\Help $helpModel */
	public function injectHelpModel(\App\Model\Help $helpModel) {
		$this->helpModel = $helpModel;
	}



	protected function startup() {
		parent::startup();

		\MultipleFileUpload\MultipleFileUpload::setQueuesModel(

		);

		$this->template->records = null;
		$this->isConferencePartVisible = $this->user->isLoggedIn() ? $this->user->isAllowed('CU') : false;
		$this->isPublicationPartVisible = $this->user->isLoggedIn() ? $this->user->isAllowed('PU') : false;
		$this->template->CU = $this->isConferencePartVisible;
		$this->template->PU = $this->isPublicationPartVisible;

		$this->template->gp = $this->globalParams;

		$this->template->help = $this->helpModel->getHelp();
	}

	public function isCU(){ return $this->isConferencePartVisible; }
	public function isPU(){ return $this->isPublicationPartVisible; }

	public function createComponentCPToggle(){
		$c = new \App\Components\ButtonToggle\ButtonGroupComponent(array(
			'all'   =>  array(
				'caption'   =>  'All info',
				'icon'      =>  'asterisk'
			),
			'p'   =>  array(
				'caption'   =>  'Publication-related info',
				'icon'      =>  'book'
			),
			'c'   =>  array(
				'caption'   =>  'Conference info',
				'icon'      =>  'flag'
			)
		), 'all');
		$c->setAjaxRequest(true);
		$c->onActiveButtonChanged[] = function($buttonName) {
			$this->template->CPToggleState = $buttonName;
			$this->redrawControl('CPToggleHandler');
		};
		return $c;
	}

	public function setupPaginator($itemCount) {
		/** @var $vp \VisualPaginator */
		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = $this->itemsPerPageDB;
		$paginator->itemCount = $itemCount;
		return $paginator;
	}

	public function createComponentVp(){
		return new \VisualPaginator();
	}

	public function setupRecordsPaginator() {
		if(isset($this->records)) {
			$paginator = $this->setupPaginator($this->records->count("*"));
			$this->records = $this->records->limit($paginator->getLength(), $paginator->getOffset());
			return $paginator;
		}
		return null;
	}

	protected function resetPagination(){
		$this['vp']->page = 1;
		$this['vp']->getPaginator()->page = 1;
	}

	public function finalFlashMessage($message, $type = 'info'){
		$this->flashMessage($message, $type);
		if($this->isAjax()){
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('flashMessages');
		}
	}



	public function redrawControl($snippet = NULL, $redraw = TRUE) {
		parent::redrawControl('common');	// redraw common parts - block definitions etc.
		parent::redrawControl($snippet, $redraw);
	}


	protected function successFlashMessage($message, $add_class = '') { return $this->easyFlashMessage($message, 'alert-success', $add_class); }
	protected function errorFlashMessage($message, $add_class = '') { return $this->easyFlashMessage($message, 'alert-danger', $add_class); }
	private function easyFlashMessage($message, $class, $add_class) { $fm = $this->flashMessage($message, $class . ($add_class ? ' '.$add_class : '')); $this->redrawControl("flashMessages"); return $fm; }

	use \Kdyby\Autowired\AutowireProperties;
	use \Kdyby\Autowired\AutowireComponentFactories;

}