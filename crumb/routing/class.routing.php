<?php
/* Modified: 7/15/2009      
 * Licence:  GNU - http://www.gnu.org/copyleft/gpl.txt
 * Copyleft: Dustin Hoffman, 2009
 * Contact:  dustin.hoffman@breefield.com
 */
 
/* routing
 * This class is responsible for breaking down a URI in Crumb and passing back
 * controller & action names, as well as paramaters attached to te URI
 *
 * Methods:
 *      __construct
 *      breakURI
 *      processParamaters
 *      processParamater
 *      getParamaters
 *      getControllerName
 *      getActionName
 *      addRoute
 */
class routing {

    /* The URI exploded by DS */
    private $path;
    /* Where is the routs file located your our app */
    private $routes_file;
    /* The paramaters end of the URI */
    private $paramaters;
    /* Action names we suggest you don't use */
    private $crumb_protected = array('__construct' => '',
                                     '__tail' => '');
    
    /* __construct
     * Used this to find the routes file in /config 
     */
    public function __construct() {

        $this->routes_file = APP_BUILD_ROOT.DS.'config'.DS.'routes.php';
        if(file_exists($this->routes_file)) {
            require_once($this->routes_file);
        } else {
            throw new Exception(sprintf('<strong>%s</strong>is missing.', 
                                $this->routes_file));
        }
    }
    
    /* breakURI
     * Used to get the route and paramaters out of our URI
     * @param string $URI The current URI called
     * @param string $base The URI relative base we need to break off the URI
     *      before using it
     * @return array
     */
    public function breakURI($URI, $base) {
        /* Although messy, we store the routs as a global variable so it's
         * accessable by un-instantiated class calls in routes.php
         */
        global $routes;
        
        /* If the base is just a DS, don't use it */
        $base = ($base != DS) ? $base : '';
        /* Remove the base from $URI */
        $break_URI = $this->str_ireplace_once($base, '', $URI);
        /* Get the route and the paramaters seperate from one another */
        $routeAndParams = explode(':', $break_URI);
        if(is_array($routeAndParams) && count($routeAndParams) > 1) {
            /* If there were paramaters, process them */
            $this->processParamaters($routeAndParams[1]);
        }
        /* Get that route back from $routeAndParams */
        $break_URI = $routeAndParams[0];
        if(is_array($routes)) {
            foreach($routes as $alias => $route) {
                /* For all the routes in routes.php find the aliases
                 * and replace them with the actual route
                 */
                $break_URI = $this->str_ireplace_once($alias, 
                                                      $route, 
                                                      $break_URI);
            }
        }
        foreach($this->crumb_protected as $alias => $route) {
            /* If there is a protected keyword in the URI, strip it */
            $break_URI = str_ireplace($alias, $route, $break_URI);
        }
        
        /* Break apart the URI at the DS's */
        $break_URI = explode(DS, trim($break_URI, DS));
        $this->path = array();
        foreach($break_URI as $bit) 
            /* Add each of the sections from the URI to the property $path */
            if(!empty($bit)) $this->path[] = urldecode($bit);
        return $this->path;
    }

    private function str_ireplace_once($needle, $replace, $haystack) { 
        $pos = stripos($haystack, $needle); 
        if ($pos === false) return $haystack; 
        return substr_replace($haystack, $replace, $pos, strlen($needle)); 
    } 

    /* processParamaters
     * When the paramaters are pulled off the back of the URI they need to be
     * cleaned and stored
     * @param string $paramaters The paramaters pulled off the URI
     */
    private function processParamaters($paramaters) {
        /* Split the paramaters at every DS */
        $paramaters = explode(DS, $paramaters);        
        foreach($paramaters as $key => $paramater) {
            /* Process each paramater individually */
            $this->processParamater(trim($paramater));
        }
    }
    
    /* processParamater
     * Process a single paramater from the URI
     * @param string $paramater A paramater from the URI
     */
    private function processParamater($paramater) {
        /* Ignore empy paramaters, i.e. /hey//there/kid */ 
        if(!empty($paramater)) {
            /* If the paramater is a key=value pair, break it */
            $paramater = explode('=', $paramater);
            if(count($paramater) > 1 
               && !empty($paramater[0])) {
                /* In the event that it is a key/value pair, make sure the
                 * key is not empty */
                $this->paramaters[$paramater[0]] = $paramater[1];
            } else {
                /* Otherwise just store the paramater in the $paramaters prop */
                $this->paramaters[] = $paramater[0];
            }
        }
    }
    
    /* getParamaters
     * @return array
     */
    public function getParamaters() {
        return $this->paramaters;
    }

    /* getControllerName
     * @return string
     */
    public function getControllerName() {
        return (!empty($this->path[0])) ? $this->path[0] : DEFAULT_CONTROLLER; 
    }
    
    /* getActionName
     * @return
     */
    public function getActionName() {
        return (!empty($this->path[1])) ? $this->path[1] : DEFAULT_ACTION; 
    }
    
    /* addRoute
     * Add a route to the globak $routes variable 
     * @param string $alias The string to find in the URI and replace
     * @param string $route The actual route to replace with
     */
    public function addRoute($alias, $route) {
        global $routes;
        /* Store this for later */
        $routes[trim($alias, DS)] = trim($route, DS);
    }

}

?>