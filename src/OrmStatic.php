<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmStatic
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Class for static correspondence such as database connection destination, 
 * SQL queue, log output, transaction, etc.
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

// require if you didn't use a package management tool such as "commposer".
require_once "OrmMySql.php";
require_once "OrmSqLite.php";
require_once "OrmPgSql.php";
require_once "OrmOracle.php";

class OrmStatic{

    public const TRANSACTION_BEGIN = "BEGIN;";
    public const TRANSACTION_ROLLBACK = "ROLLBACK;";
    public const TRANSACTION_COMMIT = "COMMIT;";
    
    private static $_pdo = [];
    private static $_log = [];

    /**
     * existDriver
     * 
     * @param String $driveName
     * @return Boolean 
     */
    public static function existDriver($driveName){

        if(empty(self::$_pdo[$driveName])){
            return false;
        }

        return true;
    }

    /**
     * addConnect
     * 
     * Create a database attached drive.
     * 
     * @param String $drivename connection drive name
     * @param Array $option connection data
     */
    public static function addConnect($driveName, $option){

        if($option["driver"] == "mysql"){
            self::$_pdo[$driveName] = OrmMySql::connect($option);
        }
        else if($option["driver"] == "sqlite"){
            self::$_pdo[$driveName]  = OrmSqLite::connect($option);
        }
        else if($option["driver"] == "pgsql"){
            self::$_pdo[$driveName]  = OrmPgSql::connect($option);    
        }
        else if($option["driver"] == "oracle"){
            self::$_pdo[$driveName]  = OrmOracle::connect($option);
        }
    }

    /**
     * query
     * 
     * Send SQL request to connection destination database
     * 
     * @param String $driveName connection drive name
     * @param String $sql SQL code
     * @param Array $bind = [] SQL query bind data
     * @return PDOStatement PDOStatement object
     */
    public static function query($driveName, $sql, $bind = []){

        $std = self::$_pdo[$driveName]->prepare($sql);
        $std->execute($bind);

        // Addition of log information.
        self::$_log[] = [
            "date"=>date("Y/m/d H:i:s"),
            "drive"=>$driveName,
            "sql"=>$sql,
            "bind"=>$bind,
        ];

        return $std;
    }

    /**
     * transaction
     * 
     * Start Transaction, Commit, Rollback
     * 
     * @param String $mode Transaction execution mode (BEGIN,COMMIT,ROLLBACK)
     */
    public static function transaction($mode){
        foreach(self::$_pdo as $drive => $p_){
            self::query($drive, $mode);
        }
    }

    /**
     * begin
     * 
     * start transaction
     */
    public static function begin(){
        self::transaction(self::TRANSACTION_BEGIN);
    }

    /**
     * commit
     * 
     * commit transaction
     */
    public static function commit(){
        self::transaction(self::TRANSACTION_COMMIT);
    }

    /**
     * rollback
     * 
     * rollback transaction
     */
    public static function rollback(){
        self::transaction(self::TRANSACTION_ROLLBACK);
    }

    /**
     * getConnect
     * 
     * Switch destination database by drive
     * 
     * @param String $driveName Connection destination drive name
     */
    public static function getConnect($driveName){
        return self::$_pdo[$driveName];
    }

    /**
     * log
     * 
     * Get SQL queue log information
     * 
     * @return Array SQL queue log information
     */
    public static function log(){
        return self::$_log;
    }

}