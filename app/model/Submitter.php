<?php

namespace App\Model;

class Submitter extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'submitter';

    public function findAllByKw($kw) {
        return $this->database->table('submitter')->where("name LIKE ? OR surname LIKE ? OR email LIKE ? OR nickname LIKE ?", "%" . $kw . "%", "%" . $kw . "%", "%" . $kw . "%", "%" . $kw . "%");
    }

    public function findOneById($id) {
        return $this->findOneBy(array("id" => $id));
    }

    public function findByLoginOrEmail($term) {
        return $this->database->table('submitter')->where("email = ? OR nickname = ?", $term, $term)->fetch();
    }

    public function deleteAssociatedRecords($userId) {

        $record = $this->find($userId);

        if ($record) {

            $this->database->beginTransaction();

            $delete_rel_tables = array(
                'submitter_has_cu_group'            =>  'submitter_id',
                'submitter_has_group'               =>  'submitter_id',
                'submitter_has_publication'         =>  'submitter_id',
                'auth_login_password'               =>  'submitter_id',
                'auth_ldap'                         =>  'submitter_id',
                'retrieve'                          =>  'submitter_id',
                'user_settings'                     =>  'submitter_id',
                'user_role'                         =>  'user_id'
            );

            $detach_rel_tables = array(
                'publication'               => 'submitter_id',
                'annotation'                => 'submitter_id',
                'attributes'                => 'submitter_id',
                'author'                    => 'submitter_id',
                'categories'                => 'submitter_id',
                'conference'                => 'submitter_id',
                'conference_year'           => 'submitter_id',
                'journal'                   => 'submitter_id',
                'publisher'                 => 'submitter_id',
                'attrib_storage'            => 'submitter_id',
                'group'                     => 'submitter_id'
            );

            foreach($detach_rel_tables as $table => $column)
                $this->database->table($table)->where(array($column => $userId))->update(array($column => NULL));

            foreach($delete_rel_tables as $table => $column)
                $this->database->table($table)->where(array($column => $userId))->delete();

            $record->delete();

            $this->database->commit();

            return $record;
        }

        return null;
    }


    public function findOneByEmail($email) {
        return $this->findOneBy(array('email' => $email));
    }
    public function findOneByNickname($nickname) {
        return $this->findOneBy(array('nickname' => $nickname));
    }

    /**
     * @param $nickname
     * @param $role
     * @param string $email
     * @param string $name
     * @param string $surname
     * @return \Nette\Database\Table\ActiveRow
     */
    public function createNew($nickname, $email = "", $name = "", $surname = "") {
        return $this->insert(
            array(
                "name"		=>	$name,
                "surname"	=>	$surname,
                "nickname"	=>	$nickname,
                "email"		=>	$email
            )
        );
    }

    public function getAllUserSuggestedConferenceCategoriesIds($userId){
        $res = $this->findAll()
            ->select(':submitter_has_cu_group.cu_group:cu_group_has_conference_category.conference_category_id')
            ->where('submitter.id', $userId);

        $conference_category_ids = array();
        foreach($res as $rec) {
            if($rec->conference_category_id)
                $conference_category_ids[] = $rec->conference_category_id;
        }
        return $conference_category_ids;
    }

    public function getNearestFreeNickname($nickname) {
        $suffix = '';
        while($this->findOneByNickname($nickname . $suffix)) {
            if(is_numeric($suffix)) $suffix++;
            else $suffix = 2;
        }
        return $nickname . $suffix;
    }

    public function getPairs() {
        $all = $this->database->fetchAll("SELECT * FROM submitter order by surname,name;");
        $arr = array();
        foreach ($all as $one) {
            $arr[$one->id] = $one->surname." ".$one->name." (".$one->nickname.")";
        }
        return $arr;
    }
}
