<?php

namespace Entity;

class Group {

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
                self::$instances[$id] = new Group($dbal, $id);
                return self::$instances[$id];
            }
        } catch (\Exception\NotFoundException $unfe) {
            return null;
        }
    }

    private function loadData() {
        $qb = $this->dbal->createQueryBuilder();
        $qb->select("id, shortcut, grade, semester, studentCount, studyForm, studyType, language");
        $qb->from("group1", "g");
        $qb->where("id = :id")->setParameter("id", $this->id);

        $this->data = $qb->execute()->fetch();
    }


    public static function createGroup($dbal, $shortcut, $grade, $semester, $studentCount, $studyForm, $studyType, $language) {

        $qb = $dbal->createQueryBuilder()
            ->insert('group1')
            ->values(
                array(
                    'shortcut' => '?',
                    'grade' => '?',
                    'semester' => '?',
                    'studentCount' => '?',
                    'studyForm' => '?',
                    'studyType' => '?',
                    'language' => '?',
                )
            )
            ->setParameter(0, $shortcut)
            ->setParameter(1, $grade)
            ->setParameter(2, $semester)
            ->setParameter(3, $studentCount)
            ->setParameter(4, $studyForm)
            ->setParameter(5, $studyType)
            ->setParameter(6, $language);
            
        if ($qb->execute()) {
            return true;
        } else {
            return false;
        }

    }

    public static function getAllGroups($dbal) {

        $qb = $dbal->createQueryBuilder();
        $qb->select("id, shortcut, grade, semester, studentCount, studyForm, studyType, language");
        $qb->from("group1", "g");
        
        $result = $qb->execute()->fetchAll();

        $groups[] = null;
        foreach ($result as $res) {
            $groups[] = Group::load($dbal, $res['id']);
        }

        return $groups;

    }

    public function getID() {
        return $this->data['id'];
    }

    public function getShortcut() {
        return $this->data['shortcut'];
    }

    public function getGrade() {
        return $this->data['grade'];
    }

    public function getSemester() {
        return $this->data['semester'];
    }

    public function getStudentCount() {
        return $this->data['studentCount'];
    }

    public function getStudyForm() {
        return $this->data['studyForm'];
    }

    public function getStudyType() {
        return $this->data['studyType'];
    }

    public function getLanguage() {
        return $this->data['language'];
    }

}