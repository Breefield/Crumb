<?php
/* Modified: 7/15/2009      
 * Licence:  GNU - http://www.gnu.org/copyleft/gpl.txt
 * Copyleft: Dustin Hoffman, 2009
 * Contact:  dustin.hoffman@breefield.com
 */
 
 session_start();

/* Basic package for including controller, model, and view classes */
require_once('MVC/class.crumbMVC.php');
require_once('routing/class.routing.php');

/* This is the root of our setup (advice: don't change this) */
define('ROOT', '/');
/* For pathing */
define('DS', '/');
/* When we need to include files, etc, we need the entire document root */
define('FS_ROOT', $_SERVER['DOCUMENT_ROOT'].ROOT);
/* For pathing the URI */
define('BUILD_URI', ROOT.trim(BASE_URL, DS));
/* For includes and requires */
define('BUILD_ROOT', FS_ROOT.trim(BASE_URL, DS));
/* The full path we'll be using in the URI */
define('APP_BUILD_ROOT', FS_ROOT.trim(BASE_URL.DS, DS).DS.trim(APP_ROOT, DS));
/* If you need to call something from the root of your appliction space */
function root() { return rtrim(ROOT.trim(BASE_URL, DS), DS).DS; }

/* Create a routing object and pass it our URI so we can get the controller
 * action, and paramaters passed in our URI
 */
$routing = new routing(APP_BUILD_ROOT);
$route = $routing->breakURI($_SERVER['REQUEST_URI'], BUILD_URI);
$controller = $routing->getControllerName();
$action = $routing->getActionName();
$paramaters = $routing->getParamaters();

/* Instantiate our crumbMVC controller */
$crumb_mvc = new crumbMVC(true, true, true);
try {  
    /* First check to see if our URI points to a support resource (not a php
     * file)
     */
    $crumb_mvc->loadSupport($route);
    
    /* Check to see if the route passed in the URI actually pertains to files
     * that exist in our app
     */
    $route_exists = true;
    if(!$crumb_mvc->controllerExists($controller) || 
       !$crumb_mvc->actionExists($controller, $action)) {
        $route_exists = false;
    }
    /* If the route doesn't exist, and we're in production mode show the 404
     * controller
     */
    if(!$route_exists && CRUMB_MODE) {
        $controller = CONTROLLER_404;
        $action = ACTION_404;
    }
    /* Include and load our controller / action with the paramaters in tow */
    $crumb_mvc->includeController($controller);
    $crumb_mvc->loadController($controller, $action, $paramaters);
} catch(Exception $e) {
    
    /* This is the error output section of CRUMB, feel free to change
     * FUTURE ADDITION: Log to file in CRUMB directory
     */
    $error = $e->getMessage();
    error_log(strip_tags($error));
    /* If in dev mode, log the error to the browser too */
    if(!CRUMB_MODE) {
        ?>
        <div class="dev-error">
            <?= $error; ?>
        </div>
        <?php
    }
}
?>