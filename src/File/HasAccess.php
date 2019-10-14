<?php
declare(strict_types=1);

namespace Carica\Io\File {

  interface HasAccess {

    /**
     * @param Access $fileAccess
     * @return mixed
     */
    public function fileAccess(Access $fileAccess = NULL): Access;
  }
}
