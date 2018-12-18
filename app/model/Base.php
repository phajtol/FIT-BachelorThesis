<?php

namespace App\Model;

use Nette;

class Base {

    use Nette\SmartObject;

    /** @var Nette\Database\Context */
    public $database;

    /** @var string */
    protected $tableName;

    /**
     * @param \Nette\Database\Context $db
     * @throws \Nette\InvalidStateException
     */
    public function __construct(\Nette\Database\Context $db)
    {
        $this->database = $db;

        if ($this->tableName === NULL) {
            $class = get_class($this);
            throw new Nette\InvalidStateException("Name of the table has to be define in $class::\$tableName.");
        }
    }

    protected function getTable(): Nette\Database\Table\Selection
    {
        return $this->database->table($this->tableName);
    }

    /**
     * Returns all the rows.
     * @return \Nette\Database\Table\Selection
     */
    public function findAll(): Nette\Database\Table\Selection
    {
        return $this->getTable();
    }

    /**
     * Vrací vyfiltrované záznamy na základě vstupního pole
     * (pole array('name' => 'David') se převede na část SQL dotazu WHERE name = 'David')
     * @param array $by
     * @return \Nette\Database\Table\Selection
     */
    public function findAllBy(array $by): Nette\Database\Table\Selection
    {
        return $this->getTable()->where($by);
    }

    /**
     * To samé jako findAllBy akorát vrací vždy jen jeden záznam
     * @param array $by
     * @return \Nette\Database\Table\ActiveRow|FALSE
     */
    public function findOneBy(array $by)
    {
        return $this->findAllBy($by)->limit(1)->fetch();
    }

    /**
     * Vrací záznam s daným primárním klíčem
     * @param $id
     * @return \Nette\Database\Table\ActiveRow|FALSE
     */
    public function find($id)
    {
        return $this->getTable()->get($id);
    }

    /**
     * Upraví záznam
     * @param $data - must contain 'id'
     * @return int - number of affected rows
     */
    public function update($data): int
    {
        return $this->findAllBy(array('id' => $data['id']))->update($data);
    }

    /**
     * Vloží nový záznam a vrátí jeho ID
     * @param array $data
     * @return \Nette\Database\Table\ActiveRow
     */
    public function insert($data): Nette\Database\Table\ActiveRow
    {
        return $this->getTable()->insert($data);
    }

    /**
     * @param $array
     * @return Nette\Database\ResultSet|null
     */
    public function insertMulti($array): ?Nette\Database\ResultSet
    {
        if (!is_array($array)) {
            throw new Nette\InvalidArgumentException('Given parameter for update must be an array');
        }
        if (!count($array)) {
            return null;
        }

        return $this->database->query('INSERT INTO ' . $this->tableName, $array);
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int {
        return $this->getTable()->where('id', $id)->delete();
    }


    /**
     * Insert row in database or update existing one.
     *
     * @param  array
     * @return \Nette\Database\Table\ActiveRow automatically found based on first "column => value" pair in $values
     * @link https://github.com/nette/web-addons.nette.org/blob/master/app/model/Table.php#L114
     */
    public function createOrUpdate($values): Nette\Database\Table\ActiveRow
    {
        $pairs = array();
        foreach ($values as $key => $value) {
            $pairs[] = "`$key` = ?"; // warning: SQL injection possible if $values infected!
        }

        $pairs = implode(', ', $pairs);
        $values = array_values($values);

        $this->database->queryArgs(
            'INSERT INTO `' . $this->tableName . '` SET ' . $pairs .
            ' ON DUPLICATE KEY UPDATE ' . $pairs, array_merge($values, $values)
        );

        return $this->findOneBy(func_get_arg(0));
    }

    // preserve compatibility
    public function fetchAll(): Nette\Database\Table\Selection
    {
        return $this->findAll();
    }

    public function beginTransaction(): void
    {
        $this->database->beginTransaction();
    }

    public function commitTransaction(): void
    {
        $this->database->commit();
    }

    public function rollbackTransaction(): void
    {
        $this->database->rollback();
    }

}
