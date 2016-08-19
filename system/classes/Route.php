<?PHP
namespace Cora;

class Route extends Framework
{
    
    protected $container;           // VARIES   - For passing data to controllers.
    protected $pathString;          // STRING   - URL excluding siteURL. mysite.com/FROM/HERE/ONWARD/
    protected $path;                // ARRAY    - Array version of pathString. URL pieces after baseURL.
    protected $controllerPath;      // STRING   - filepath to controller .php file.
    protected $controllerOffset;    // INT      - The offset within the path array of the controller.
    protected $controllerName;      // STRING
    protected $controllerNamespace; // STRING
    protected $controller;          // OBJECT
    protected $method;              // STRING
    protected $collectionIDgiven;   // BOOL     - If the URL is of form /articles/107 this is set to true.
    protected $collectionID;        // INT      - The ID if a collection ID is specified.
    
    
    public function __construct($container = false)
    {
        parent::__construct(); // Call parent constructor too so we don't lose functionality.
        
        // Register a autoloader function. Is called when an unloaded class is invoked.
        spl_autoload_register(array($this, 'autoLoader'));
        spl_autoload_register(array($this, 'coraExtensionLoader'));
        spl_autoload_register(array($this, 'libraryLoader'));
        spl_autoload_register(array($this, 'coraLoader'));
        //spl_autoload_register(array($this, 'eventLoader'));
        //spl_autoload_register(array($this, 'listenerLoader'));
        
        
        // For site specific data. This will be passed to Cora's controllers when they
        // are invoked in the routeExec() method.
        $this->container = $container;
        
        // Set PATH info.
        $this->setPath($_SERVER['REQUEST_URI']);
        
        // Debug
        $this->debug('Route: ' . $this->pathString);

    }
    
    
    /**
     *  Given a URL, sets needed PATH information.
     *
     *  NOTE: $_SERVER['QUERY_STRING'] is set by the server and may not be available
     *  depending on the server being used.
     */
    public function setPath($url) 
    {
        // Removes any GET data from the url.
        // Makes 'mysite.com/websites/build/?somevalue=test' INTO '/websites/build'
        $cleanURI = str_replace('?'.$_SERVER['QUERY_STRING'], '', $url);

        // Removes the 'mysite.com' from 'mysite.com/controller/method/id/'
        // Resulting pathString will be 'controller/method/id/'
        $this->pathString = explode($this->config['site_url'], $cleanURI, 2)[1];
        
        // If config option to make url lowercase is true
        if ($this->config['lowercase_url']) {
            $this->pathString = strtolower($this->pathString);
        }
        
        // Setup Path array
        $this->path = explode('/', $this->pathString);
    }
    
    
    /**
     *  Searches through $path to figure out what part of it is the controller.
     *  This requires searching through the filesystem.
     *
     *  If   $path = /folder1/folder2/Controller/Method/Id
     *  Then $controllerPath    = '/folder1/folder2/Controller'
     *  And  $controllerOffset  = 2
     *
     *  NOTE: This is a recursive function.
     */
     public function routeFind($basePath = '', $offset = 0)
    {
        $this->debug('');
        
        // Vars setup
        $curPath = $this->partialPathArray($offset, 1);
        $controller = '';
        $controllerFileName = '';
        
        $this->debug('Current Path: '.implode('/', $curPath));

        // if $curPath isn't empty
        if (is_array($curPath) and !empty($curPath) and $curPath[0] != '') {
            $controller_path = $curPath[0];
        }
        // Else grab the default controller.
        else {
            $controller_path = $this->config['default_controller'];
        }
            $controller = $this->getClassName($controller_path);
            $controllerFileName =   $this->config['controllersPrefix'] .
                                    $controller .
                                    $this->config['controllersPostfix'] .
                                    '.php';
        
        // Set working filepath.
        $dirpath = $this->config['pathToControllers'].$basePath.$controller_path;
        $filepath = $this->config['pathToControllers'].$basePath.$controllerFileName;

        // Debug
        $this->debug('Searching for: ' . $filepath);

        // Check if the controller .php file exists in this directory.
        if (file_exists($filepath)) {
            $this->controllerPath = $this->partialPathString(0, $offset+1);
            $this->controllerOffset = $offset;
            $this->controllerName = $controller;
            
            $this->debug('File Found: ' . $controllerFileName);
            $this->debug('Controller Path: ' . $this->controllerPath);
            
            // Create instance of controller and check if that method exists in it. Otherwise we want to keep
            // looking for other sub-folder classes that might map to the url.
            $controllerInstance = $this->getController();
            $method = $this->getMethod();
            $this->debug("getMethod() call in routeFind() returns: ".$method);
            
            if (!is_callable(array($controllerInstance, $method))) {
                // Update namespace
                $this->controllerNamespace .= $this->controllerName.'\\';
                
                //  Controller->Method combo doesn't exist, so undo previously set data.
                $this->controllerPath = null;
                $this->controllerOffset = null;
                $this->controllerName = null;
                
                // Debug
                $this->debug('Not matching method within controller. Continuing search.');
                
            } else {
                // Valid controller+method combination found, so stop searching further.
                $this->controller   = $controllerInstance;
                $this->method       = $method;
                return true;
            }
        }
        else {
            // Update namespace
            $this->controllerNamespace .= $controller.'\\';
        }

        // Else check if there's a matching directory we can look through.
        if (is_dir($dirpath)) {
            $this->debug('Directory Found: ' . $basePath . $controller);
            
            // Recursive call
            $this->routeFind($basePath . $controller_path . '/', $offset+1);
        }
        
    } // end routeFind
    
    
    /**
     *  Uses the info generated by routeFind() to then create an instance of the
     *  appropriate controller and call the desired method.
     */
    public function routeExec()
    {
        
        // If no controller was found by routeFind()...
        if (!isset($this->controllerPath)) {
            $this->debug('routeExec: No controllerPath set. Routing to 404.');
            $this->error404();
            exit;
        }

        // Grab method arguments from the URL.
        if ($this->collectionIDGiven) {
            $methodArgs = $this->partialPathArray($this->controllerOffset+1, 1);
        }
        else {
            $methodArgs = $this->partialPathArray($this->controllerOffset+2);
        }
        
        // Remove the last item from arguments if empty.
        $lastItem = count($methodArgs)-1;
        if (isset($methodArgs[$lastItem]) && $methodArgs[$lastItem] == '') {
            array_pop($methodArgs);
        }
        
        // If no arguments are set then make empty array.
        if (empty($methodArgs) || $methodArgs[0] == '') {
            $methodArgs = array();
        }
        else {
            // Sanitize arguments.
            $input = new Input($methodArgs);
            $methodArgs = $input->getData();
        }

        /** Maps an array of arguments derived from the URL into a method with a comma
         *  delimited list of parameters. Calls the method.
         *
         *  I.E. If the URL is:
         *  'www.mySite.com/MyController/FooBar/Param1/Param2/Param3'
         *
         *  And the FooBar method within MyController is defined as:
         *  public function FooBar($a, $b, $c) {}
         *
         *  $a will have the value 'Param1'
         *  $b will have the value 'Param2'  ... and so forth.
         */
        call_user_func_array(array($this->controller, $this->method), $methodArgs);
        
    } // end routeExec
    
    
    /**
     *  Checks if routeFind found a path to a controller. Can optionally check this 
     *  before calling routeExec.
     *
     *  Note: This helps with integrating Cora into legacy applications.
     *  You can check if a matching controller was found in the directory you're putting
     *  Cora controllers in, and if not, then run legacy routing instead.
     */
    public function exists()
    {
        if (!isset($this->controllerPath)) {
            return false;
        }
        return true;
    }
    
    
    protected function getController()
    {
        
        // Load generic Cora parent class
        require_once('Cora.php');    
        
        // If the config specifies an application specific class that extends Cora, load that.
        //if ($this->config['cora_extension'] != '') {
            //require_once($this->config['pathToCora'].'extensions/'.$this->config['cora_extension'].'.php');
        //}
        
        // Include the controller code.
        $cPath =    $this->config['pathToControllers'] .
                    $this->getPath($this->controllerPath) .
                    $this->config['controllersPrefix'] .
                    $this->controllerName .
                    $this->config['controllersPostfix'] .
                    '.php';
        
        if (file_exists($cPath)) {
            include_once($cPath);
        }
        // Return an instance of the controller.
        $class = $this->controllerNamespace.$this->getClassName($this->controllerName);
        
        return new $class($this->container);
    }
    
    
    protected function getMethod()
    {
        
        $method = '';
        $this->collectionIDGiven = false;
        $this->collectionID = false;
        
        // Figure out method to be called, or use default.
        if (isset($this->path[$this->controllerOffset+1])) {
            $method = $this->path[$this->controllerOffset+1];
            
            // If no method is specified or if the method spot is numeric (indicating 
            // that an ID is being passed to a collection for RESTful routing)
            // Route to default method.
            if ($method == '') {
                $method = $this->config['default_method'];
            }
            else if (is_numeric($method)) {
                $this->collectionIDGiven = true;
                $this->collectionID = $method;
                $method = $this->config['default_method'];          
            }
        }
        else {
            $method = $this->config['default_method'];
        }
        
        // RESTful routing:
        // Modify method routed to if request is not of type GET.
        if ($this->config['enable_RESTful']) {

            $httpMethod = $_SERVER['REQUEST_METHOD'];
            
            if ($this->collectionIDGiven) {
                switch ($httpMethod) {
                    case 'GET':
                        $method = 'itemGET';
                        break;
                    case 'PUT':
                        $method = 'itemPUT';
                        break;
                    case 'POST':
                        $method = 'itemPOST';
                        break;
                    case 'DELETE':
                        $method = 'itemDELETE';
                        break;
                }
            }
            else {
                switch ($httpMethod) {
                    case 'PUT':
                        $method = $method.'PUT';
                        break;
                    case 'POST':
                        $method = $method.'POST';
                        break;
                    case 'DELETE':
                        $method = $method.'DELETE';
                        break;
                }
            }
        }
        
        return $method;
        
    } // END getMethod
    

