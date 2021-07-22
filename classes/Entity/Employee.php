<?php

namespace Entity;

class Employee {

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
                self::$instances[$id] = new Employee($dbal, $id);
                return self::$instances[$id];
            }
        } catch (\Exception\NotFoundException $unfe) {
            return null;
        }
    }

    private function loadData() {
        $qb = $this->dbal->createQueryBuilder();
        $qb->select("id, surname, lastname, privateEmail, publicEmail, workType, doctor");
        $qb->from("employee", "e");
        $qb->where("id = :id")->setParameter("id", $this->id);

        $this->data = $qb->execute()->fetch();
    }


    
    public static function createEmployee($dbal, $surname, $lastname, $privatemail, $publicmail, $worktype, $doctor) {

        $qb = $dbal->createQueryBuilder()
            ->insert('employee')
            ->values(
                array(
                    'surname' => '?',
                    'lastname' => '?',
                    'privateEmail' => '?',
                    'publicEmail' => '?',
                    'workType' => '?',
                    'doctor' => '?',
                )
            )
            ->setParameter(0, $surname)
            ->setParameter(1, $lastname)
            ->setParameter(2, $privatemail)
            ->setParameter(3, $publicmail)
            ->setParameter(4, $worktype)
            ->setParameter(5, $doctor);
            
        if ($qb->execute()) {
            return true;
        } else {
            return false;
        }

    }

    public static function getAllEmployees($dbal) {

        $qb = $dbal->createQueryBuilder();
        $qb->select("id, surname, lastname, privateEmail, publicEmail, workType, doctor");
        $qb->from("employee", "e");
        
        $result = $qb->execute()->fetchAll();

        $groups[] = null;
        foreach ($result as $res) {
            $groups[] = Employee::load($dbal, $res['id']);
        }

        return $groups;

    }

    public function getID() {
        return $this->data['id'];
    }

    public function getSurname() {
        return $this->data['surname'];
    }

    public function getLastname() {
        return $this->data['lastname'];
    }

    public function getPrivatemail() {
        return $this->data['privatEmail'];
    }

    public function getPublicmail() {
        return $this->data['publicEmail'];
    }

    public function getWorktype() {
        return $this->data['workType'];
    }

    public function getDoctor() {
        return $this->data['doctor'];
    }

}