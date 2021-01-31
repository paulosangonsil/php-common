<?php
require_once 'includes.inc';

/**
 * @author Administrator
 *
 */
class MySQL_PDODriver extends \Abstract_DBAccess {
    private /*PDO*/ $_objPDO;
    private /*PDOStatement*/    $_objQuery;

    /**
     */
    public function __construct(/*string*/ $hostName, /*string*/ $dbName) {
        parent::__construct ($hostName, $dbName);
    }

    protected function _init() {
        try {
            $dsn = "mysql:host=" . $this->get_nameHost() . ";dbname=" . $this->get_nameDB();

            $this->set_objPDO( new PDO( $dsn, $this->get_nameUser(),
                $this->get_passwordUser() ) );
        } catch (PDOException $e){
            $this->set_creationException($e);
        }
    }

    protected function _assembleSQLConditionalAnd($inStr, $key, $cond, $value): string {
        $inStr .= "`$key` $cond ";

        if ( is_numeric ($value) ) {
            $inStr .= "$value";
        }
        else if ( is_null($value) ) {
            $inStr .= 'NULL';
        }
        else {
            $inStr .= "'$value'";
        }

        return $inStr;
    }

    public function processSimpleQuery(/*Map<key, value*/ $valuesMap): string {
        $strValues = "";

        if ($valuesMap != NULL) {
            $firstIter = TRUE;

            foreach ($valuesMap as $itemKey => $itemValue) {
                if ($firstIter) {
                    $firstIter = FALSE;
                }
                else {
                    $strValues .= " AND ";
                }

                if (is_array($itemValue)){
                    $strTmp = '';

                    $condSign = '=';

                    if (array_key_exists($itemValue, Abstract_DBAccess::IDX_NAME_COND_SYMBOL)){
                        $condSign = $itemValue[Abstract_DBAccess::IDX_NAME_COND_SYMBOL];
                    }

                    $firstSubIter = TRUE;

                    foreach ($itemValue as $itemSubKey => $itemSubValue) {
                        if (strcmp($itemSubKey, Abstract_DBAccess::IDX_NAME_COND_SYMBOL) != 0) {
                            if ($firstSubIter) {
                                $firstSubIter = FALSE;
                            }
                            else {
                                $strTmp .= " AND ";
                            }

                            $strTmp .= $this->_assembleSQLConditionalAnd
                                        ($strTmp, $itemSubKey, $condSign, $itemSubValue);
                        }
                    }

                    $strValues .= "($strTmp)";
                }
                else {
                    $strValues .= $this->_assembleSQLConditionalAnd
                                    ($strValues, $itemKey, '=', $itemValue);
                }
            }
        }

        return $strValues;
    }

