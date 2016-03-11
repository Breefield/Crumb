<?php
/* Modified: 7/15/2009      
 * Licence:  GNU - http://www.gnu.org/copyleft/gpl.txt
 * Copyleft: Dustin Hoffman, 2009
 * Contact:  dustin.hoffman@breefield.com
 */
 
/* The classes referenced in the class mvc */
include('class.mimeType.php');
include('class.controller.php');
include('class.model.php');
include('class.view.php');

/* crumbMVC 
 * Our Model-View-Controller class that manages finding, loading, and 
 * instantiating pieces of the framework 
 */
class crumbMVC {

    /* When a controller is loaded into Crumb is is stored in here for 
     * later use
     */
    private $loaded_controller;
    
    /* __construct
     * The method which instantiates crumbMVC
     * @param bool $scan_controllers Should new controllers be found
     * @param bool $scan_models Should new models be found
     * @param bool $scan_view Shoud new views / support files be found
     */
    public function __construct($scan_controllers=false,
                                $scan_models=false,
                                $scan_views=false) {
        global $APP_CONTROLLERS, $APP_MODELS, $APP_VIEWS;
        /* Find controllers, models, and views if applicable */
        if($scan_controllers) {
            $this->scrubTree(APP_BUILD_ROOT.DS.'controllers', 
                             $APP_CONTROLLERS);
        }
        if($scan_models) {
            $this->scrubTree(APP_BUILD_ROOT.DS.'models', $APP_MODELS, '/');
        }
        if($scan_views) {
            $this->scrubTree(APP_BUILD_ROOT.DS.'views', $APP_VIEWS, '/');
        }
    }
    
    /* loadController
     * Load up a new controller and call it's action method, as well as it's
     * __construct, and eventually __tail
     * @param string $controller The controller we'll be loading
     * @param string $action The action to call
     * @param array $paramaters The URI paramaters to pass to the controller
     */
    public function loadController($controller=DEFAULT_CONTROLLER, 
                                   $action=DEFAULT_ACTION,
                                   $paramaters=null) {
        /* Check to see if the class exists */
        if(class_exists($controller)) {
            /* If it does, check to see that it's action exists */
            if(method_exists($controller, $action)) {
                /* Instantiate the controller and call it's action */
                $this->loaded_controller = new $controller();
                $instanceof = ($this->loaded_controller instanceof controller);
                if($instanceof) {
                    /* If the controller extends the class "controller" */
                    if($action != '__construct') {
                        /* One last check to see that the action isn't
                         * __construct, we wouldn't want to call it twice */
                        $this->loaded_controller->$action($paramaters);
                        /* Close out the controller with any layout ends */
                        $this->loaded_controller->__tail();
                    }
                } else {
                    throw new Exception(
                        sprintf('Controller <strong>%s</strong> does not extend '. 
                                'the controller.', $controller));
                }
            } else {
                throw new Exception(
                sprintf('The action <strong>%s</strong> is not in '. 
                        'the controller <strong>%s</strong>.', 
                        $action, $controller));
            }
        } else {
            throw new Exception(sprintf('Failed to instantiate the controller '.
                                        '<strong>%s</strong>.', $controller));
        }
    }
    
    /* includePiece
     * Find out if a piece exists in the framework, and if so,
     * require it once
     * @param string $piece The controller, model, or view we're looking for
     * @param array $PIECES The array storing all these piece's names
     * 
     */
    private function includePiece($piece, $PIECES, $type='controller') {
        /* The filename based on the piece name */ 
        $file = trim($piece, DS);
        /* If the file name is a key in the $PIECES array */

        if(in_array($file, array_keys($PIECES))) {
            /* Then the actual file path we're looking for becomes apparent,
             * require it
             */
            $file = $PIECES[$file];
            require_once($file);
            if(!class_exists($piece)) {
                throw new Exception(
                    sprintf('The controller <strong>%s</strong> is not in the '.
                            'file <strong>%s</strong>.', $piece, $piece.'.php'));
                            
            }
        } else {
            throw new Exception(sprintf('%s file <strong>%s.php</strong> '.
                                        'is missing.', ucwords($type), $piece));
        }
    }
    
    /* pieceExists
     * Check to see if a piece exists in it's associative array
     * @param string $piece The controller, model, or view we're looking for
     * @param array $PIECES The array storing all these piece's names
     * @return bool
     */
    public function pieceExists($piece, $PIECES) {
        $file = trim($piece, DS);
        if(!in_array($file, array_keys($PIECES))) {
            return false;
        }
        return true;
    }
    
