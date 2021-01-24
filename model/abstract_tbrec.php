<?php

/**
 * @author Administrator
 *
 */
abstract class Abstract_TbRec {
    const   OFFSET_PAGE_NONE    = -997,
            OFFSET_PAGE_FIRST   = -998,
            OFFSET_PAGE_LAST    = -999,
            OFFSET_PAGE_NMB     = 0,

            LIST_ORDER_ASC      = 1,
            LIST_ORDER_DESC     = 2;

    const   TIMESTAMP_FORMAT    = "YmdHis";
    const   TIMESTAMP_FORMAT_DB = "Y-m-d H:i:s";
    const   DATE_FORMAT_DB      = "Y-m-d";
    
    protected /*int*/ $_id    = UNDEFINED;
    protected /*List<int>*/ $_listFldsModified  = [];
    protected /*Abstract_DBAccess*/ $_objDB;
    protected /*int*/ $_recsPerPage             = 40;

    static private $_dbConnStatic   = NULL;

    static protected function _getDefaultDBObj(): Abstract_DBAccess {
        if (Abstract_TbRec::$_dbConnStatic == NULL) {
            Abstract_TbRec::$_dbConnStatic = new MySQL_PDODriver(DB_HOSTNAME, DB_NAME);
            Abstract_TbRec::$_dbConnStatic->setCredentials(DB_USERNAME, DB_USERPASS);

            if (Abstract_TbRec::$_dbConnStatic->get_creationException() != NULL) {
                die (Abstract_TbRec::$_dbConnStatic->get_creationException()->getMessage());
            }
        }

        return Abstract_TbRec::$_dbConnStatic;
    }

    abstract protected function _init();

    /**
     */
    public function __construct(/*int*/ $id, /*Abstract_DBAccess*/ $dbConn) {
        $this->set_id($id);
        $this->set_objDB($dbConn);
    }

    /**
     * _listFldsModified
     * @return list<int>
     */
    protected function get_listFldsModified() {
        return $this->_listFldsModified;
    }

    /**
     * _objDB
     * @return Abstract_DBAccess
     */
    protected function get_objDB(){
        return $this->_objDB;
    }

    /**
     * _objDB
     * @param Abstract_DBAccess $_objDB
     * @return Abstract_TbRec
     */
    protected function set_objDB($_objDB){
        $this->_objDB = $_objDB;
    }

    /**
     * _id
     * @return int
     */
    public function get_id(){
        return $this->_id;
    }

    /**
     * _id
     * @param int $_id
     */
    public function set_id($_id){
        $this->_id = $_id;
    }

    /**
     *
     */
    abstract /*int*/ public function store(): bool;

    /**
     *
     * @param List<int> $listIds
     * @return  int
     */
    abstract protected /*int*/ function idsToDelete (/*List<int>*/ $listIds);

    /**
     *
     */
    static protected /*Array<Object>*/ abstract function listAll ($cond = NULL, $offsetPage = NULL);

    protected /*int*/ function _countRows(/*string*/ $nameTb, /*Map<colName: string, colValue: Object>*/ $conditionsMap = NULL) {
        $mtdRet = -1;

        $resQuery = Abstract_TbRec::_getDefaultDBObj()->query($nameTb, "COUNT(*)", $conditionsMap);

        if ($resQuery) {
            $mtdRet = Abstract_TbRec::_getDefaultDBObj()->getLastQueryResult();
        }

        return $mtdRet[0][0];
    }

    /**
     * _recsPerPage
     * @return int
     */
    public function get_recsPerPage(){
        return $this->_recsPerPage;
    }

    /**
     * _recsPerPage
     * @param int $_recsPerPage
     * @return Abstract_DBAccess
     */
    public function set_recsPerPage(/*int*/ $_recsPerPage){
        $this->_recsPerPage = $_recsPerPage;
    }

    public /*Array<Object>*/ function getPageOffsetFromArray (/*Array<Object>*/ $entryList, /*int*/ $pgNmb) {
        if ($pgNmb > 0) {
            $pgNmb--;
        }

        if ( count($entryList) >= ( $pgNmb * $this->get_recsPerPage() ) ) {
            $mtdRet = array_slice($entryList,
                        $pgNmb * $this->get_recsPerPage(), $this->get_recsPerPage());
        }
        else {
            $mtdRet = $entryList;
        }

        return $mtdRet;
    }

    public function getTotalPagesOffsetFromArray (/*Array<Object>*/ $entryList) {
        $mtdRet = count($entryList);

        if ($mtdRet > 0) {
            $mtdRet /= $this->get_recsPerPage();
        }

        return ceil($mtdRet);
    }

    protected /*int*/ function _getTotalPagesOffset (/*string*/ $nameTb,
        /*Map<colName: string, colValue: Object>*/ $mapVals = NULL) {
        $mtdRet = $this->_countRows($nameTb, $mapVals);

        if ($mtdRet > 0) {
            $mtdRet /= $this->get_recsPerPage();
        }

        return ceil($mtdRet);
    }

    public /*Array*/ function getErrorInfo() {
        return Abstract_TbRec::_getDefaultDBObj()->get_creationException();
    }
}
