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
        $qb->select("id, surname, lastname, privateEmail, publicEmail, workType, doctor, points");
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
        $qb->select("id, surname, lastname, privateEmail, publicEmail, workType, doctor, points");
        $qb->from("employee", "e");
        
        $result = $qb->execute()->fetchAll();

        $groups[] = null;
        foreach ($result as $res) {
            $groups[] = Employee::load($dbal, $res['id']);
        }

        return $groups;

    }

    public static function addPointsToEmployee($dbal, $idEmployee, $points) {

        $employee = Employee::load($dbal, $idEmployee);
        if (!is_null($employee)) {
            $currentPoints = $employee->getPoints();
            $points += $currentPoints;

            $qb = $dbal->createQueryBuilder();
            $qb->update('employee', 'e');
            $qb->set('e.points', ':points');
            $qb->where('e.id = :idEmployee');
            $qb->setParameter('points', $points);
            $qb->setParameter('idEmployee', $idEmployee);
            $qb->execute();
            
            $employee = Employee::load($dbal, $idEmployee);
        }

        return $employee;

    }

    public static function substractPointsFromEmployee($dbal, $idEmployee, $points) {

        $employee = Employee::load($dbal, $idEmployee);
        if (!is_null($employee)) {
            $currentPoints = $employee->getPoints();
            $currentPoints -= $points ;
            if ($currentPoints < 0) {
                $currentPoints = 0;
            }

            $qb = $dbal->createQueryBuilder();
            $qb->update('employee', 'e');
            $qb->set('e.points', ':points');
            $qb->where('e.id = :idEmployee');
            $qb->setParameter('points', $currentPoints);
            $qb->setParameter('idEmployee', $idEmployee);
            $qb->execute();
            
            $employee = Employee::load($dbal, $idEmployee);
        }

        return $employee;

    }

    public static function getEmployeePoints($dbal, $idEmployee) {

        $qb = $dbal->createQueryBuilder();
        $qb->select("points");
        $qb->from("employee", "e");
        $qb->where("e.id = :employeeId")->setParameter("employeeId", $idEmployee);
        $employee = $qb->execute()->fetch();
        return $employee['points'];

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

    public function getPoints() {
        return $this->data['points'];
    }

}