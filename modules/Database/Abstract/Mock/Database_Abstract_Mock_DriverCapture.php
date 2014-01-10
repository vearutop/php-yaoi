<?php

abstract class Database_Abstract_Mock_DriverCapture extends Database_Driver implements Mock_Able {
    /**
     * @var Database_Driver
     */
    protected $driver;

    /**
     * @param Database_Driver $driver
     */
    public function setOriginalDriver(Database_Driver $driver) {
        $this->driver = $driver;
    }

    public function query($statement)
    {
        $queryMock = $this->mock->branch2(self::QUERY, $statement);
        $queryMock->temp(self::RESULT, $this->driver->query($statement));
        return $queryMock;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function lastInsertId($queryMock)
    {
        $res = $this->driver->lastInsertId($queryMock->temp(self::RESULT));
        $queryMock->add2($res, self::LAST_INSERT_ID);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rowsAffected($queryMock)
    {
        $res = $this->driver->rowsAffected($queryMock->temp(self::RESULT));
        $queryMock->add2($res, self::ROWS_AFFECTED);
        return $res;
    }


    public function escape($value)
    {
        $res = $this->driver->escape($value);
        $this->mock->branch2(self::ESCAPE)->add2($res, $value);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rewind($queryMock)
    {
        $res = $this->driver->rewind($queryMock->temp(self::RESULT));
        $queryMock->branch2(self::REWIND)->add2($res);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     */
    public function fetchAssoc($queryMock)
    {
        $row = $this->driver->fetchAssoc($queryMock->temp(self::RESULT));
        $queryMock->branch2(self::ASSOC_ROWS)->add2($row);
        return $row;
    }


    /**
     * @var Mock_DataSetCapture
     */
    protected $mock;
    public function mock(Mock_DataSet $dataSet = null)
    {
        if ($dataSet instanceof Mock_DataSetCapture) {
            $this->mock = $dataSet;
        }
        else {
            throw new Mock_Exception(Mock_Exception::CAPTURE_REQUIRED, 'Capture data set required');
        }
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     */
    public function queryErrorMessage($queryMock)
    {
        $err = $this->driver->queryErrorMessage($queryMock->temp(self::RESULT));
        $queryMock->add2($err, self::ERROR_MESSAGE);
        return $err;
    }



}