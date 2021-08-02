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
        $qb->select("id, name, idSubject, idGroup, idEmployee, type, studentCount, lessonCount, weekCount, language, points, source");
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
                
                
                    if (!is_null($subject->getClassCount()) && !is_null($group->getStudentCount())) {

                        $tagCount = ceil($group->getStudentCount() / $subject->getClassCount());
                        $studentsPerGroup = floor($group->getStudentCount() / $tagCount);

                        for($i=0; $i < $tagCount; $i++) {
                            $students[$i] = $studentsPerGroup;
                        }

                        if (($studentsPerGroup * $tagCount) != $subject->getExerciseCount()) {
                            $rest = $group->getStudentCount() - ($studentsPerGroup * $tagCount);

                            for($i=0; $i < $rest; $i++) {
                                $students[$i] += 1;
                            }
                        }

                        foreach($students as $student) {
                            $points = \Entity\Points::defaultWeekCount * \Entity\Points::pointsPerExcercise * 1;
                            for ($i = 0; $i < $subject->getExerciseCount(); $i++) {
                                \Entity\Tag::createTag($dbal, $subject->getShortcut() . $subjectCount . '-cv', 'cvičení', $student, 1, \Entity\Points::defaultWeekCount, $points, \Entity\Points::defaultLanguage, $group->getId()??0, $subject->getId());
                            }
                        }

                        foreach($students as $student) {
                            $points = \Entity\Points::defaultWeekCount * \Entity\Points::pointsPerSeminar * 1;
                            for ($i = 0; $i < $subject->getSeminarCount(); $i++) {
                                \Entity\Tag::createTag($dbal, $subject->getShortcut() . $subjectCount . '-sem', 'cvičení', $student, 1, \Entity\Points::defaultWeekCount, $points, \Entity\Points::defaultLanguage, $group->getId()??0, $subject->getId());
                            }
                        }


                    }

                }
                $subjectCount++;
            }
        }

        return;

    }

    public static function createTag($dbal, $name, $type, $studentCount, $lessonCount, $weekCount, $points, $language = null, $idGroup = null, $idSubject = null, $idEmployee = null, $source = 'generated') {

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
                'source' => '?',
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
        ->setParameter(9, $name)
        ->setParameter(10, $source);

        
        if ($qb->execute()) {
            return true;
        } else {
            return false;
        }

    }

    public static function getAllTags($dbal) {

        $qb = $dbal->createQueryBuilder();
        $qb->select("id, name, idSubject, idEmployee, idGroup, type, studentCount, lessonCount, weekCount, language, points, source");
        $qb->from("tag", "t");
        
        $result = $qb->execute()->fetchAll();

        $tags[] = null;
        foreach ($result as $res) {
            $tags[] = Tag::load($dbal, $res['id']);
        }

        return $tags;

    }

    public static function deleteAllTags($dbal, $group) {
        
        if (!is_null($group->getId())) {
            $qb = $dbal->createQueryBuilder();
            $qb->delete("tag", "t");
            $qb->where("t.idGroup = :idGroup")->setParameter("idGroup", $group->getId());
            $qb->andWhere("t.source = :generated")->setParameter("generated", 'generated');
            $qb->execute();
        }
    
    }

    public static function updateTag($dbal, $employeeId, $tagId) {

        $qb = $dbal->createQueryBuilder();
        $qb->update('tag', 't');
        $qb->set('t.idEmployee', ':employeeId');
        $qb->where('t.id = :tagId');
        $qb->setParameter('employeeId', $employeeId);
        $qb->setParameter('tagId', $tagId);
        $qb->execute();
    
    }

    public static function getAllTagsForEmployee($dbal, $employeeId) {

        $qb = $dbal->createQueryBuilder();
        $qb->select("id, name, idSubject, idEmployee, idGroup, type, studentCount, lessonCount, weekCount, language, points, source");
        $qb->from("tag", "t");
        $qb->where("t.idEmployee = :employeeId")->setParameter("employeeId", $employeeId);
        
        $result = $qb->execute()->fetchAll();

        $tags[] = null;
        foreach ($result as $res) {
            $tags[] = Tag::load($dbal, $res['id']);
        }

        return $tags;

    }

    public static function getTagById($dbal, $idTag) {

        return Tag::load($dbal, $idTag);

    }

    public static function unsetEmployee($dbal, $idTag) {

        $qb = $dbal->createQueryBuilder();
        $qb->update('tag', 't');
        $qb->set('t.idEmployee', ':employeeId');
        $qb->where('t.id = :tagId');
        $qb->setParameter('employeeId', null);
        $qb->setParameter('tagId', $idTag);
        $qb->execute();

    }

    public static function getAllUnassignedTagsForEmployee($dbal, $employeeId) {

        $qb = $dbal->createQueryBuilder();
        $qb->select("id, name, idSubject, idEmployee, idGroup, type, studentCount, lessonCount, weekCount, language, points, source");
        $qb->from("tag", "t");
        $qb->where($qb->expr()->notIn('t.idEmployee', array($employeeId)));
        $result = $qb->execute()->fetchAll();

        $qb = $dbal->createQueryBuilder();
        $qb->select("id, name, idSubject, idEmployee, idGroup, type, studentCount, lessonCount, weekCount, language, points, source");
        $qb->from("tag", "t");
        $qb->where($qb->expr()->isNull('t.idEmployee'));
        $resultNull = $qb->execute()->fetchAll();

        $tags[] = null;
        foreach ($result as $res) {
            $tags[] = Tag::load($dbal, $res['id']);
        }
        foreach ($resultNull as $res) {
            $tags[] = Tag::load($dbal, $res['id']);
        }

        return $tags;

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

    public function getSource() {
        return $this->data['source'];
    }

}