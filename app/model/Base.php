<?php

namespace App\Model;

use Nette;

class Base extends \Nette\Object {

    /** @var Nette\Database\Context */
    public $database;

    /** @var string */
    protected $tableName;

    /**
     * @param Nette\Database\Connection $db
     * @throws \Nette\InvalidStateException
     */
    public function __construct(\Nette\Database\Context $db) {
        $this->database = $db;

        if ($this->tableName === NULL) {
            $class = get_class($this);
            throw new Nette\InvalidStateException("Name of the table has to be define in $class::\$tableName.");
        }
    }

    protected function getTable() {
        return $this->database->table($this->tableName);
    }

    /**
     * Returns all the rows.
     * @return \Nette\Database\Table\Selection
     */
    public function findAll() {
        return $this->getTable();
    }

    /**
     * Vrací vyfiltrované záznamy na základě vstupního pole
     * (pole array('name' => 'David') se převede na část SQL dotazu WHERE name = 'David')
     * @param array $by
     * @return \Nette\Database\Table\Selection
     */
    public function findAllBy(array $by) {
        return $this->getTable()->where($by);
    }

    /**
     * To samé jako findAllBy akorát vrací vždy jen jeden záznam
     * @param array $by
     * @return \Nette\Database\Table\ActiveRow|FALSE
     */
    public function findOneBy(array $by) {
        return $this->findAllBy($by)->limit(1)->fetch();
    }

    /**
     * Vrací záznam s daným primárním klíčem
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow|FALSE
     */
    public function find($id) {
        return $this->getTable()->get($id);
    }

    /**
     * Upraví záznam
     * @param array $data
     */
    public function update($data) {
        return $this->findAllBy(array('id' => $data['id']))->update($data);
    }

    /**
     * Vloží nový záznam a vrátí jeho ID
     * @param array $data
     * @return \Nette\Database\Table\ActiveRow
     */
    public function insert($data) {
        return $this->getTable()->insert($data);
    }

    public function insertMulti($array) {
        if(!is_array($array)) throw new Nette\InvalidArgumentException('Given parameter for update must be an array');
        if(!count($array)) return null;
        return $this->database->query('INSERT INTO ' . $this->tableName, $array);
    }

    public function delete($id) {
        $this->getTable()->where('id', $id)->delete();
    }


    /**
     * Insert row in database or update existing one.
     *
     * @param  array
     * @return \Nette\Database\Table\ActiveRow automatically found based on first "column => value" pair in $values
     * @link https://github.com/nette/web-addons.nette.org/blob/master/app/model/Table.php#L114
     */
    public function createOrUpdate(array $values)
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
    public function fetchAll(){
        return $this->findAll();
    }

    public function beginTransaction() { $this->database->beginTransaction(); }
    public function commitTransaction() { $this->database->commit(); }
    public function rollbackTransaction() { $this->database->rollback(); }

}