    public function processInStrQuery(/*Map<key, value*/ $valuesMap): string {
        $strValues = "";

        if ($valuesMap != NULL) {
            $firstIter = TRUE;

            foreach ($valuesMap as $itemKey => $itemValue) {
                if ($firstIter) {
                    $firstIter = FALSE;
                }
                else {
                    $strValues .= " AND ";
                }

                $strValues .= "(INSTR(`$itemKey`, '$itemValue') > 0)";
            }
        }

        return $strValues;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_DBAccess::query()
     */
    public function query(/*string*/ $nameTb, /*string*/ $namesColToRet, /*Map<colName: string, colValue: Object>*/ $valuesMap = NULL,
        /*Map<colName: string; order: Abstract_DBAccess::LIST_ORDER_*>*/ $order = NULL,
        /*int*/ $numPgOffset = 0, /*int*/ $numMaxItems = 0): bool {
        return $this->_queryBase('processSimpleQuery', $nameTb, $namesColToRet, $valuesMap, $order, $numPgOffset, $numMaxItems);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_DBAccess::query()
     */
    protected function _queryBase(/*string*/ $variationFn, /*string*/ $nameTb, /*string*/ $namesColToRet,
        /*Map<colName: string, colValue: Object>*/ $conditions = NULL,
        /*Map<colName: string; order: Abstract_DBAccess::LIST_ORDER_*>*/ $order = NULL,
        /*int*/ $numPgOffset = 0, /*int*/ $numMaxItems = 0): bool {
        $mtdRet = FALSE;

        if ($this->get_objPDO() == NULL) {
            goto mtdEnd;
        }

        $strQuery = "SELECT $namesColToRet FROM `" . $this->get_nameDB() . "`.`$nameTb`";

        $strValues = NULL;

        if ($conditions != NULL) {
            if ( is_array($conditions) ) {
                if ($variationFn != NULL) {
                    $strValues = call_user_func(array($this, $variationFn), $conditions);
                }
            }
            else if ( is_string($conditions) ) {
                $strValues = $conditions;
            }
        }

        if ($strValues != NULL) {
            $strQuery .= " WHERE $strValues";
        }

        if ($order != NULL) {
            foreach ($order as $nameCol => $orderCol) {
                $strQuery .= " ORDER BY `$nameCol` ";

                if ($orderCol == Abstract_TbRec::LIST_ORDER_ASC) {
                    $strQuery .= "ASC";
                }
                else if ($orderCol == Abstract_TbRec::LIST_ORDER_DESC) {
                    $strQuery .= "DESC";
                }
            }
        }

        if ( /*($numPgOffset != 0) &&(*/ ($numMaxItems > $numPgOffset) ) {
            $strQuery .= " LIMIT " . $numPgOffset * $numMaxItems .
                            ", " . $numMaxItems;
        }

        $mtdRet = $this->rawquery($strQuery);

        mtdEnd:
        return $mtdRet;
    }

    public function rawquery(/*string*/ $query): bool {
        $this->set_objQuery( $this->get_objPDO()->query($query) );

        return ($this->_getLastQueryResult() != FALSE);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_DBAccess::queryMixed()
     */
    public function queryMixed(/*string*/ $nameTb, /*string*/ $namesColToRet,
        /*List<string>*/ $condArray = NULL,
        /*Map<colName: string; order: Abstract_TbRec::LIST_ORDER_*>*/ $order = NULL,
        /*int*/ $numPgOffset = 0, /*int*/ $numMaxItems = 0): bool {
        return $this->_queryBase(NULL, $nameTb, $namesColToRet, $condArray, $numPgOffset, $numMaxItems);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_DBAccess::_getLastQueryResult()
     */
    protected function _getLastQueryResult() {
        return $this->_objQuery;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_DBAccess::getLastQueryResult()
     */
    public function getLastQueryResult() {
        return $this->_objQuery->fetchAll();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_DBAccess::getGeneratedIncNumber()
     */
    public function getGeneratedIncNumber(): int {
        $mtdRet = UNDEFINED;

        if ($this->get_objPDO() != NULL) {
            $mtdRet = $this->get_objPDO()->lastInsertId();
        }

        return $mtdRet;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_DBAccess::insert()
     */
    public function insert($nameTb, $valuesMap) {
        $mtdRet = FALSE;

        if ( ($this->get_objPDO() == NULL) &&
                ($valuesMap != NULL) ) {
            goto mtdEnd;
        }

        $strQuery = "INSERT INTO `" . $this->get_nameDB() . "`.`$nameTb` (";

        $strValues = "";

        foreach ($valuesMap as $itemKey => $itemValue) {
            if ( ! empty($strValues) ) {
                $strValues .= ", ";
            }

            $strValues .= "$itemKey";
        }

        $strQuery .= "$strValues) VALUES (";

        $strValues = "";

        foreach ($valuesMap as $itemKey => $itemValue) {
            if ( ! empty($strValues) ) {
                $strValues .= ", ";
            }

            if ( is_numeric ($itemValue) ) {
                $strValues .= "$itemValue";
            }
            else {
                $strValues .= "'$itemValue'";
            }
        }

        $strQuery .= "$strValues)";

        $this->set_objQuery( $this->get_objPDO()->query($strQuery) );

        $mtdRet = ($this->_getLastQueryResult() != FALSE);

        mtdEnd:
        return $mtdRet;
    }

    /**
     *
     * {@inheritDoc}
     * @see Abstract_DBAccess::update()
     */
    public function update($nameTb, $valuesMap, $conditionsMap = NULL) {
        $mtdRet = FALSE;

        if ( ($this->get_objPDO() == NULL) ||
            ($valuesMap == NULL) ) {
            goto mtdEnd;
        }

        $strQuery = "UPDATE `" . $this->get_nameDB() . "`.`$nameTb` SET ";

        $firstIter = TRUE;

        foreach ($valuesMap as $itemKey => $itemValue) {
            if ($firstIter) {
                $firstIter = FALSE;
            }
            else {
                $strQuery .= ", ";
            }

            $strQuery .= "`$itemKey`=";

            if ( is_numeric ($itemValue) ) {
                $strQuery .= "$itemValue";
            }
            else {
                $strQuery .= "'$itemValue'";
            }
        }

        if ($conditionsMap != NULL) {
            $strQuery .= " WHERE ";

            $firstIter = TRUE;

            foreach ($conditionsMap as $itemKey => $itemValue) {
                if ($firstIter) {
                    $firstIter = FALSE;
                }
                else {
                    $strQuery .= " AND ";
                }

                $strQuery .= "`$itemKey`=";

                if ( is_numeric ($itemValue) ) {
                    $strQuery .= "$itemValue";
                }
                else {
                    $strQuery .= "'$itemValue'";
                }
            }
        }

        $this->set_objQuery( $this->get_objPDO()->query($strQuery) );

        $mtdRet = ($this->_getLastQueryResult() != FALSE);

        mtdEnd:
        return $mtdRet;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_DBAccess::delete()
     */
    public function delete($nameTb, $valuesMap): bool {
        $mtdRet = FALSE;

        if ($this->get_objPDO() == NULL) {
            goto mtdEnd;
        }

        $strQuery = "DELETE FROM `" . $this->get_nameDB() . "`.`$nameTb`";

        $strValues = NULL;

        if ($valuesMap != NULL) {
            $firstIter = TRUE;

            do {
                $itemValue = current($valuesMap);

                if ( $itemValue === FALSE ) continue;

                $itemKey = key($valuesMap);

                if (! $firstIter) {
                    $strQuery .= " AND ";
                }
                else {
                    $strQuery .= " WHERE ";

                    $firstIter = FALSE;
                }

                if ( is_array($itemValue) ) {
                    $firstIter01 = TRUE;

                    $strValues = "";

                    foreach ($itemValue as $innerValue) {
                        if (! $firstIter01) {
                            $strValues .= ", ";
                        }
                        else {
                            $firstIter01 = FALSE;
                        }

                        if ( is_numeric ($innerValue) ) {
                            $strValues .= "$innerValue";
                        }
                        else {
                            $strValues .= "'$innerValue'";
                        }
                    }

                    $strQuery .= "`$itemKey` IN ($strValues)";
                }
                else {
                    $strQuery .= "`$itemKey`=";

                    if ( is_numeric ($itemValue) ) {
                        $strQuery .= "$itemValue";
                    }
                    else {
                        $strQuery .= "'$itemValue'";
                    }
                }

                next($valuesMap);
            } while ( $itemValue !== FALSE );
        }

        $this->set_objQuery( $this->get_objPDO()->query($strQuery) );

        $mtdRet = ($this->_getLastQueryResult() != FALSE);

        mtdEnd:
        return $mtdRet;
    }

    /**
     * _objPDO
     * @return PDO
     */
    protected function get_objPDO(){
        return $this->_objPDO;
    }

    /**
     * _objPDO
     * @param PDO $_objPDO
     */
    protected function set_objPDO($_objPDO){
        $this->_objPDO = $_objPDO;
    }

    /**
     * _objQuery
     * @param PDOStatement $_objQuery
     */
    protected function set_objQuery($_objQuery){
        $this->_objQuery = $_objQuery;

        if ( is_object($_objQuery) ) {
            $this->set_creationException($_objQuery->errorInfo());
        }
    }
}
