<?php

namespace Carica\Io\Stream\Serial {

  class Factory {

    private $_useDio = NULL;
     
    public function useDio($use = NULL) {
      if (isset($use)) {
        if ($use && extension_loaded('dio')) {
          $this->_useDio = TRUE;
        } elseif (!$use) {
          $this->_useDio = FALSE;
        } else {
          throw new LogicException('Extension "dio" not available.');
        }
      } elseif (NULL == $this->_useDio) {
        $this->_useDio = extension_loaded('dio');
      }
      return $this->_useDio;
    }
    
    public function get($device) {
      if ($this->useDio()) {
        return new Dio($device);
      } else {
        return new Stream\Serial($device);
      }
    }
    
  }
}