<?php

class LSAuthResult
{
    protected $_code;
    protected $_message;
    
    public function __construct($code = 0, $message = '') {
        $this->setError($code, $message);
    }
    
    public function isValid()
    {
        if ($this->_code === 0) {
            return true;
        }
        
        return false;
    }
    
    public function getCode()
    {
        return $this->_code;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    public function setError($code, $message = null) {
        $this->_code = $code;
        $this->_message = $message;
    }
}