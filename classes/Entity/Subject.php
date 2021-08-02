<?php

namespace Entity;

class Subject {

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
                self::$instances[$id] = new Subject($dbal, $id);
                return self::$instances[$id];
            }
        } catch (\Exception\NotFoundException $unfe) {
            return null;
        }
    }

    private function loadData() {
        $qb = $this->dbal->createQueryBuilder();
        $qb->select("id, shortcut, weekCount, lectureCount, exerciseCount, seminarCount, endType, language, classCount");
        $qb->from("subject", "s");
        $qb->where("id = :id")->setParameter("id", $this->id);

        $this->data = $qb->execute()->fetch();
    }


    public static function createSubject($dbal, $shortcut, $weekCount, $lectureCount, $exerciseCount, $seminarCount, $endType, $language, $classCount) {

        $qb = $dbal->createQueryBuilder()
            ->insert('subject')
            ->values(
                array(
                    'shortcut' => '?',
                    'weekCount' => '?',
                    'lectureCount' => '?',
                    'exerciseCount' => '?',
                    'seminarCount' => '?',
                    'endType' => '?',
                    'language' => '?',
                    'classCount' => '?'
                )
            )
            ->setParameter(0, $shortcut)
            ->setParameter(1, $weekCount)
            ->setParameter(2, $lectureCount)
            ->setParameter(3, $exerciseCount)
            ->setParameter(4, $seminarCount)
            ->setParameter(5, $endType)
            ->setParameter(6, $language)
            ->setParameter(7, $classCount);

            

        if ($qb->execute()) {
            return true;
        } else {
            return false;
        }

    }

    public static function getSubjectById($dbal, $idSubject) {

        return Subject::load($dbal, $idSubject);

    }

    public static function getAllSubjectsByGroupId($dbal, $idGroup) {
        $qb = $dbal->createQueryBuilder();
        $qb->select("s.id, s.shortcut, s.weekCount, s.lectureCount, s.exerciseCount, s.seminarCount, s.endType, s.language, s.classCount");
        $qb->from("subject", "s");
        $qb->innerJoin("s", "subject_group_rel", "sgr", 's.id = sgr.id_subject');
        $qb->where("sgr.id_group = :idGroup")->setParameter("idGroup", $idGroup);
        // $qb->where("id = :id")->setParameter("id", $id);

        $result = $qb->execute()->fetchAll();


        $subjects[] = null;
        foreach ($result as $res) {
            if (!is_null($res)) {
                $subjects[] = Subject::load($dbal, $res['id']);
            }
        }

        return $subjects;
    }

    public static function getAllSubjects($dbal) {
        $qb = $dbal->createQueryBuilder();
        $qb->select("s.id, s.shortcut, s.weekCount, s.lectureCount, s.exerciseCount, s.seminarCount, s.endType, s.language, s.classCount");
        $qb->from("subject", "s");
        $result = $qb->execute()->fetchAll();

        $subjects = null;
        foreach ($result as $res) {
            if (!is_null($res)) {
                $subjects[] = Subject::load($dbal, $res['id']);
            }
        }

        return $subjects;
    }

    public static function getUnassignedSubjects($dbal, $idGroup) {

        $qb = $dbal->createQueryBuilder();
        $qb->select("id_subject");
        $qb->from("subject_group_rel", "sgr");
        $qb->where("sgr.id_group = :idGroup")->setParameter("idGroup", $idGroup);

        $result = null;
        $subjects = null;
        $idSubjects = array();

        $result = $qb->execute()->fetchAll();

        foreach ($result as $res) {
            $idSubjects[] = (int)$res['id_subject'];
        }

        if (count($idSubjects)) {
            $qb = $dbal->createQueryBuilder();
            $qb->select("s.id, s.shortcut, s.weekCount, s.lectureCount, s.exerciseCount, s.seminarCount, s.endType, s.language, s.classCount");
            $qb->from("subject", "s");
            $qb->where($qb->expr()->notIn('s.id', $idSubjects));
            $subjects = $qb->execute()->fetchAll();

            $groupResult = null;
            foreach ($subjects as $sbj) {
                $groupResult[] = Subject::load($dbal, $sbj['id']);
            }

            return $groupResult;

        } else {
            return Subject::getAllSubjects($dbal);
        }

    }

    public function getID() {
        return $this->data['id'];
    }

    public function getShortcut() {
        return $this->data['shortcut'];
    }

    public function getLectureCount() {
        return $this->data['lectureCount'];
    }

    public function getExerciseCount() {
        return $this->data['exerciseCount'];
    }

    public function getSeminarCount() {
        return $this->data['seminarCount'];
    }

    public function getClassCount() {
        return $this->data['classCount'];
    }
    
}