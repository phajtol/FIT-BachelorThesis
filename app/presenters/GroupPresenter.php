<?php

namespace App\Presenters;

use App\Components\ButtonToggle\ButtonGroupComponent;
use App\CrudComponents\Group\GroupCrud;
use App\Model;


class GroupPresenter extends SecuredPresenter {

    /** @var \App\Factories\IGroupCrudFactory @inject */
    public $groupCrudFactory;

    /** @var \Nette\Http\Request @inject */
    public $httpRequest;

    /** @var Model\Group @inject */
    public $groupModel;

    /** @var Model\SubmitterHasGroup @inject */
    public $submitterHasGroupModel;


    /**
     * @return \App\CrudComponents\Group\GroupCrud
     */
    public function createComponentCrud(): GroupCrud
    {
        $c = $this->groupCrudFactory->create();

        $c->onAdd[] = function ($row) {
            $this->successFlashMessage("Group has been added successfully");
            $this->redrawControl('groupShowAll');
        };

        $c->onDelete[] = function ($row) {
            $this->successFlashMessage("Group has been deleted successfully");
            $this->redrawControl('groupShowAll');
        };
        $c->onEdit[] = function ($row) {
            $this->successFlashMessage("Group has been edited successfully");
            $this->redrawControl('groupShowAllRecords');
        };

        return $c;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    protected function startup(): void
    {
        parent::startup();
    }

    /**
     *
     */
    public function actionDefault(): void
    {
        //wtf???
    }

    /**
     *
     */
    public function renderDefault(): void
    {
        //wtf??
    }

    /**
     * @param $sort
     * @param $order
     * @param $keywords
     * @param $filter
     */
    public function actionShowAll($sort, $order, $keywords, $filter): void
    {
        $this->drawAllowed = true;
    }

    /**
     * @param $starred
     */
    public function drawGroups($starred): void
    {
        if ($this->drawAllowed) {
            $params = $this->httpRequest->getQuery();
            $alphabet = range('A', 'Z');

            if (!isset($params['sort'])) {
                $params['sort'] = 'name';
            }

            if (!isset($params['order'])) {
                $params['order'] = 'ASC';
            }

            if (!isset($params['filter']) || $params['filter'] == 'none') {
                $params['filter'] = 'none';
            }

            if (!isset($params['keywords'])) {
                $params['keywords'] = '';
            }

            if (!isset($this->template->records)) {
                $this->records = $this->groupModel->findAllByKw($params);
                if($starred) $this->records->where(':submitter_has_group.submitter_id = ?', $this->user->id);
                $this->template->recordsStarred = [];

                $recordsStarredTemp = $this->submitterHasGroupModel->findAllBy(['submitter_has_group.submitter_id' => $this->user->id]);
                foreach ($recordsStarredTemp as $record) {
                    $this->template->recordsStarred[] = $record->group_id;
                }

                $vp = new \VisualPaginator();
                $this->addComponent($vp, 'vp');
                $paginator = $vp->getPaginator();
                $paginator->itemsPerPage = $this->itemsPerPageDB;
                $paginator->itemCount = $this->records->count("*");

                $this->records = $this->records->limit($paginator->getLength(), $paginator->getOffset());

                $this->template->records = $this->records;
                $this->data = $params;

                if (isset($params['sort'])) {
                    $this->template->sort = $params['sort'];
                } else {
                    $this->template->sort = null;
                }

                if (isset($params['order'])) {
                    $this->template->order = $params['order'];
                } else {
                    $this->template->order = null;
                }

                if (isset($params['keywords'])) {
                    $keywords = $params['keywords'];
                }

                if (isset($params['filter'])) {
                    $filter = $params['filter'];
                } else $filter = null;

                $params = [];

                if (isset($keywords)) {
                    $params['keywords'] = $keywords;
                }

                if (isset($filter)) {
                    $params['filter'] = $filter;
                }

                $this->template->filter = $filter;

                $this->template->alphabet = $alphabet;

                $this->template->params = $params;
            }

            $this->redrawControl('groupShowAll');
        }
    }

    /**
     *
     */
    public function renderShowAll(): void
    {
        if ($this->drawAllowed) {
            $this->drawGroups($this['individualFilter']->getActiveButtonName() == 'starred');
        }
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function handleSetFavouriteGroup(int $id): void
    {
        $this->submitterHasGroupModel->insert([
            'group_id' => $id,
            'submitter_id' => $this->user->id
        ]);

        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function handleUnsetFavouriteGroup(int $id): void
    {
        $record = $this->submitterHasGroupModel->findOneBy([
            'group_id' => $id,
            'submitter_id' => $this->user->id
        ]);

        if ($record) {
            $record->delete();
        }

        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }

    /**
     * @return ButtonGroupComponent
     */
    public function createComponentIndividualFilter(): ButtonGroupComponent
    {
        $c = new ButtonGroupComponent([
            'all'     =>  [
                'caption'   =>  'All groups',
                'icon'      =>  'list'
            ],
            'starred'      =>  [
                'caption'   =>  'Starred groups',
                'icon'      =>  'star'
            ],
        ], 'all');

        $c->onActiveButtonChanged[] = function () {
            //???
        };

        return $c;
    }
}
