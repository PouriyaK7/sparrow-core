<?php


namespace Sparrow;

use mysqli;
use mysqli_result;
use mysqli_stmt;

class DB
{

    /**
     * @var null|mixed $DB
     */

    private static mixed $DB;

    /**
     * @var mixed $stmt
     */

    private static mixed $stmt;

    /**
     * @return mysqli
     * Returns UTF-8 mysql connection.
     */

    public static function connect() : mysqli {
        if (!isset(self::$DB)) {
            self::$DB = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            self::$DB->set_charset('utf8');
        }
        return self::$DB;
    }

    /**
     * @param string $query
     * @param string|null $className
     * @param string|null $bindParams
     * @param int $stmtResult
     * @param mixed ...$values
     * @return object[]|array[]|bool|mysqli_stmt|mysqli_result
     */

    public const
        NUM_ROWS = 6, FETCH_ALL = 0, EXECUTED = 3,
        AFFECTED_ROWS = 4, INSERT_ID = 5, GET_RESULT = 2, STMT = 1;

    public static function prepare(string $query, ?string $className = null, int $stmtResult = 0, ?string $bindParams = null, ...$values) {
        self::$stmt = self::connect()->prepare($query);
        if ($stmtResult == 1)
            return self::$stmt;
        if (!is_null($bindParams)) {
            self::$stmt->bind_param($bindParams, ...$values);
        }
        $execute = self::$stmt->execute();
        if ($stmtResult == 3)
            return $execute;
        if ($stmtResult == 4)
            return self::$stmt->affected_rows;
        $result = self::$stmt->get_result();
        if ($stmtResult == 5)
            return self::$stmt->insert_id;
        if ($stmtResult == 6)
            return self::$stmt->num_rows;
        if ($stmtResult == 2)
            return $result;
        if ($result->num_rows) {
            if (!is_null($className))
                while ($item = self::fetch_object($result, $className))
                    $return[] = $item;
            else
                while ($item = $result->fetch_assoc())
                    $return[] = $item;
            return $return ?? [];
        }
        return [];
    }

    /**
     * @param $stmt
     * @param array|null $bind
     * @param false $noresult
     * @return bool|mysqli_result|mysqli_stmt
     */

    static public function fastStmt(&$stmt, ?array $bind = null, $noresult = false): mysqli_result|bool|mysqli_stmt
    {
        if(is_string($stmt))
            $stmt = self::connect()->prepare($stmt);
        if(is_object($stmt) && $stmt instanceof mysqli_stmt)
            if(isset($bind)){
                if(count($bind))
                    $stmt->bind_param(str_repeat('s', count($bind)), ...$bind);
                if($res = $stmt->execute())
                    return $noresult?: ($stmt->get_result()?: true);
            }
            else return $stmt;
        return false;
    }

    /**
     * @param mysqli_result $result
     * @param string|null $className
     * @return object
     */

    public static function fetch_object(mysqli_result $result, string $className = null): object
    {
        return $result->fetch_object($className);
    }

    /**
     * Kills connection to database
     */

    public static function end() {
        mysqli_close(self::$DB);
        self::$DB = null;
    }

    /**
     * @param mixed $stmt
     * @return mysqli_stmt|object[]
     */

    public static function stringToStmt(mixed $stmt): array|mysqli_stmt
    {
        return is_string($stmt)? self::connect()->prepare($stmt): $stmt;
    }
}