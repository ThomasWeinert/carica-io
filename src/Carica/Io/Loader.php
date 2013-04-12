<?php
/**
* Map the namespace to local files and include the class source files
*
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright 2012 Thomas Weinert <thomas@weinert.info>
*/

namespace Carica\Io {

  /**
   * Map the namespace to local files and include the class source files
   */
  class Loader {

    /**
     * The actual autloader function that will be registered.
     *
     * @param string $class
     */
    public static function load($class) {
      if ($file = self::getFilename($class)) {
        $file = __DIR__.$file;
        if (file_exists($file) && is_readable($file)) {
          include_once($file);
          return TRUE;
        }
      }
      return FALSE;
    }

    /**
     * Get the file name for the given class.
     *
     *  It removes the current namespace of this class from
     *  the given class name and transform the backslashes
     *  to directory separates.
     *
     * @param string $class
     * @return string|NULL
     */
    public static function getFilename($class) {
      if (0 === strpos($class, __NAMESPACE__)) {
        return str_replace(
          '\\', DIRECTORY_SEPARATOR, substr($class, strlen(__NAMESPACE__))
        ).'.php';
      }
      return FALSE;
    }

    /**
     * Register the autoloader function using SPL.
     *
     * @codeCoverageIgnore
     */
    public static function register() {
      spl_autoload_register(array(__CLASS__, 'load'));
    }
  }
}
