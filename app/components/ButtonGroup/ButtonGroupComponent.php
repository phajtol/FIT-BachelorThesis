<?php

namespace App\Components\ButtonToggle;


class ButtonGroupComponent extends \App\Components\BaseControl {

	/**
	 * @persistent
	 */
	public $activeButton = null;

	/** @var  string */
	protected $templateFile;

	/** @var bool */
	protected $ajaxRequest;

	/** @var  string */
	protected $defaultActiveButton;

	/** @var array */
	protected $buttons;

	/** @var Callback[] */
	public $onActiveButtonChanged;


	/**
	 * @param array $buttons - array( BUTTON_NAME => ( array( caption => .., icon => .., type => .. ) ), .. )
	 * @param string $defaultActiveButton - default active button name (key of $buttons array)
	 * @param \Nette\ComponentModel\IContainer $parent
	 * @param string $name
	 */
	public function __construct(array $buttons, string $defaultActiveButton, \Nette\ComponentModel\IContainer $parent = NULL, string $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->ajaxRequest = false;

		$reflection = $this->getReflection();
		$dir = dirname($reflection->getFileName());
		$this->templateFile = $dir . DIRECTORY_SEPARATOR . $reflection->getShortName() . '.latte';

		$this->onActiveButtonChanged = [];

		$this->defaultActiveButton = $defaultActiveButton;
		$this->buttons = $buttons;

		if ($this->activeButton == null) {
		    $this->activeButton = $this->defaultActiveButton;
        }
	}

    /**
     * @return null|string
     */
	public function getActiveButtonName(): ?string
    {
		return $this->activeButton;
	}

	/**
	 * @param bool $value
	 * @return ButtonGroupComponent
	 */
	public function setAjaxRequest(bool $value = TRUE): ButtonGroupComponent
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

		foreach ($this->buttons as $buttonName => &$button) {
			if (!isset($button['type'])) {
			    $button['type'] = 'default';
			}

			if (isset($button['items'])) {
				$button['isCurrent'] = false;
				foreach ($button['items'] as $subbuttonName => &$subbutton) {
					$subbutton['isCurrent'] = (strval($subbuttonName) == strval($this->activeButton));
					if ($subbutton['isCurrent']) {
					    $button['isCurrent'] = true;
                    }
				}
			} else {
				$button['isCurrent'] = (strval($buttonName) == strval($this->activeButton));
			}
		}

		$this->template->buttons = $this->buttons;
		$this->template->activeButton = $this->activeButton;

		$this->template->render();
	}

    /**
     * @param string $buttonName
     */
	public function handleClick(string $buttonName): void
    {
		$buttonsFlatten = $this->getButtonsFlatten();

		if ($buttonName && (!isset($buttonsFlatten[$buttonName]) || isset($buttonsFlatten[$buttonName]['items']))) {
		    $buttonName = $this->defaultActiveButton;
        }

		if ($this->activeButton != $buttonName) {
            $this->onActiveButtonChanged($buttonName);
        }

		$this->activeButton = $buttonName;

		if ($this->presenter->isAjax()) {
			$this->redrawControl('buttons');
		}
	}

    /**
     * @return array
     */
	public function getButtonsFlatten(): array
    {
		$arr = [];

		foreach ($this->buttons as $buttonName => $button) {
			if (isset($button['items'])) {
				foreach ($button['items'] as $subbuttonName => $subbutton) {
				    $arr[$subbuttonName] = $subbutton;
                }
			} else {
				$arr[$buttonName] = $button;
			}
		}

		return $arr;
	}


}