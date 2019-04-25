<?php

namespace App\Model;

use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;


class RightsRequest extends Base
{
    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'rights_request';


    /**
     * @return Selection
     */
    public function getAllWaiting(): Selection
    {
        return $this->getTable()->where('verdict = ?', 'waiting');
    }


    /**
     * @return int
     */
    public function getWaitingCount(): int
    {
        return $this->getAllWaiting()->count('*');
    }


    /**
     * @param int $userId
     * @return Selection
     */
    public function getAllByUser(int $userId): Selection
    {
        return $this->getTable()->where('submitter_id = ?', $userId);
    }


    /**
     * @param int $userId
     * @return bool
     */
    public function hasUserWaitingRequests(int $userId): bool
    {
        return $this->getTable()
                ->where('submitter_id = ?', $userId)
                ->where('verdict = ?', 'waiting')
                ->count() > 0;
    }

    /**
     * @param int $userId
     * @return Selection
     */
    public function getUsersUnseenVerdict(int $userId): Selection
    {
        return $this->getTable()
                ->where('submitter_id = ?', $userId)
                ->where('verdict <> ?', 'waiting')
                ->where('seen = ?', '0')
                ->order('verdict_datetime DESC')
                ->limit(1);
    }

    /**
     * @param int $userId
     * @param int $requestId
     * @return bool
     */
    public function markVerdictAsSeen(int $userId, int $requestId): bool
    {
        $res = $this->getTable()->where('rights_request_id = ?', $requestId);

        if ($res->fetch()->submitter_id === $userId) {
            $res->update(['seen' => '1']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $userId
     * @return void
     */
    public function request(int $userId): void
    {
        $this->getTable()->insert([
            'submitter_id' => $userId,
            'verdict' => 'waiting',
            'request_datetime' => new DateTime(),
            'verdict_datetime' => null,
            'verdict_submitter_id' => null
        ]);
    }

    /**
     * @param int $requestId
     * @param int $userId
     * @return int
     */
    public function approve(int $requestId, int $userId): int
    {
        $row = $this->getTable()->where('rights_request_id', $requestId);

        $row->update([
            'verdict_submitter_id' => $userId,
            'verdict_datetime' => new DateTime(),
            'verdict' => 'approved'
        ]);

        return $row->fetchField('submitter_id');
    }

    /**
     * @param int $requestId
     * @param int $userId
     * @return bool
     */
    public function reject(int $requestId, int $userId): bool
    {
        return $this->getTable()
            ->where('rights_request_id', $requestId)
            ->update([
                'verdict_submitter_id' => $userId,
                'verdict_datetime' => new DateTime(),
                'verdict' => 'rejected'
        ]);
    }

    /**
     * @return Selection
     */
    public function getAll(): Selection
    {
        return $this->getTable()
            ->alias('verdict_submitter_id', 'verdict_submitter')
            ->select($this->tableName . '.*,
                submitter.name,
                submitter.surname,
                verdict_submitter.name AS verdict_name,
                verdict_submitter.surname AS verdict_surname')
            ->order('request_datetime DESC');
    }
}