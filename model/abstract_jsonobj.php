<?php

/**
 *
 * @author Administrator
 *        
 */
abstract class Abstract_JSONObj {
    protected /*object*/ $_objJSON;
    protected /*bool*/ $_isJSON;

    /**
     * 
     * @param string $fromString
     */
    public function __construct(/*string*/ $fromString) {
        $this->set_objJSON(json_decode($fromString));

        $this->set_isJSON($this->_objJSON != NULL);
    }

    /**
     */
    function __destruct() {
        // TODO - Insert your code here
    }

    /**
     * 
     * @param string $fromString
     * @return boolean
     */
    public /*boolean*/ function isValidJSON() : bool {
        return $this->_isJSON;
    }

    /**
     * _objJSON
     * @return object
     */
    public function get_objJSON(): object {
        return $this->_objJSON;
    }

    /**
     * _objJSON
     * @param object $_objJSON
     * @return Abstract_JSONObj
     */
    public function set_objJSON($_objJSON) {
        $this->_objJSON = $_objJSON;
        return $this;
    }

    /**
     * _isJSON
     * @return bool
     */
    public function get_isJSON(): bool {
        return $this->_isJSON;
    }

    /**
     * _isJSON
     * @param bool $_isJSON
     * @return Abstract_JSONObj
     */
    public function set_isJSON($_isJSON) {
        $this->_isJSON = $_isJSON;
        return $this;
    }

    /**
     * 
     */
    public function __toString(): string {
        return json_encode($this->_objJSON);
    }
}
