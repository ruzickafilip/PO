<?php

namespace Entity;

class Tag {

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
                self::$instances[$id] = new Tag($dbal, $id);
                return self::$instances[$id];
            }
        } catch (\Exception\NotFoundException $unfe) {
            return null;
        }
    }

    private function loadData() {
        $qb = $this->dbal->createQueryBuilder();
        $qb->select("id, name, idSubject, idGroup, idEmployee, type, studentCount, lessonCount, weekCount, language, points");
        $qb->from("tag", "t");
        $qb->where("id = :id")->setParameter("id", $this->id);

        $this->data = $qb->execute()->fetch();
    }


    public static function generateTags($dbal, $group, $subjects) {

        $studentCount = $group->getStudentCount();
        \Entity\Tag::deleteAllTags($dbal, $group);

        $subjectCount = 1;
        foreach($subjects as $subject) {
            if (!is_null($subject)) {
                if (!is_null($subject->getLectureCount()) && $subject->getLectureCount() > 0) {
                    $points = \Entity\Points::defaultWeekCount * \Entity\Points::pointsPerLecture * $subject->getLectureCount();
                    \Entity\Tag::createTag($dbal, $subject->getShortcut() . $subjectCount, 'přednáška', $group->getStudentCount(), $subject->getLectureCount(), \Entity\Points::defaultWeekCount, $points, \Entity\Points::defaultLanguage, $group->getId()??0, $subject->getId());
                
                
                    // vytvoreni stitku pro cviceni
                        // - podle kapacity tridy u subjektu a poctu studentu u group, vytvorit rovnomerne skupiny
                        // - kazda skupina ma X stitku, kolik je pocet cviceni
                        // - vzorec pro body 1.2 * 1 * 14


                    // vytvoreni stitku pro seminar
                        // - stejny jak cviceni


                    // assignovani stitku k zamestnancum
                    // rucni vytvareni stitku
                    // editace vsech entit
                    // vypocet bodu zamestnance
                
                }
                $subjectCount++;
            }
        }

        return;

    }

    public static function createTag($dbal, $name, $type, $studentCount, $lessonCount, $weekCount, $points, $language = null, $idGroup = null, $idSubject = null, $idEmployee = null) {

        $qb = $dbal->createQueryBuilder()
        ->insert('tag')
        ->values(
            array(
                'type' => '?',
                'studentCount' => '?',
                'lessonCount' => '?',
                'weekCount' => '?',
                'points' => '?',
                'language' => '?',
                'idSubject' => '?',
                'idEmployee' => '?',
                'idGroup' => '?',
                'name' => '?',
            )
        )
        ->setParameter(0, $type)
        ->setParameter(1, $studentCount)
        ->setParameter(2, $lessonCount)
        ->setParameter(3, $weekCount)
        ->setParameter(4, $points)
        ->setParameter(5, $language)
        ->setParameter(6, $idSubject)
        ->setParameter(7, $idEmployee)
        ->setParameter(8, $idGroup)
        ->setParameter(9, $name);
        
        if ($qb->execute()) {
            return true;
        } else {
            return false;
        }

    }

    public static function getAllTags($dbal) {

        $qb = $dbal->createQueryBuilder();
        $qb->select("id, name, idSubject, idEmployee, idGroup, type, studentCount, lessonCount, weekCount, language, points");
        $qb->from("tag", "t");
        
        $result = $qb->execute()->fetchAll();

        $groups[] = null;
        foreach ($result as $res) {
            $groups[] = Tag::load($dbal, $res['id']);
        }

        return $groups;

    }

    public static function deleteAllTags($dbal, $group) {
        
        if (!is_null($group->getId())) {
            $qb = $dbal->createQueryBuilder();
            $qb->delete("tag", "t");
            $qb->where("t.idGroup = :idGroup")->setParameter("idGroup", $group->getId());
            $qb->execute();
        }
    
    }

    public function getID() {
        return $this->data['id'];
    }

    public function getName() {
        return $this->data['name'];
    }

    public function getIdSubject() {
        return $this->data['idSubject'];
    }

    public function getIdEmployee() {
        return $this->data['idEmployee'];
    }

    public function getType() {
        return $this->data['type'];
    }

    public function getStudentCount() {
        return $this->data['studentCount'];
    }

    public function getLessonCount() {
        return $this->data['lessonCount'];
    }

    public function getWeekCount() {
        return $this->data['weekCount'];
    }

    public function getLanguage() {
        return $this->data['language'];
    }

    public function getPoints() {
        return $this->data['points'];
    }

}