<?php

namespace Yaoi\Database;

use Yaoi\Database;
use Yaoi\Database\Definition\Table;
use Yaoi\Mappable;
use Yaoi\Sql\SelectInterface;
use Yaoi\Sql\Statement;
use Yaoi\Sql\Symbol;
use Yaoi\BaseClass;
use Yaoi\Database\Definition\Column;
use Yaoi\String\Utils;

abstract class Entity extends BaseClass implements Mappable\Contract, Entity\Contract
{
    /**
     * @var Table[]
     */
    private static $tables = array();

    /**
     * Method should return table definition of entity
     * @return Table
     */
    public static function table()
    {
        $className = get_called_class();
        $table = &self::$tables[$className];
        if (null !== $table) {
            return $table;
        }
        $columns = new \stdClass();
        static::setUpColumns($columns);
        $schemaName = Utils::fromCamelCase(str_replace('\\', '', $className));
        $table = new Table($columns, self::getDatabase($className), $schemaName);
        $table->className = $className;
        static::setUpTable($table, $columns);

        return $table;
    }

    /**
     * @return \stdClass|static
     */
    public static function columns() {
        return static::table()->columns;
    }


    protected $persistent;


    /**
     * @param null|static $filter
     * @return SelectInterface
     */
    public static function statement($filter = null) {
        $className = get_called_class();
        $table = static::table();
        $statement = $table->database()->select($table);
        $statement->bindResultClass($className);

        if ($filter instanceof static) {
            foreach ($filter->toArray(true) as $name => $value) {
                $statement->where("? = ?", new Symbol($table->schemaName, $name), $value);
            }
        }
        return $statement;
    }

    /**
     * @param ...$id
     * @return static
     * @throws \Yaoi\Entity\Exception
     * @todo testdoc
     */
    public static function find($id)
    {
        $className = get_called_class();
        $table = static::table();
        $statement = $table->database()->select($table);
        $statement->bindResultClass($className);

        $args = func_get_args();
        $i = 0;
        foreach ($table->primaryKey as $keyField) {
            if (!isset($args[$i])) {
                throw new \Yaoi\Entity\Exception('Full primary key required', \Yaoi\Entity\Exception::KEY_MISSING);
            }
            $keyValue = $args[$i++];
            $statement->where('? = ?', new Symbol($table->schemaName, $keyField->schemaName), $keyValue);
        }
        return $statement->query()->fetchRow();
    }

    static function fromArray(array $row, $object = null, $source = null)
    {
        if (is_null($object)) {
            $object = new static;
        }

        foreach (static::table()->getColumns(true) as $column) {
            if (array_key_exists($column->schemaName, $row)) {
                $object->{$column->propertyName} = Column::castField($row[$column->schemaName], $column->flags);
            }
        }

        if ($source) {
            $object->persistent = true;
        }

        return $object;

    }

    public function toArray($skipNotSetProperties = false)
    {
        $result = array();
        foreach (static::table()->getColumns(true) as $column) {
            $value = $this->{$column->propertyName};
            if (null === $value) {
                if ($skipNotSetProperties) {
                    continue;
                }
            } else {
                $value = Column::castField($value, $column->flags);
            }

            $result[$column->schemaName] = $value;
        }
        return $result;
    }

    public function save()
    {
        if ($this->persistent) {
            $this->update();
        } else {
            $this->insert();
        }
    }


    public function delete() {
        $table = static::table();
        $delete = $table->database()->delete($table->schemaName);
        $data = $this->toArray();

        foreach ($table->primaryKey as $keyField) {
            if (!isset($this->{$keyField->schemaName})) {
                throw new \Yaoi\Entity\Exception('Primary key required for delete', \Yaoi\Entity\Exception::KEY_MISSING);
            }
            $delete->where("? = ?", new Symbol($keyField->schemaName), $data[$keyField->schemaName]);
        }

        $delete->query();
        $this->persistent = false;
        return $this;
    }


    public function update()
    {
        $table = static::table();
        $update = $table->database()->update($table->schemaName);
        $data = $this->toArray();

        foreach ($table->primaryKey as $keyField) {
            if (!isset($data[$keyField->schemaName])) {
                throw new \Yaoi\Entity\Exception('Primary key required for update', \Yaoi\Entity\Exception::KEY_MISSING);
            }
            $update->where("? = ?", new Symbol($keyField->schemaName), $data[$keyField->schemaName]);
            unset($data[$keyField->schemaName]);
        }
        $update->set($data);
        $update->query();
        $this->persistent = true;
        return $this;
    }

    public function insert()
    {
        $table = static::table();
        $insert = $table->database()->insert($table->schemaName);
        $data = $this->toArray();

        if ($autoId = $table->autoIdColumn) {
            if (empty($data[$autoId->schemaName])) {
                unset($data[$autoId->schemaName]);
            }
        }

        $insert->valuesRow($data);

        $query = $insert->query();

        if ($autoId) {
            if (empty($this->{$autoId->propertyName})) {
                $this->{$autoId->propertyName} = $query->lastInsertId();
            }
        }

        $this->persistent = true;
        return $this;
    }

    /**
     * @var Contract[]
     */
    private static $databases = array();
    public static function bindDatabase(Contract $database, $forceTableCacheClean = false) {
        $class = get_called_class();
        if ($forceTableCacheClean || !isset(self::$databases[$class]) || self::$databases[$class] !== $database) {
            self::$databases[$class] = $database;
            if (isset(self::$tables[$class])) {
                unset(self::$tables[$class]);
            }
        }
    }

    private static function getDatabase($class) {
        if (isset(self::$databases[$class])) {
            return self::$databases[$class];
        }
        else {
            return Database::getInstance();
        }
    }


    public function findOrSave() {
        /** @var static $item */
        $item = self::statement($this)->query()->fetchRow();
        if (!$item) {
            $this->save();
        }
        else {
            self::fromArray($item->toArray(), $this);
        }
        return $this;
    }


}