<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 7.4.2015
 * Time: 19:43
 */

namespace App\Components\FavouriteConferenceToggle;


class FavouriteConferenceToggleComponent extends \Nette\Application\UI\Control {

	protected $ajaxRequest = true;

	/** @var  string */
	protected $templateFile;

	/** @var  \App\Model\SubmitterFavouriteConference */
	protected $submitterFavouriteConferenceModel;

	protected $userId;

	protected $conferenceId;

	protected $isFavourite = null;

	protected $isSmall = true;

	public $onMarkedAsFavourite = array();

	public function __construct(\App\Model\SubmitterFavouriteConference $submitterFavouriteConferenceModel, $userId, $conferenceId,
		\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {

		parent::__construct($parent, $name);

		$this->submitterFavouriteConferenceModel = $submitterFavouriteConferenceModel;
		$this->userId = $userId;
		$this->conferenceId = $conferenceId;

		$reflection = $this->getReflection();
		$dir = dirname($reflection->getFileName());
		$this->templateFile = $dir . DIRECTORY_SEPARATOR . $reflection->getShortName() . '.latte';

	}

	public function setIsFavourite($isFavourite) {
		$this->isFavourite = $isFavourite;
	}

	public function isFavourite(){
		if($this->isFavourite === null) {
			$this->isFavourite = $this->submitterFavouriteConferenceModel->isHisFavourite($this->userId, $this->conferenceId);
		}
		return $this->isFavourite;
	}

	public function render() {
		$isFavourite = $this->isFavourite();

		$this->template->isFavourite = $isFavourite;
		$this->template->setFavouriteLink = $this->link('setFavourite!', array($isFavourite ? false : true));
		$this->template->isSmall = $this->isSmall;
		$this->template->isFavourite = $this->isFavourite();
		$this->template->ajaxRequest = $this->ajaxRequest;

		$this->template->setFile($this->templateFile);
		$this->template->render();
	}

	public function handleSetFavourite($markAsFavourite){
		$markAsFavourite = $markAsFavourite ? true : false;

		if($markAsFavourite) {
			$this->submitterFavouriteConferenceModel->associateFavouriteConference($this->conferenceId, $this->userId);
		} else {
			$this->submitterFavouriteConferenceModel->detachFavouriteConference($this->conferenceId, $this->userId);
		}
		$this->onMarkedAsFavourite($markAsFavourite);
		$this->setIsFavourite($markAsFavourite);
		$this->redrawControl('button');
	}

	/**
	 * @param boolean $isSmall
	 */
	public function setIsSmall($isSmall) {
		$this->isSmall = $isSmall;
	}

	/**
	 * @return boolean
	 */
	public function isAjaxRequest() {
		return $this->ajaxRequest;
	}

	/**
	 * @param boolean $ajaxRequest
	 */
	public function setAjaxRequest($ajaxRequest) {
		$this->ajaxRequest = $ajaxRequest;
	}



}