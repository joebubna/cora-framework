<?php
namespace Cora;

class Framework {

    protected $config;
    // protected $load;

    function __construct() {

        // Load and set cora config.
        require(dirname(__FILE__).'/../config/config.php');

        // Load custom app config
        include($config['basedir'].'cora/config/config.php');

        if (file_exists($config['basedir'].'cora/config/local.config.php')) {
            include($config['basedir'].'cora/config/local.config.php');
        }

        // Store config settings as data member.
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function debug($message = '', $newLine = true) {
        if ($this->config['debug'] == true) {

            // Start hiding HTML comment
            if ($this->config['debugHide'] == true) {
                echo '<!-- ';
            }

            // Actual debug message.
            echo $message;

            // If hiding output, do /n to form newline, otherwise use <br>
            if ($newLine) {
                if ($this->config['debugHide']) {
                    echo "\n";
                }
                else {
                    echo "<br>";
                }
            }

            // End if hiding HTML comment.
            if ($this->config['debugHide'] == true) {
                echo '-->';
            }
        }
    }

    protected function debugArray($arr) {
        if ($this->config['debug'] == true) {
            echo '<pre>';
            print_r($arr);
            echo '</pre>';
        }
    }


    /**
     *  Get the 'fileName' out of 'folder/folder/fileName.php
     */
    protected function getName($pathname) {
        $arr = explode('/', $pathname);
        return $arr[count($arr)-1];
    }

    /**
     *  Get the 'fileName' out of 'folder\folder\fileName.php
     */
    protected function getNameBackslash($pathname) {
        $arr = explode('\\', $pathname);
        return $arr[count($arr)-1];
    }

    /**
     *  Get the 'models' out of 'models/folder/fileName.php
     */
    public static function getRoot($pathname) {
        // Remove leading slash if necessary.
        if($pathname[0] == '/') {
            $pathname = substr($pathname, 1);
        }

        // Create array from path
        $arr = explode('/', $pathname);
        
        // If a pathname was given (as opposed to just a filename like "myFile.php")
        // Return the first part of it. Otherwise just return nothing for root.
        if (count($arr) > 1) {
            return $arr[0];
        }
        return '';
    }

    /**
     *  Get the 'models' out of 'models\folder\fileName.php
     */
    public static function getRootBackslash($pathname) {
        
        // Remove leading slash if necessary.
        if($pathname[0] == '\\') {
            $pathname = substr($pathname, 1);
        }

        // Create array from path
        $arr = explode('\\', $pathname);
        
        // If a pathname was given (as opposed to just a filename like "myFile.php")
        // Return the first part of it. Otherwise just return nothing for root.
        if (count($arr) > 1) {
            return $arr[0];
        }
        return '';
    }

    /**
     *  Get the 'folder/folder' out of 'folder/folder/fileName.php
     */
    protected function getPath($pathname) {
        $arr = explode('/', $pathname);
        $partialPathArray = array_slice($arr, 0, count($arr)-1);
        $path = implode('/', $partialPathArray);

        // If path isn't blank, then add ending slash to it.
        if ($path != '')
            $path = $path . '/';

        return $path;
    }

    /**
     *  Get the 'folder/folder' out of 'folder\folder\fileName.php
     */
    protected function getPathBackslash($pathname, $removeBaseName = false) {
        $arr = explode('\\', $pathname);
        if ($removeBaseName) {
            $partialPathArray = array_slice($arr, 1, count($arr)-2);
        }
        else {
            $partialPathArray = array_slice($arr, 0, count($arr)-1);
        }
        array_walk($partialPathArray, array($this, '_lcfirst'));
        $path = implode('/', $partialPathArray);

        // If path isn't blank, then add ending slash to it.
        if ($path != '')
            $path = $path . '/';

        return $path;
    }

    private function _lcfirst(&$str, $i)
    {
        $str = lcfirst($str);
    }

}
