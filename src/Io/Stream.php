<?php

namespace Carica\Io {

  interface Stream {

    function resource();
    
    function open();
    
    function close();
    
    function read($bytes = 1024);
    
    function write($data);

  }

  function encodeBinaryFromArray(array $data) {
    array_unshift($data, 'C*');
    return call_user_func_array('pack', $data);
  }
  
  function decodeBinaryToArray($data) {
    
  }

}