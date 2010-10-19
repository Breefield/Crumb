<?php
/* Modified: 7/15/2009      
 * Licence:  GNU - http://www.gnu.org/copyleft/gpl.txt
 * Copyleft: Dustin Hoffman, 2009
 * Contact:  dustin.hoffman@breefield.com
 */
 
/* controller
 * Basic controller functionality
 */
class controller extends crumbMVC {
     
    /* The view class we'll have attached at our controller's hip */
    private $view; 
    
    /* Was this controller cleared */
    private $cleared;
    
    /* __construct
     * When the class fires up we want to show any layout stuff right off
     */
    public function __construct() {
        $this->view = new view();
        /* If a __layout method exists on the controller that extends this */
        if(method_exists($this, '__layout')) $this->__layout();
        if(isset($this->layout_top)) {
            /* If there are top layouts to show, show them */
            $this->view->loadLayout($this->layout_top, $this->layout_vars);
        }
    }
    
    /* __tail
     * This is the function crumbMVC calls after it calls the action method
     */
    public function __tail() {
        if(isset($this->layout_bottom) && !$this->cleared) {
            /* If there are bottom layouts to show, show them */
            $this->view->loadLayout($this->layout_bottom, $this->layout_vars);
        }
        if(!$this->cleared) $this->view->showSaved();
    }

    /* load
     * load a new model and instantiate it with the name of the model as the 
     * property name
     * @param string $model The name of the model
     */
    public function load($model) {
        global $APP_MODELS;
        try {
            /* Include the model first */
            $this->includeModel($model, $APP_MODELS, true);
            $model = $this->getPieceName($model);
            /* Then instantiate it */
            $this->$model = new $model;
        } catch(Exception $e) {
            throw $e;
        }
    }
    
    /* show 
     * Show a view
     * @param string $view The name of the view
     * @param array $vars The variables we want to acces in our view
     */
    protected function show($view, $vars = null) {
        $this->view->saveVariables($vars);
        $this->view->save($view);
    }
    
    protected function run($controller, $paramaters = null, $action = '__index') {
        $this->view->clearSaved();
        $this->cleared = true;
       
        $route_exists = true;
        if(!$this->controllerExists($controller) || 
           !$this->actionExists($controller, $action)) {
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
        $this->includeController($controller);
        $this->loadController($controller, $action, $paramaters);
    }
}
?>