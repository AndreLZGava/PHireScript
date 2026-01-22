<?php


namespace PHireScript\Classes;


use PHireScript\Classes\Repository;


 class UserRepository extends Repository {
    public string $tableName = 'user';
    public function getUser( $id): null|self {
        return Null;
    }

}

