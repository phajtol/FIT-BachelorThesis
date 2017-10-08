<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \VisualPaginator,
    Nette\Diagnostics\Debugger;

class GroupPresenter extends SecuredPresenter {

    /** @var Nette\Database\Context */
    private $database;

    /** @var \App\Factories\IGroupCrudFactory */
    protected $groupCrudFactory;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * @param \App\Factories\IGroupCrudFactory $groupCrudFactory
     */
    public function injectGroupCrudFactory(\App\Factories\IGroupCrudFactory $groupCrudFactory) {
        $this->groupCrudFactory = $groupCrudFactory;
    }


    public function createComponentCrud(){
        $c = $this->groupCrudFactory->create();

        $c->onAdd[] = function($row){
            $this->successFlashMessage("Group has been added successfully");
            $this->redrawControl('groupShowAll');
        };
        $c->onDelete[] = function($row) {
            $this->successFlashMessage("Group has been deleted successfully");
            $this->redrawControl('groupShowAll');
        };
        $c->onEdit[] = function($row) {
            $this->successFlashMessage("Group has been edited successfully");
            $this->redrawControl('groupShowAllRecords');
        };

        return $c;
    }


    protected function startup() {
        parent::startup();
    }

    public function actionDefault() {
        
    }

    public function renderDefault() {
        
    }

    public function actionShowAll($sort, $order, $keywords, $filter) {
        $this->drawAllowed = true;
    }

    public function drawGroups($starred) {
        if ($this->drawAllowed) {
            $params = $this->context->httpRequest->getQuery();
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
                Debugger::fireLog('--aaaaaaaaaa');

                $this->records = $this->context->Group->findAllByKw($params);
                if($starred) $this->records->where(':submitter_has_group.submitter_id = ?', $this->user->id);
                $this->template->recordsStarred = array();

                $recordsStarredTemp = $this->context->SubmitterHasGroup->findAllBy(array('submitter_has_group.submitter_id' => $this->user->id));
                foreach ($recordsStarredTemp as $record) {
                    $this->template->recordsStarred[] = $record->group_id;
                }



                $this->vp = new \VisualPaginator($this, 'vp');
                $paginator = $this->vp->getPaginator();
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

                $params = array();

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

    public function renderShowAll() {
        if ($this->drawAllowed) {
            $this->drawGroups($this['individualFilter']->getActiveButtonName() == 'starred');
        }
    }


    public function handleSetFavouriteGroup($id) {

        Debugger::fireLog('handleSetFavouriteGroup(' . $id . ')');


        $this->context->SubmitterHasGroup->insert(array(
            'group_id' => $id,
            'submitter_id' => $this->user->id
        ));

        $this->flashMessage('Operation has been completed successfully.', 'alert-success');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        } else {
            $this->redrawControl();
        }
    }

    public function handleUnsetFavouriteGroup($id) {

        Debugger::fireLog('handleUnsetFavouriteGroup(' . $id . ')');

        $record = $this->context->SubmitterHasGroup->findOneBy(array(
            'group_id' => $id,
            'submitter_id' => $this->user->id));

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

    public function createComponentIndividualFilter ( ) {
        $c = new \App\Components\ButtonToggle\ButtonGroupComponent([
            'all'     =>  array(
                'caption'   =>  'All groups',
                'icon'      =>  'list'
            ),
            'starred'      =>  array(
                'caption'   =>  'Starred groups',
                'icon'      =>  'star'
            ),
        ], 'all');

        $c->onActiveButtonChanged[] = function(){

        };

        return $c;
    }
}