    /* controllerExists
     * Check to see if a controller exists
     * @param string $controller The controller name
     * @return bool
     */
    public function controllerExists($controller) {
        global $APP_CONTROLLERS;
        return $this->pieceExists($controller, $APP_CONTROLLERS);
    }
    
    /* actionExists
     * Check to see if a controller exists
     * @param string $controller The controller name
     * @param string $action The action name
     * @return bool
     */
    public function actionExists($controller, $action) {
        global $APP_CONTROLLERS;
        try {
            $this->includePiece($controller, $APP_CONTROLLERS, 'controller');
        } catch(Exception $e) {
            return false;
        }
        if(!method_exists($controller, $action)) return false;
        return true;
    }
    
    /* getPieceName
     * Get the name of a piece by splitting a string at the DS
     * @param string $piece The name of the piece
     * @return string
     */
    public function getPieceName($piece) {        
        $piece_name = explode('/', $piece);
        return (is_array($piece_name)) ? end($piece_name) : $piece;
    }
    
    /* includeController
     * Include a controller
     * @param string $controller The name of the controller to include
     */
    public function includeController($controller) {
        global $APP_CONTROLLERS;
        try {
            $this->includePiece($controller, $APP_CONTROLLERS, 'controller');
        } catch(Exception $e) {
            throw $e;
        }
    
    }
    
    /* includeModel
     * Include a controller
     * @param string $model The name of the model to include
     */
    public function includeModel($model) {
        global $APP_MODELS;
        try {
            $this->includePiece($model, $APP_MODELS, 'model');
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    
    }
    
    /* loadSupport
     * Support files are those which are in the /view directory but that don't
     * end in ".php" - this method loads them.
     * @param array $file The filename of the support file
     */
    public function loadSupport($file) {
        global $APP_VIEWS;
        /* $file will be an array because it should have been processed by the
         * routing class
         */

        if(is_array($file)) {
            /* Build the actual file name from the array */
            $file_name = '';
            foreach($file as $bit) $file_name .= $bit.DS;
            $file_name = trim($file_name, DS);
            $file_type = explode('.', $file_name);
            /* Get the filetype from the filename */
            if(is_array($file_type) && count($file_type) > 1) {
                $file_type = end($file_type);
            } else {
                $file_type = '';
            }
            /* If the filename is in $APP_VIEWS then it's good, include it */
            if(in_array($file_name, array_keys($APP_VIEWS)) 
               && !empty($file_type)) {
                $file = $APP_VIEWS[$file_name];
                /* Figure out the mime-type and set our header */
                $mime_type = mimeType::getMimeType($file);
                header('Content-type: '.$mime_type);
                /* read the file off, this prevents against the browser
                 * prompting a download
                 */
                readfile($file);
                exit();
            }
        }
    }
    
    /* scrubTree
     * Look through a file tree for files to store in an array
     * @param string $dir A directory path
     * @param array-pointer &$store The array to store the filenames in
     * @param string $path If we want to tack the path onto the key in 
     *      the array we're creating
     */
    private function scrubTree($dir, &$store, $path='') {
        /* If we're given a directory mind you */
        if(is_dir($dir)) $files = scandir($dir);
        foreach($files as $file) {
            /* For every file, check to see if it's actually a directory */
            $dir_name = $file;
            $dir_path = $dir.DS.trim($dir_name, DS);
            // If it's a directory
            if(is_dir($dir_path) && $dir_name[0] != '.') {
                /* If it is, and it's not hidden */
                if(!empty($path)) {
                    /* Recursive call, adding to the $path */
                    $this->scrubTree($dir_path, $store, 
                                     trim($path.DS.$dir_name, DS));
                } else {
                    /* Recursive call, ignoring the $path */
                    $this->scrubTree($dir_path, $store, $sdir);
                }
            }
            /* If it's a file, not a directory */
            if(is_file($dir.DS.$file) && $file[0] != '.') {
                /* Get the file extension */
                $file_type = explode('.', $file);
                $extension = $file_type[count($file_type) - 1];
                /* If we're storing the path as part of the key */
                if(!empty($path)) $key = $path.DS.$file;
                else $key = $file;
                /* Store the filename and file location away for later */
                $key = str_ireplace('.php', '', $key);
                $store[trim($key, DS)] = $dir.DS.$file;
            }
        }
    }
}

?>