    protected function getClassName($slug)
    {
        return str_replace(' ', '', ucwords(preg_replace('/[-_]/', ' ', $slug)));
    }
    
    /**
     *  The following two methods work off the URL path stored in $this->path.
     *  They are used to return part of that path in either Array or String form
     *  when asked to by the recursive calls of routeFind().
     */
    protected function partialPathString($offset, $length = null, $dataArray = false)
    {
        if ($dataArray == false) {
            $dataArray = $this->path;
        }
        $partialPathArray = array_slice($dataArray, $offset, $length);
        return implode('/', $partialPathArray);
    }
       
    protected function partialPathArray($offset, $length = null)
    {
        return array_slice($this->path, $offset, $length);
    }
    
    
    protected function error404()
    {
        
        require_once('Cora.php');    
        require('Error.php');
        $error = new \Cora\Error($this->container);
        $error->handle('404');
        
    }
    
    protected function autoLoader($className)
    {
        $fullPath = $this->config['pathToModels'] .
                    $this->getPathBackslash($className) .
                    $this->config['modelsPrefix'] .
                    $this->getNameBackslash($className) .
                    $this->config['modelsPostfix'] .
                    '.php';
        //echo 'Trying to load ', $className, '<br> &nbsp;&nbsp;&nbsp; from file ', $fullPath, "<br> &nbsp;&nbsp;&nbsp; via ", __METHOD__, "<br>";
        
        // Grab root of namespace.
        $rootName = explode('\\', $className)[0];
        
        // Depending on the root of the namespace, possibly run one of several autoloaders.
        if ($rootName == 'Event') {
            $this->eventLoader($className);
        }
        else if ($rootName == 'Listener') {
            $this->listenerLoader($className);
        }
//        else if ($rootName == 'Library') {
//            $namespaceExcludingLibrary = $this->partialPathString(1, null, explode('\\', $className));
//            $this->libraryLoader($namespaceExcludingLibrary);
//        }
        else if (file_exists($fullPath)) {
            include($fullPath);
        }
    }
    
