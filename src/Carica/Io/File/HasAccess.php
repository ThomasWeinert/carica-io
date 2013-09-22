<?php

namespace Carica\Io\File {

  interface HasAccess {

    /**
     * @param Access $fileAccess
     * @return mixed
     */
    function fileAccess(Access $fileAccess = NULL);
  }
}