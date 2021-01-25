<?php

/**
 * @author Administrator
 *
 */
abstract class Abstract_DBAccess {
    private /*string*/ $_nameHost;
    private /*string*/ $_nameDB;
    private /*string*/ $_nameUser;
    private /*string*/ $_passwordUser;
    private /*Exception*/ $_creationException    = NULL;

    const IDX_NAME_COND_SYMBOL = 'condition';

    /**
     */
    public function __construct(/*string*/ $hostName, /*string*/ $dbName) {
        $this->set_nameDB($dbName);

        $this->set_nameHost($hostName);
    }

    public function setCredentials(/*string*/ $nameUsr, /*string*/ $passWord) {
        $this->set_nameUser($nameUsr);

        $this->set_passwordUser($passWord);

        $this->_init();
    }

    protected abstract function  _init ();

    /*+insert(tbName: string; valuesMap: Map<colName: string; colValue: Object>): int*/
    public abstract function insert(/*string*/ $nameTb, /*Map<colName: string, colValue: Object>*/ $valuesMap);

    public abstract function update(/*string*/ $nameTb, /*Map<colName: string, colValue: Object>*/ $valuesMap,
        /*Map<colName: string, colValue: Object>*/ $conditionsMap = NULL);

    /*+query(tbName: string; colToRet: string; valuesMap: Map<colName: string, colValue: Object>): int*/
    public abstract function query(/*string*/ $nameTb, /*string*/ $namesColToRet,
                    /*Map<colName: string; colValue: Object>*/ $valuesMap = NULL,
                    /*Map<colName: string; order: Abstract_TbRec::LIST_ORDER_*>*/ $order = NULL,
                    /*int*/ $numPgOffset = 0, /*int*/ $numMaxItems = 0): bool;

    public abstract function queryMixed(/*string*/ $nameTb, /*string*/ $namesColToRet,
        /*List<string>*/ $condArray = NULL,
        /*Map<colName: string; order: Abstract_TbRec::LIST_ORDER_*>*/ $order = NULL,
        /*int*/ $numPgOffset = 0, /*int*/ $numMaxItems = 0): bool;

    public abstract function rawquery(/*string*/ $query): bool;

    public abstract function processSimpleQuery(/*Map<key, value*/ $valuesMap): string;
    public abstract function processInStrQuery(/*Map<key, value*/ $valuesMap): string;

    /*+getLastQueryResult(): List<Object>*/
    public abstract /*List<Object>*/ function getLastQueryResult();

    /*+delete(tbName: string; valuesMap: Map<colName: string; colValue: Object>): int*/
    public abstract function delete(/*string*/ $nameTb, /*Map<colName: string; colValue: Object>*/ $valuesMap);

    /**
     * _nameHost
     * @return string
     */
    protected function get_nameHost(){
        return $this->_nameHost;
    }

    /**
     * _nameHost
     * @param string $_nameHost
     */
    protected function set_nameHost($_nameHost){
        $this->_nameHost = $_nameHost;
    }

    /**
     * _nameDB
     * @return string
     */
    protected function get_nameDB(){
        return $this->_nameDB;
    }

    /**
     * _nameDB
     * @param string $_nameDB
     */
    protected function set_nameDB($_nameDB){
        $this->_nameDB = $_nameDB;
    }

    /**
     * _nameUser
     * @return string
     */
    protected  function get_nameUser(){
        return $this->_nameUser;
    }

    /**
     * _nameUser
     * @param string $_nameUser
     */
    protected function set_nameUser($_nameUser){
        $this->_nameUser = $_nameUser;
    }

    /**
     * _passwordUser
     * @return string
     */
    protected function get_passwordUser(){
        return $this->_passwordUser;
    }

    /**
     * _passwordUser
     * @param string $_passwordUser
     */
    protected function set_passwordUser($_passwordUser){
        $this->_passwordUser = $_passwordUser;
    }

    /**
     * _creationException
     * @return Exception
     */
    public function get_creationException(){
        return $this->_creationException;
    }

    /**
     * _creationException
     * @param Exception $_creationException
     */
    protected function set_creationException($_creationException){
        $this->_creationException = $_creationException;
    }
}
