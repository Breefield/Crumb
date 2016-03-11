<?php
/* Modified: 7/15/2009      
 * Licence:  GNU - http://www.gnu.org/copyleft/gpl.txt
 * Copyleft: Dustin Hoffman, 2009
 * Contact:  dustin.hoffman@breefield.com
 */
 
/* view
 * Class to extend all our views functionality
 */
class view extends crumbMVC {
    
    /* The variables to be declared as global when rendering out view */
    private $globals;
    /* Don't you dare. name your variables with these names */
    private $crumb_var_names = array('APP_CONTROLLERS',
                                          'APP_MODELS',
                                          'APP_VIEWS');
    
    /* load
     * load up a view
     * @param string @view The local path of the view
     */
    public function load($view) {
        global $APP_VIEWS;
        if(!is_null($this->globals)) {
            /* Set the global variables for our view to use
             * remember, we have to set them as global every method call
             * we plan to use them
             */
            foreach($this->globals as $global) global $$global;
        }
        $view = trim($view, DS);
        if(in_array($view, array_keys($APP_VIEWS))) {
            /* Show the view */
            $file = $APP_VIEWS[$view];
            include_once($file);
        } else {
            throw new Exception(sprintf('View file <strong>%s.php</strong> '.
                                        'is missing.', $view));
        }
    }

    /* loadLayout
     * If there are layout files, load them into the output
     * @param string $layouts Local view name
     * @param array $vars The global vars to set
     */
    public function loadLayout($layout, $vars) {
        if(!empty($layout)) {
            foreach($layout as $file) {
                $this->setVariables($vars);
                $this->load($file);
            }
        }
    }
    
    /* setVariables
     * Set an array of variables as global variables
     * @param array $vars The array of variables
     */
    public function setVariables($vars) {
        if(!empty($vars)) {
            foreach($vars as $name => $value) {
                if(!in_array($name, $this->crumb_var_names)) {
                    /* For every variable we're gonna set, if it's not trying
                     * to overwrite a protected name, set it as global
                     */
                    $this->globals[] = $name;
                    global $$name;
                    $$name = $value;
                } else {
                    throw new Exception(
                        sprintf('Variable name <strong>%s</strong> is '.
                                'protected by Crumb.', $name));
                }   
            }
        }
    }
}
?>