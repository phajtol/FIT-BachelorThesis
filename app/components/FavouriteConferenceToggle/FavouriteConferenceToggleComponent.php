<?php

namespace App\Components\FavouriteConferenceToggle;


class FavouriteConferenceToggleComponent extends \Nette\Application\UI\Control {

    /** @var bool */
	protected $ajaxRequest = true;

	/** @var  string */
	protected $templateFile;

	/** @var  \App\Model\SubmitterFavouriteConference */
	protected $submitterFavouriteConferenceModel;

	/** @var int */
	protected $userId;

	/** @var int */
	protected $conferenceId;

	/** @var null */
	protected $isFavourite = null;

	/** @var bool */
	protected $isSmall = true;

	/** @var Callback[]  */
	public $onMarkedAsFavourite = [];



    /**
     * FavouriteConferenceToggleComponent constructor.
     * @param \App\Model\SubmitterFavouriteConference $submitterFavouriteConferenceModel
     * @param int $userId
     * @param int $conferenceId
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param string|NULL $name
     */
	public function __construct(\App\Model\SubmitterFavouriteConference $submitterFavouriteConferenceModel,
                                int $userId,
                                int $conferenceId,
		                        \Nette\ComponentModel\IContainer $parent = NULL,
                                string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->submitterFavouriteConferenceModel = $submitterFavouriteConferenceModel;
		$this->userId = $userId;
		$this->conferenceId = $conferenceId;

		$reflection = $this->getReflection();
		$dir = dirname($reflection->getFileName());
		$this->templateFile = $dir . DIRECTORY_SEPARATOR . $reflection->getShortName() . '.latte';
	}

    /**
     * @param bool $isFavourite
     */
	public function setIsFavourite(bool $isFavourite): void
    {
		$this->isFavourite = $isFavourite;
	}

    /**
     * @return bool|null
     */
	public function isFavourite(): ?bool
    {
		if ($this->isFavourite === null) {
			$this->isFavourite = $this->submitterFavouriteConferenceModel->isHisFavourite($this->userId, $this->conferenceId);
		}

		return $this->isFavourite;
	}

    /**
     * @throws \Nette\Application\UI\InvalidLinkException
     */
	public function render(): void
    {
		$isFavourite = $this->isFavourite();

		$this->template->isFavourite = $isFavourite;
		$this->template->setFavouriteLink = $this->link('setFavourite!', [$isFavourite ? false : true]);
		$this->template->isSmall = $this->isSmall;
		$this->template->isFavourite = $this->isFavourite();
		$this->template->ajaxRequest = $this->ajaxRequest;

		$this->template->setFile($this->templateFile);
		$this->template->render();
	}

    /**
     * @param $markAsFavourite
     */
	public function handleSetFavourite($markAsFavourite)
    {
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
	 * @param bool $isSmall
	 */
	public function setIsSmall(bool $isSmall) {
		$this->isSmall = $isSmall;
	}

	/**
	 * @return boolean
	 */
	public function isAjaxRequest(): bool
    {
		return $this->ajaxRequest;
	}

	/**
	 * @param boolean $ajaxRequest
	 */
	public function setAjaxRequest(bool $ajaxRequest): void
    {
		$this->ajaxRequest = $ajaxRequest;
	}



}