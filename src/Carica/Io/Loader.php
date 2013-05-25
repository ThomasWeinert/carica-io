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

    private static $_mappings = array();

    /**
     * The actual autloader function that will be registered.
     *
     * @param string $class
     */
    public static function load($class) {
      if ($file = self::getFilename($class)) {
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
      foreach (self::$_mappings as $namespace => $directory) {
        if (0 === strpos($class, $namespace)) {
          return $directory.DIRECTORY_SEPARATOR.str_replace(
            '\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace))
          ).'.php';
        }
      }
      if (0 === strpos($class, __NAMESPACE__.'\\')) {
        return __DIR__.str_replace(
          '\\', DIRECTORY_SEPARATOR, substr($class, strlen(__NAMESPACE__))
        ).'.php';
      }
      return FALSE;
    }

    public static function map($mappings) {
      foreach ($mappings as $namespace => $directory) {
        if (substr($namespace, -1) != '\\') {
          $namespace .= '\\';
        }
        if (empty($directory) && isset(self::$_mappings[$namespace])) {
          unset(self::$_mappings[$namespace]);
        } else {
          self::$_mappings[$namespace] = $directory;
        }
      }
      uksort(
        self::$_mappings,
        function($ns1, $ns2) {
          $length1 = strlen($ns1);
          $length2 = strlen($ns2);
          if ($length1 > $length2) {
            return -1;
          } elseif ($length1 < $length2) {
            return 1;
          } else {
            return 0;
          }
        }
      );
    }

    public function reset() {
      self::$_mappings = array();
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
