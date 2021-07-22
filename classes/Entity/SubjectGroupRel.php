<?php

namespace Entity;

class SubjectGroupRel {

    protected $dbal;

    private $id;

    private $data;

    private static $instances = array();

    private function __construct($dbal, $id) {
        $this->dbal = $dbal;
        $this->id = $id;
        $this->loadData();
    }

    public static function load($dbal, $id) {
        try {
            if(isset(self::$instances[$id])) {
                return self::$instances[$id];
            } else {
                self::$instances[$id] = new SubjectGroupRel($dbal, $id);
                return self::$instances[$id];
            }
        } catch (\Exception\NotFoundException $unfe) {
            return null;
        }
    }

    private function loadData() {
        $qb = $this->dbal->createQueryBuilder();
        $qb->select("id, id_subject, id_group");
        $qb->from("subject_group_rel", "sgr");
        $qb->where("id = :id")->setParameter("id", $this->id);

        $this->data = $qb->execute()->fetch();
    }


    public static function createSubjectGroupRel($dbal, $idSubject, $idGroup) {

        $qb = $dbal->createQueryBuilder()
            ->insert('subject_group_rel')
            ->values(
                array(
                    'id_subject' => '?',
                    'id_group' => '?'
                )
            )
            ->setParameter(0, $idSubject)
            ->setParameter(1, $idGroup);

        if ($qb->execute()) {
            return true;
        } else {
            return false;
        }

    }

    public static function deleteSubjectGroupRelation($dbal, $idGroup, $idSubject) {
        
        $qb = $dbal->createQueryBuilder();
        $qb->delete("subject_group_rel", "sgr");
        $qb->where("sgr.id_group = :idGroup")->setParameter("idGroup", $idGroup);
        $qb->andWhere("sgr.id_subject = :idSubject")->setParameter("idSubject", $idSubject);
        $qb->execute();

    }

    public function getID() {
        return $this->data['id'];
    }

}