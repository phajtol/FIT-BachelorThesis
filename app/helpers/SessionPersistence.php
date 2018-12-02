<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 2.6.2015
 * Time: 18:56
 */

namespace App\Helpers;


use Nette\Application\UI\PresenterComponentReflection;

trait SessionPersistence {

	protected $__SESSION_SECTION = 'SessPers';


    /**
     * Saves state information for all subcomponents to $this->globalState.
     * @return array
     * @throws \ReflectionException
     */
	protected function getGlobalState($forClass = NULL)
	{
		static $sinces = array();

		if ($this->globalState === NULL && isset($this->session->getSection($this->__SESSION_SECTION)->data)) {
			$state = parent::getGlobalState($forClass);
			foreach ($this->session->getSection($this->__SESSION_SECTION)->data as $k => $v) {
				if(!array_key_exists($k, $state))
					$state[$k] = $v;
			}
			$this->saveState($state, $forClass ? new PresenterComponentReflection($forClass) : NULL);

			if ($sinces === NULL) {
				$sinces = array();
				foreach ($this->getReflection()->getPersistentParams() as $name => $meta) {
					$sinces[$name] = $meta['since'];
				}
			}

			$components = $this->getReflection()->getPersistentComponents();
			$iterator = $this->getComponents(TRUE, 'Nette\Application\UI\IStatePersistent');

			foreach ($iterator as $name => $component) {
				if ($iterator->getDepth() === 0) {
					// counts with Nette\Application\RecursiveIteratorIterator::SELF_FIRST
					$since = isset($components[$name]['since']) ? $components[$name]['since'] : FALSE; // FALSE = nonpersistent
				}
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				$params = array();
				$component->saveState($params);
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
					$sinces[$prefix . $key] = $since;
				}
			}

		} else {
			return parent::getGlobalState($forClass);
		}

		return $state;
	}

	protected function shutdown($response) {
		parent::shutdown($response);

		$this->session->getSection($this->__SESSION_SECTION)->data = $this->getGlobalState();

		//var_dump( $this->getGlobalState());
	}

	/**
	 * Saves state informations for next request.
	 * @param  array
	 * @param  PresenterComponentReflection (internal, used by Presenter)
	 * @return void
	 */
	public function saveState(array & $params, $reflection = NULL)
	{
		$reflection = $reflection === NULL ? $this->getReflection() : $reflection;
		foreach ($reflection->getPersistentParams() as $name => $meta) {

			if (isset($params[$name])) {
				// injected value

			} elseif (!isset($meta['since']) || $this instanceof $meta['since']) {
				$params[$name] = $this->$name; // object property value

			} else {
				continue; // ignored parameter
			}

			$type = gettype($meta['def']);
			if (!PresenterComponentReflection::convertType($params[$name], $type)) {
				throw new InvalidLinkException(sprintf("Invalid value for persistent parameter '%s' in '%s', expected %s.", $name, $this->getName(), $type === 'NULL' ? 'scalar' : $type));
			}

			if ($params[$name] === $meta['def'] || ($meta['def'] === NULL && is_scalar($params[$name]) && (string) $params[$name] === '')) {
				$params[$name] = NULL; // value transmit is unnecessary
			}
		}
	}

}