<?php

const DB_HOST = "";
const DB_NAME = "";
const DB_USER = "";
const DB_PASSWORD = "";

class DbManager
{
    private static function getConnection()
    {
        $pdoConfig  = "sqlsrv:Server=" . DB_HOST . ";Database=" . DB_NAME . ";";

        try {
            if (!isset($connection)) {
                $connection =  new PDO($pdoConfig, DB_USER, DB_PASSWORD);
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return $connection;
        } catch (PDOException $e) {
            $mensagem = "Drivers disponiveis: " . implode(",", PDO::getAvailableDrivers());
            $mensagem .= "\nErro: " . $e->getMessage();
            throw new Exception($mensagem);
        }
    }
    
    public static function getFirstPending(){
        return self::getConnection()->query("SELECT TOP 1 * FROM repositorios where status = 0")->fetch();
    }

    public static function changeStatus($name,$newStatus){
        $query = "UPDATE repositorios set status = '$newStatus' where name_with_owner = '$name'";
        self::getConnection()->prepare($query)->execute();
    }
    
    public static function insertMany($repoList){
        $flatennedRepos = implode("'),('",$repoList);
        $query = "INSERT INTO [repositorios] (name_with_owner) VALUES ('".$flatennedRepos."')";
        self::getConnection()->prepare($query)->execute();
    }

    public static function insertPr($prLine,$idRepositorio,$endDate){
        $query = 'INSERT INTO pull_requests (id_repositorio, created_at, closed_at, [state], diff_size, files, [description], comments, reviews, assignees) VALUES (?,?,?,?,?,?,?,?,?,?)';
        self::getConnection()->prepare($query)->execute([
            $idRepositorio,
            $prLine["createdAt"],
            $endDate,
            $prLine['state'],
            $prLine['additions'] + $prLine['deletions'],
            $prLine['files']['totalCount'],
            $prLine['body'],
            $prLine['comments']['totalCount'],
            $prLine['reviews']['totalCount'],
            $prLine['assignees']['totalCount']
        ]);
    }
}