    protected function eventLoader($className)
    {
        $fullPath = $this->config['pathToEvents'] .
                    $this->getPathBackslash($className, true) .
                    $this->config['eventsPrefix'] .
                    $this->getNameBackslash($className) .
                    $this->config['eventsPostfix'] .
                    '.php';
        //echo 'Trying to load ', $className, '<br> &nbsp;&nbsp;&nbsp; from file ', $fullPath, "<br> &nbsp;&nbsp;&nbsp; via ", __METHOD__, "<br>";
        if (file_exists($fullPath)) {
            include($fullPath);
        }
    }
    
    protected function listenerLoader($className)
    {
        $fullPath = $this->config['pathToListeners'] .
                    $this->getPathBackslash($className, true) .
                    $this->config['listenerPrefix'] .
                    $this->getNameBackslash($className) .
                    $this->config['listenerPostfix'] .
                    '.php';
        //echo 'Trying to load ', $className, '<br> &nbsp;&nbsp;&nbsp; from file ', $fullPath, "<br> &nbsp;&nbsp;&nbsp; via ", __METHOD__, "<br>";
        if (file_exists($fullPath)) {
            include($fullPath);
        }
    }
    
    protected function coraLoader($className)
    {
        $fullPath = dirname(__FILE__) . '/' .
                    //$this->getPathBackslash($className) .
                    $this->getNameBackslash($className) .
                    '.php';
        //echo 'Trying to load ', $className, '<br> &nbsp;&nbsp;&nbsp; from file ', $fullPath, "<br> &nbsp;&nbsp;&nbsp; via ", __METHOD__, "<br>";
        if (file_exists($fullPath)) {
            include($fullPath);
        }
    }
    
    protected function coraExtensionLoader($className)
    {
        $fullPath = $this->config['pathToCora'] .
                    'extensions/' .
                    $this->getPathBackslash($className) .
                    $this->getNameBackslash($className) .
                    '.php';
        //echo 'Trying to load ', $className, '<br> &nbsp;&nbsp;&nbsp; from file ', $fullPath, "<br> &nbsp;&nbsp;&nbsp; via ", __METHOD__, "<br>";
        if (file_exists($fullPath)) {
            include($fullPath);
        }
    }
    
    protected function libraryLoader($className)
    {
        //$name = $this->getName($className);
        //$path = $this->getPath($className);
        $fullPath = $this->config['pathToLibraries'] .
                    $this->getPathBackslash($className) .
                    $this->config['librariesPrefix'] .
                    $this->getNameBackslash($className) .
                    $this->config['librariesPostfix'] .
                    '.php';
        
        //echo 'Trying to load ', $className, '<br> &nbsp;&nbsp;&nbsp; from file ', $fullPath, "<br> &nbsp;&nbsp;&nbsp; via ", __METHOD__, "<br>";
        // If the file exists in the Libraries directory, load it.
        if (file_exists($fullPath)) {
            include_once($fullPath);
        }
    }
} // end Class
