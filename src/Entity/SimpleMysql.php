<?php
namespace Yaoi\Entity;

use Closure;
use Yaoi\Database;
use Yaoi\Sql\SelectInterface;
use Yaoi\Sql\Symbol;
use Yaoi\BaseClass;
use Yaoi\Database\Contract;

/**
 * Class Entity_SimpleMysql
 * @deprecated
 */
class SimpleMysql extends BaseClass
{
    /**
     * @var string
     */
    static public $tableName;
    static public $idField = 'id';
    static public $uniqueKey = array();

    private $fetched = false;

    private static $columns = array();

    private static $databases = array();

    public static function bindDatabase(Contract $db)
    {
        self::$databases[get_called_class()] = $db;
    }


    public static function getTableName()
    {
        if (null === static::$tableName) {
            return get_called_class();
        } else {
            return static::$tableName;
        }
    }


    public static function getIdName()
    {
        return static::$idField;
    }

    /**
     * @return Contract
     */
    private static function db()
    {
        $class = get_called_class();
        if (isset(self::$databases[$class])) {
            return self::$databases[$class];
        } else {
            return Database::getInstance($class);
        }
    }

    public static function getColumns()
    {
        $tableName = self::getTableName();
        if (!isset(self::$columns[$tableName])) {
            self::$columns[$tableName] = self::db()
                ->query("DESC `$tableName`")
                ->fetchPairs(0, 0);
        }
        return self::$columns[$tableName];
    }


    /**
     * @param $id
     * @return $this
     */
    public static function getById($id)
    {
        $idField = static::$idField;
        $select = static::select();
        $row = $select
            ->where("? = ?", new Symbol($idField), $id)
            ->query()
            ->fetchRow();

        if ($row) {
            $obj = static::fromArray($row);
            $obj->fetched = true;

            return $obj;
        } else {
            return null;
        }
    }

    /**
     * @return SelectInterface
     */
    public static function select()
    {
        $select = self::db()->select(self::getTableName());
        return $select;
    }

    /**
     * @param $columnValues
     * @param Closure $withSelectDo
     * @return static[]
     */
    public static function getBy($columnValues, Closure $withSelectDo = null)
    {
        if ($columnValues instanceof static) {
            $columnValues = $columnValues->toArray(true);
        }

        $columns = self::getColumns();

        $select = static::select();
        foreach ($columnValues as $column => $value) {
            if (isset($columns[$column])) {
                $select->where("? = ?", new Symbol($column), $value);
            }
        }

        if ($withSelectDo) {
            $withSelectDo($select);
        }

        $result = array();
        foreach ($select->query() as $row) {
            $obj = new static();
            $obj->fetched = true;
            foreach ($row as $key => $value) {
                $obj->$key = $value;
            }
            $result [] = $obj;
        }
        return $result;
    }


    public function update()
    {
        $update = self::db()->update(self::getTableName());
        $data = array();
        foreach (static::getColumns() as $column) {
            if (property_exists($this, $column)) {
                $data[$column] = $this->$column;
            }
        }
        $idField = static::$idField;
        $update->where("`$idField` = ?", $this->$idField);
        $update->set($data);
        unset($data[$idField]);
        $update->query();
        $this->fetched = true;
        return $this;
    }

    public function insert()
    {
        $insert = self::db()->insert(self::getTableName());
        $data = array();
        foreach (static::getColumns() as $column) {
            if (property_exists($this, $column)) {
                $data[$column] = $this->$column;
            }
        }
        $insert->valuesRow($data);
        if (!is_array(static::$idField)) {
            if (!isset($data[static::$idField])) {
                $id = $insert->query()->lastInsertId();
                $this->{static::$idField} = $id;
            }
        } else {
            $insert->query();
        }

        $this->fetched = true;

        return $this;
    }

    public function save()
    {
        if ($this->fetched) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    public static function fromArray($data)
    {
        $instance = new static();
        foreach ($data as $name => $value) {
            $instance->$name = $value;
        }
        return $instance;
    }

    public function toArray($skipUndefined = false)
    {
        $columns = self::getColumns();
        $result = array();
        if ($skipUndefined) {
            $data = (array)$this;
            foreach ($columns as $column) {
                if (array_key_exists($column, $data)) {
                                    $result[$column] = $this->$column;
                }
            }
        } else {
            foreach ($columns as $column) {
                $result[$column] = isset($this->$column) ? $this->$column : null;
            }
        }
        return $result;
    }


    public function bindByUnique()
    {
        if ($this->fetched) {
            return false;
        }
        if (static::$uniqueKey) {
            if (!is_array(static::$uniqueKey)) {
                static::$uniqueKey = array(static::$uniqueKey);
            }

            $select = static::select();
            foreach (static::$uniqueKey as $uniqueField) {
                if (!isset($this->$uniqueField)) {
                    return false;
                }
                $select->where('? = ?', new Symbol($uniqueField), $this->$uniqueField);
            }
            $row = $select->query()->rowsAffectedIn($rowsCount)->fetchRow();
            if ($rowsCount > 1 || !$rowsCount) {
                return false;
            }

            foreach ($row as $key => $value) {
                if (!isset($this->$key)) {
                    $this->$key = $value;
                }
            }
            $this->fetched = true;
            return true;
        }
        return false;
    }

    public function getId()
    {
        $idField = static::$idField;
        if (!isset($this->$idField)) {
            if (!$this->fetched) {
                $this->bindByUnique();
            }
            if (!$this->fetched) {
                $this->save();
            }
        }

        return $this->$idField;
    }

    public function remove()
    {
        if ($this->fetched) {
            $idField = static::$idField;
            static::removeById($this->$idField);
            $this->fetched = false;
        }
    }

    public static function removeById($id)
    {
        self::db()->delete(static::$tableName)->where('? = ?', new Symbol(static::$idField), $id);
    }


    protected $relationData;

}