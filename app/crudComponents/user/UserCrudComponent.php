<?php

namespace App\CrudComponents\User;

use App\Components\Publication\PublicationControl;
use App\CrudComponents\BaseCrudComponent;
use App\CrudComponents\BaseCrudControlsComponent;
use App\Helpers\Func;
use App\Model\Author;
use App\Services\Authenticators\BaseAuthenticator;


class UserCrudComponent extends BaseCrudComponent {

	/** @var  Callable[] */
	public $onMessage;

	/** @var \Nette\Security\User */
	protected $loggedUser;

	/** @var \App\Model\Submitter */
	protected $submitterModel;

	/** @var \App\Model\CuGroup */
	protected $cuGroupModel;

	/** @var \App\Model\SubmitterHasCuGroup */
	protected $submitterHasCuGroupModel;

	/** @var \App\Model\Acl */
	protected $aclModel;

	/** @var  \App\Model\Publication */
	protected $publicationModel;

	/** @var \App\Model\Author */
	protected $authorModel;

	/** @var  \App\Model\UserRole */
	protected $userRoleModel;

	/** @var  BaseAuthenticator */
	protected $baseAuthenticator;


    /**
     * UserCrudComponent constructor.
     * @param \Nette\Security\User $loggedUser
     * @param \App\Model\Acl $aclModel
     * @param \App\Model\CuGroup $cuGroupModel
     * @param \App\Model\SubmitterHasCuGroup $submitterHasCuGroupModel
     * @param \App\Model\Submitter $submitterModel
     * @param \App\Model\Publication $publicationModel
     * @param Author $authorModel
     * @param \App\Model\UserRole $userRoleModel
     * @param BaseAuthenticator $baseAuthenticator
     * @param \Nette\ComponentModel\IContainer|NULL $parent
     * @param null $name
     */
	function __construct(\Nette\Security\User $loggedUser,
                         \App\Model\Acl $aclModel,
						 \App\Model\CuGroup $cuGroupModel,
                         \App\Model\SubmitterHasCuGroup $submitterHasCuGroupModel,
						 \App\Model\Submitter $submitterModel,
                         \App\Model\Publication $publicationModel,
						 \App\Model\Author $authorModel,
						 \App\Model\UserRole $userRoleModel,
						 BaseAuthenticator $baseAuthenticator,
						 \Nette\ComponentModel\IContainer $parent = NULL,
                         $name = NULL)
    {
		parent::__construct($parent, $name);

		$this->aclModel = $aclModel;
		$this->cuGroupModel = $cuGroupModel;
		$this->loggedUser = $loggedUser;
		$this->submitterHasCuGroupModel = $submitterHasCuGroupModel;
		$this->submitterModel = $submitterModel;
		$this->publicationModel = $publicationModel;
		$this->authorModel = $authorModel;
		$this->userRoleModel = $userRoleModel;
		$this->baseAuthenticator = $baseAuthenticator;

		$this->onMessage = [];

		$this->onControlsCreate[] = function (BaseCrudControlsComponent &$controlsComponent) {
			$controlsComponent->addActionAvailable('showRelatedPublications');
		};
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleDelete(int $id): void
    {
		$record = $this->submitterModel->find($id);

		if ($record) {
			$record->toArray(); // load the object to be passed to the callback
			$this->submitterModel->deleteAssociatedRecords($id);

			$this->template->userDeleted = true;

			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('this');
			} else {
				$this->redrawControl('deleteUser');
			}

			$this->onDelete($record);
		}
	}

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleEdit(int $id): void
    {
		$submitter = $this->submitterModel->find($id);
		$form = $this['userEditForm'];

		// load cu groups
		$cu_groups_res = $this->submitterHasCuGroupModel->getAllByUserId($id);
		$cu_groups_ids = [];

		foreach ($cu_groups_res as $cu_group) {
			$cu_groups_ids[] = $cu_group->id;
		}

		$form['cu_groups']->setValue($cu_groups_ids);

		// load roles
		$roles = $this->userRoleModel->findAllByUserId($id);
		$roles_ids = [];

		foreach ($roles as $role) {
		    $roles_ids[] = $role->role;
        }
		$form['roles']->setValue($roles_ids);

		// load auth type
		$authMethod = $this->baseAuthenticator->getUserAuthenticationMethod($id);
		$form['auth_type']->setValue($authMethod);

		// load other values
		$form->setDefaults($submitter); // set up new values

		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('this');
		} else {
			$this->redrawControl('userEditForm');
		}
	}

    /**
     * @return array
     */
	protected function getAvailableRoles(): array
    {
		return $this->aclModel->getAvailableRoles();
	}

    /**
     * @return array
     */
	protected function getAvailableCuGroups(): array
    {
		$res = $this->cuGroupModel->findAll()->order('name ASC');
		$cuGroups = [];

		foreach ($res as $rec) {
		    $cuGroups[$rec->id] = $rec->name;
        }

		return $cuGroups;
	}

	protected function saveUserRelated($user_id, $cu_groups_ids, $roles, $auth_type)
    {
		// save cu groups
		$this->submitterHasCuGroupModel->getAllByUserId($user_id)->delete();
		$cu_group_insertion = array();

		foreach($cu_groups_ids as $cu_group_id) {
			$cu_group_insertion[] = array('submitter_id' => $user_id, 'cu_group_id' => $cu_group_id);
		}

		$this->submitterHasCuGroupModel->insertMulti($cu_group_insertion);

		// save roles
		$this->userRoleModel->setUserRoles($user_id, $roles);

		// auth type
		$this->baseAuthenticator->setUserAuthenticationMethod($user_id, $auth_type);
	}

    /**
     * @param string $name
     * @return UserAddForm
     */
	public function createComponentUserAddForm(string $name): UserAddForm
    {
        $form = new UserAddForm(
            $this->loggedUser,
            $this->getAvailableRoles(),
            $this->getAvailableCuGroups(),
            $this->baseAuthenticator->getAvailableAuthMethods(),
            $this->submitterModel,
            $this,
            $name
        );
        $this->reduceForm($form);

        $form->onValidate[] = function (UserAddForm $form) {
            if (count($form['roles']->value) === 0) {
                $form['roles']->addError('At least one role must be checked');
            }
        };

        $form->onError[] = function () {
            $this->redrawControl('userAddForm');
        };

        $form->onSuccess[] = function (UserAddForm $form) {
		    $formValues = $form->getValuesTransformed();

		    $cu_groups = Func::getAndUnset($formValues, 'cu_groups');
		    $roles = Func::getAndUnset($formValues, 'roles');
		    $auth_type = Func::getAndUnset($formValues, 'auth_type');

		    Func::valOrNull($formValues, 'email');
		    $newUserId = $this->baseAuthenticator->createNewUser($formValues['nickname'], BaseAuthenticator::DEFAULT_ROLE, $formValues['email'], $formValues['name'], $formValues['surname']);
		    $record = $this->submitterModel->find($newUserId);

		    if ($record) {
			    $this->saveUserRelated($record->id, $cu_groups, $roles, $auth_type);
			    $this->template->userAdded = true;

			    if ($this->presenter->isAjax()) {
				    $form->clearValues();
				    $this->redrawControl('userAddForm');
			    } else {
                    $this->redirect('this');
                }

			    $this->onAdd($record);
		    }
        };

        return $form;
	}

    /**
     * @param string $name
     * @return UserEditForm
     */
	public function createComponentUserEditForm(string $name): UserEditForm
    {
		$form = new UserEditForm(
		    $this->loggedUser,
            $this->getAvailableRoles(),
            $this->getAvailableCuGroups(),
            $this->baseAuthenticator->getAvailableAuthMethods(),
			$this->submitterModel,
            $this,
            $name
        );
		$this->reduceForm($form);

		$form->onValidate[] = function (UserEditForm $form) {
		    if (count($form['roles']->value) === 0) {
		        $form['roles']->addError('At least one role must be checked');
            }
        };

		$form->onError[] = function () {
			$this->redrawControl('userEditForm');
		};

		$form->onSuccess[] = function(UserEditForm $form) {
		    $formValues = $form->getValuesTransformed();

		    $cu_groups = Func::getAndUnset($formValues, 'cu_groups');
		    $roles = Func::getAndUnset($formValues, 'roles');
		    $auth_type = Func::getAndUnset($formValues, 'auth_type');

		    Func::valOrNull($formValues, 'email');

		    $this->submitterModel->update($formValues);

		    $this->saveUserRelated($formValues['id'], $cu_groups, $roles, $auth_type);

		    $record = $this->submitterModel->find($formValues['id']);

		    $this->template->userEdited = true;

		    if ($this->presenter->isAjax()) {
			    $this->redrawControl('userEditForm');
		    } else {
                $this->redirect('this');
		    }

		    $this->onEdit($record);
		};

		return $form;
	}


    /**
     * @return PublicationControl
     */
	public function createComponentPublication(): PublicationControl
    {
        return new PublicationControl();
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
	public function handleShowRelatedPublications(int $id): void
    {
        $publications = $this->publicationModel->getMultiplePubInfoByParams(['publication.submitter_id' => $id]);
        $authorsByPubId = [];

        foreach ($publications as $pub) {
            $authorsByPubId[$pub->id] = $this->authorModel->getAuthorsNamesByPubIdPure($pub->id);
        }

        $this->template->publicationsRelatedToUser = $publications;
        $this->template->authorsByPubId = $authorsByPubId;

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		} else {
			$this->redrawControl('publicationsRelatedToUser');
		}
	}

    /**
     * @param array|null $params
     */
	public function render(?array $params = []): void
    {
		$this->addDefaultTemplateVars([
			'userAdded'     =>  false,
			'userEdited'    =>  false,
			'userDeleted'   =>  false,
			"publicationsRelatedToUser" => []
		]);

		parent::render($params);
	}


}