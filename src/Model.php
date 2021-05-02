<?php


namespace Sparrow;


use Exception;

class Model
{


    protected array $validationRules = [];

    protected array $validationMessages = [];

    /**
     * @var string $table Stores table name
     */
    protected static string $table;

    /**
     * @var string $primary Stores primary field's name
     */
    protected static string $primary;

    /**
     * @var object $model
     */
    public object $model;

    /**
     * Model constructor.
     * @param null $id
     */

    public function __construct($id = null)
    {
        if (!isset(self::$primary))
            self::$primary = 'id';
        if (isset($id)) {
            $model = self::find($id);
            foreach (get_object_vars($model) as $index => $item)
                $this->{$index} = $item;
        }
    }

    /**
     * @param $id
     * @return $this
     */
    public static function find($id): Model
    {
        return DB::prepare('SELECT * FROM ' . static::$table . ' WHERE ' . static::$primary . ' = ?', static::class, 0, 's', $id)[0];
    }

    /**
     * @return array
     */

    public static function all(): array {
        return DB::prepare('SELECT * FROM ' . static::$table, null, DB::FETCH_ALL);
    }

    /**
     * @param int $page
     * @param int $itemsCount
     * @return array
     */

    public static function paginate(int $page, $itemsCount = 20): array {
        return DB::prepare('SELECT * FROM ' . self::$table . ' LIMIT ?,?', null, 0, 'ii', $page, $itemsCount);
    }

    /**
     * Deletes the row of object
     */

    public function delete() {
        DB::prepare('DELETE FROM ' . self::$table . ' WHERE ' . self::$primary . ' = ' . $this->{self::$primary}, null, 2);
    }

    /**
     * @return bool
     */

    public function save(): bool {
        $text = '';
        foreach ($this as $key => $item)
            if ($key != 'primary' && $key != 'table')
                $text .= $key . ' = ' . DB::connect()->escape_string($item) . ',';
        $text = substr($text, 0, strlen($text)-1);
        if (isset($this->{self::$primary})) {
            $stmt = DB::connect()->prepare('UPDATE ' . self::$table . ' SET ' . $text . ' WHERE ' . self::$primary . ' = ?');
            $stmt->bind_param('s', $this->{self::$primary});
            return $stmt->execute();
        }
        else {
            $fieldNames = '(';
            $params = '';
            $questionMarks = '(';
            foreach ($this as $index => $item) {
                if ($index != 'primary' && $index != 'table') {
                    $fieldNames .= '`' . $index . '`,';
                    if (is_int($item))
                        $params .= 'i';
                    else
                        $params .= 's';
                    $questionMarks .= '?,';
                    $values[] = $item;
                }
            }
            $fieldNames .= substr($fieldNames, 0, strlen($fieldNames)-1) . ')';
            $questionMarks .= substr($questionMarks, 0, strlen($questionMarks)-1) . ')';
            return DB::prepare('INSERT INTO ' . self::$table . $fieldNames . ' VALUES ' . $questionMarks, null, 0, $params, $values ?? []);
        }
    }

    public static function query(array $conditions, string $fields = '') {
        try {
            $tableName = static::$table;
        }
        catch (Exception $e) {
            $arr = explode('\\', static::class);
            $tableName = strtolower(end($arr)) . 's';
            if ($tableName == 'withdraw')
                $tableName = 'withdraws';
        }


        $query = 'SELECT ' . ($fields == ''? '*': $fields) . ' FROM ' . $tableName . (isset($conditions[0])? ' WHERE ': '') . ($conditions[0] ?? '');
        return DB::prepare($query, null, 0);
    }

    /**
     * @param $field
     * @param $operator
     * @param $value
     * @return array[]|bool|\mysqli_result|\mysqli_stmt|object[]
     */

    public static function where($field, $operator, $value)
    {
        try {
            $tableName = static::$table;
        }
        catch (Exception $e) {
            $arr = explode('\\', static::class);
            $tableName = strtolower(end($arr)) . 's';
            if ($tableName == 'withdraw')
                $tableName = 'withdraws';
        }

        $query = 'SELECT * FROM ' . $tableName . ' WHERE `' . $field . '` ' . $operator . ' ?';
        return DB::prepare(
            $query,
            null,
            0,
            's',
            $value
        );
    }

    /**
     * @param array $data
     * @return array|bool|int|mixed|\mysqli_result|\mysqli_stmt
     */

    public static function create(array $data)
    {
        try {
            $tableName = static::$table;
        }
        catch (Exception $e) {
            $arr = explode('\\', static::class);
            $tableName = strtolower(end($arr)) . 's';
            if ($tableName == 'withdraw')
                $tableName = 'withdraws';
        }

        foreach ($data as $key => $value) {
            $fields[] = $key;
            $values[] = $value;
            $questionMarks[] = '?';
            $bindParams[] = 's';
        }

        if (!isset($fields) || !isset($values)) {
            return 0;
        }

        $query = 'INSERT INTO `' . $tableName . '`(' . implode(',', $fields) . ') VALUES (' . implode(',', $questionMarks) . ')';

        return DB::prepare(
            $query,
            null,
            DB::INSERT_ID,
            implode(',', $bindParams),
            ...$values
        );
    }
}