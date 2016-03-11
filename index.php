<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
/* Leave this empty if you'll be developing from the root of your website
 * or webapp.
 */
define('BASE_URL', 'bm-timelapse');

/* This is where *your* folders and files sit, leave it blank if they are
 * directly adjacent to CRUMB_DIR 
 */
define('APP_ROOT', 'app');

/* This is where crumb sits, if you decide to rename the crumb folder to
 * something else, change this as well 
 */
define('CRUMB_DIR', 'crumb');

/* This is the controller we'll call if no other controller is defined */
define('DEFAULT_CONTROLLER', 'index');

/* This is the first action/method called on any controller included in the
 * framework, you can se it to be whatever you like, just don't set it to
 * __construct or __tail
 */
define('DEFAULT_ACTION', '__index');

/* Which controller should be called if the called controller is missing
 * ONLY APPLICABLE: if CRUMB_MODE is set to '1'
 */
define('CONTROLLER_404', 'index');

/* On CONTROLLER_404 controller, which action should be called
 * ONLY APPLICABLE: if CRUMB_MODE is set to '1'
 */
define('ACTION_404', 'is404');

/* Error display mode */
define('CRUMB_MODE', 1);
error_reporting(E_ALL);

/* Include Path */
set_include_path(get_include_path().PATH_SEPARATOR.'./'.PATH_SEPARATOR.'./'.APP_ROOT);

/* This brings everything together */
require_once(CRUMB_DIR.'/unload.php');
?>