<?php

namespace App\Model;

use Nette\DateTime;



class ReferenceCount extends Base {

    /**
     * Name of the database table.
     * @var string
     */
    protected $tableName = 'reference_count';

    /**
     * Count update interval in hours.
     */
    const UPDATE_INTERVAL = 8;


    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->getTable()
            ->select('count')
            ->where('reference_count_id = ?', 1)
            ->fetchField('count');
    }

    /**
     * @return DateTime
     */
    public function getLastUpdate(): DateTime
    {
        return $this->getTable()
            ->select('timestamp')
            ->where('reference_count_id = ?', 1)
            ->fetchField('timestamp');
    }


    /**
     * @param int $count
     */
    public function updateCount(int $count): void
    {
        $this->getTable()
            ->where('reference_count_id = ?', 1)
            ->update([
                'count' => $count,
                'timestamp' => new DateTime()
            ]);
    }

}
