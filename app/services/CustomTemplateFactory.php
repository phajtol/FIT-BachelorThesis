<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 12.3.2015
 * Time: 16:59
 */

namespace App\Services;


class CustomTemplateFactory extends \Nette\Bridges\ApplicationLatte\TemplateFactory {

	/**
	 * @var \App\Interfaces\IRoleTranslator
	 */
	protected $roleTranslator;

	/** @var  \App\Interfaces\IAuthMethodTranslator */
	protected $authMethodTranslator;

	/**
	 * @param \App\Interfaces\IRoleTranslator $roleTranslator
	 */
	public function setRoleTranslator(\App\Interfaces\IRoleTranslator $roleTranslator)
	{
		if(!$this->roleTranslator)
			$this->roleTranslator = $roleTranslator;
	}

	/**
	 * @param \App\Interfaces\IAuthMethodTranslator $authMethodTranslator
	 */
	public function setAuthMethodTranslator(\App\Interfaces\IAuthMethodTranslator $authMethodTranslator) {
		if(!$this->authMethodTranslator)
			$this->authMethodTranslator = $authMethodTranslator;
	}


	public function createTemplate(\Nette\Application\UI\Control $control = NULL)
	{
		if(!$this->roleTranslator) throw new \Exception("Role translator has not been set");
		if(!$this->authMethodTranslator) throw new \Exception("Auth method translator has not been set");

		$template = parent::createTemplate($control);

		if($template != null) {

			$template->addFilter("translateRole", function($roleId){
				return $this->roleTranslator->translateRole($roleId);
			});

			$template->addFilter("translateAuthMethod", function($authMethod) {
				return $this->authMethodTranslator->translateAuthMethod($authMethod);
			});

			$dateFormatFn = function(\DateTime $date = null) {
				if($date == null) return '-';
				return $date->format("j.n.Y");
			};

			$dateFormatWithoutYearFn = function(\DateTime $date = null) {
				if($date == null) return '-';
				return $date->format("j.n.");
			};

			$template->addFilter("ldate", $dateFormatFn);

			$template->addFilter("ldaterange", function(\DateTime $a = null, \DateTime $b = null) use ($dateFormatFn, $dateFormatWithoutYearFn) {
				if($a && $b) {
					if($a->format('j.n.Y') == $b->format('j.n.Y')) {
						return $dateFormatFn($a);
					} else {
						if($a->format('Y') != $b->format('Y'))
							return $dateFormatFn($a) . ' - ' . $dateFormatFn($b);
						else return $dateFormatWithoutYearFn($a) . ' - ' . $dateFormatFn($b);
					}
				} elseif($a) {
					return $dateFormatFn($a) . ' - ?';
				} elseif($b) {
					return '? - ' . $dateFormatFn($b);
				} else {
					return $dateFormatFn(null);
				}
			});


			$fltAuthorName = function($author) {
				return $author->surname . ", " . $author->name . ($author->middlename ? ", " . $author->middlename : "");
			};

			$template->addFilter("authorName", function($author) use ($fltAuthorName) {
				return $fltAuthorName($author);
			});

			$template->addFilter("authors", function($authors) use ($fltAuthorName) {
				if(!is_array($authors)) $authors = [$authors];
				$names = [];
				foreach($authors as $author) $names[] = $fltAuthorName($author);
				return implode('; ', $names);
			});
		}

		return $template;
	}


}