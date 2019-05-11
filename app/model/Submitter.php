<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class Submitter extends Base {

    /**
     * Name of the database table
     * @var string
     */
    protected $tableName = 'submitter';

    /**
     * @param string $kw
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByKw(string $kw): Selection
    {
        return $this->database->table('submitter')
            ->where('name LIKE ? OR surname LIKE ? OR email LIKE ? OR nickname LIKE ?',
                '%' . $kw . '%',
                '%' . $kw . '%',
                '%' . $kw . '%',
                '%' . $kw . '%'
            );
    }

    /**
     * @param int $id
     * @return FALSE|\Nette\Database\Table\ActiveRow
     */
    public function findOneById(int $id)
    {
        return $this->findOneBy(["id" => $id]);
    }

    /**
     * @param string $term
     * @return false|ActiveRow
     */
    public function findByLoginOrEmail(string $term): ActiveRow
    {
        return $this->database->table('submitter')->where("email = ? OR nickname = ?", $term, $term)->fetch();
    }

    /**
     * @param int $userId
     * @return FALSE|ActiveRow|null
     */
    public function deleteAssociatedRecords(int $userId): ?ActiveRow
    {
        $record = $this->find($userId);

        if ($record) {
            $this->database->beginTransaction();

            $delete_rel_tables = [
                'submitter_has_cu_group'            =>  'submitter_id',
                'submitter_has_group'               =>  'submitter_id',
                'submitter_has_publication'         =>  'submitter_id',
                'auth_login_password'               =>  'submitter_id',
                'auth_ldap'                         =>  'submitter_id',
                'retrieve'                          =>  'submitter_id',
                'user_settings'                     =>  'submitter_id',
                'user_role'                         =>  'user_id'
            ];

            $detach_rel_tables = [
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
            ];

            foreach($detach_rel_tables as $table => $column)
                $this->database->table($table)->where([$column => $userId])->update([$column => NULL]);

            foreach($delete_rel_tables as $table => $column)
                $this->database->table($table)->where([$column => $userId])->delete();

            $record->delete();

            $this->database->commit();

            return $record;
        }

        return null;
    }

    /**
     * @param string $email
     * @return FALSE|ActiveRow
     */
    public function findOneByEmail(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @param string $nickname
     * @return FALSE|ActiveRow
     */
    public function findOneByNickname(string $nickname)
    {
        return $this->findOneBy(['nickname' => $nickname]);
    }

    /**
     * @param string $nickname
     * @param string $email
     * @param string $name
     * @param string $surname
     * @return \Nette\Database\Table\ActiveRow
     */
    public function createNew(string $nickname, string $email = '', ?string $name = '', string $surname = ''): ActiveRow
    {
        return $this->insert([
                'name'  	=>	$name,
                'surname'	=>	$surname,
                'nickname'	=>	$nickname,
                'email'		=>	$email
            ]);
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getAllUserSuggestedConferenceCategoriesIds(int $userId): array
    {
        $conference_category_ids = [];
        $res = $this->findAll()
            ->select(':submitter_has_cu_group.cu_group:cu_group_has_conference_category.conference_category_id')
            ->where('submitter.id', $userId);

        foreach ($res as $rec) {
            if ($rec->conference_category_id) {
                $conference_category_ids[] = $rec->conference_category_id;
            }
        }

        return $conference_category_ids;
    }

    /**
     * @param string $nickname
     * @return string
     */
    public function getNearestFreeNickname(string $nickname): string
    {
        $suffix = '';

        while ($this->findOneByNickname($nickname . $suffix)) {
            if (is_numeric($suffix)) {
                $suffix++;
            } else {
                $suffix = 2;
            }
        }

        return $nickname . $suffix;
    }

    /**
     * @return array
     */
    public function getPairs(): array
    {
        $arr = [];
        $all = $this->database->fetchAll('SELECT * FROM submitter order by surname,name;');

        foreach ($all as $one) {
            $arr[$one->id] = $one->surname . ' ' . $one->name . ' (' . $one->nickname . ')';
        }

        return $arr;
    }
}
