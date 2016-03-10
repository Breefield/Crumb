This was written in 2010, omg! I hope you're not using it in production, if you are, help me debug my old projects running this...

## Installation
To install Crumb, simply download the package, unzip it, and move it to the location where you wish to develop your application (or checkout if you're using github). Be sure to move .htaccess when you are moving the contents of crumb.zip to their new location. Without .htaccess your mileage will vary. As in vary low.

## MVC at a glance
Model-view-controller (MVC) is an architectural pattern used in software engineering. Successful use of the pattern isolates business logic from the user interface, permitting one to be freely modified without affecting the other. The controller collects user input, the model manipulates application data, and the view presents results to the user. Typically, views and controllers come in pairs, each corresponding to a small part of the user interface; an application will likely be built with many pairs. - via Wikipedia
In summary, you're going to have three different folders: /models, /views, and /controllers, each of these will have MVC files in them. For example, the controller "lemonade" should reside in /controllers/lemonade.php.
Unlike controllers, models and views can be stored in sub-directories of their enclosing folder. The model "admins" could reside in/models/users/admins.php.

## Configuration
To get started, open up index.php and take note of all the configuration variables. The figure on the left coincides.  
This is the basic structure of a Crumb application. All the files in the application are stored somewhere, be that your HDD, or a webserver.
* BASE_URL: Where all these files are stored in relation to the root of your environment.  If you are developing on a webserver, and your root is usually /www, you would (for example) add Crumb to /www/crumb, and set BASE_URL to "crumb".
* PP_ROOT: The directory in which your application MVC folder/files reside. If you wish to have the folders /models, /views, and /controllers directly inside of your BASE_URL, then leave APP_ROOT blank.
* CRUMB_DIR: The directory in which Crumb files are stored. We advise you don't touch any of this unless you really know what you're doing - which hopefully you do, as Crumb isn't dead simple quite yet.
* DEFAULT_CONTROLLER: The controller which is called in the event no other controller is defined in the URI (we'll get to this a tad bit later).
* DEFAULT_ACTION: The method (action) to call on DEFAULT_CONTROLLER.
* CONTROLLER_404: The controller that is called if CRUMB_MODE is set to "1", and the controller defined in the URI cannot be found.
* ACTION_404: The action that is called if CRUMB_MODE is set to "1", and the action defined in the URI cannot be found.
* CRUMB_MODE: If CRUMB_MODE is set to "0" all pathing errors will be displayed in the browser, as well as sent to the php error_log.  However, if CRUMB_MODE is set to "1" these errors will solely be logged to php's error_log, and CONTROLLER_404, and ACTION_404 will be called to notify the user that the page they are looking for is missing. If CONTROLLER_404 or ACTION_404 is missing...well, you're outta luck.

## Controllers
Controllers are the core element of Crumb. Controller class files are to be stored in /controllers with the same filename as their classname. All controllers must extend the class "controller."  
Example: the controller "hello_world" would exist in /controllers/hello_wold.php, and be defined as "class hello_world {"
If you wanted to access a controller in your browser, you would navigate to BASE_URL/controller_name. For example, the controller name of this page is "documentation", the BASE_URL is "crumb", as the root is www.breefield.com.
You can add an action to your controller simply by defining a method for that controller. The method defined for this page is __index, as I have DEFAULT_ACTION set to "__index". If you wanted to call the action "help", you would navigate to "www.breefield.com/crumb/documentation/help", which in turn would call the method "help" on the class "documentation."

Controllers interact with models and views by calling them up.  Views are shown by calling "$this->show('view_name');" inside the action (method) that is being called in your controller.  And models are called by calling "$this->load('model_name);."   The contents of /controllers/authentication.php
```
class authentication extends controller { 
  public function login($name='') { 
    $this->load->('user'); 
    $vars['success'] = $this->user->login($name[0]); 
    $this->show('authentication/login', $vars);
  }
}
```

By visiting the URI /authentication/login we would be presented with the view /views/authentication/login.php. Prior to that the model/models/user.php would have been loaded and instantiated as "$this->user."  
Something else I haven't touched upon, when defending the controller "login" I added the parameter "$name." This parameter is filled by passing variables in the URI.  By visiting /authentication/login:Dustin you would populate that parameter with an array: "Array ( [0] => Dustin )", if you were to visit/authentication/login:name=Dustin, you would populate the $name parameter with the array "Array ( [name] => Dustin )"
You can pass as many parameters on the back of the URI as you like, simply delimitate them with /'s,  i.e. /authentication/login:name=Dustin/age=17

## Models
Models are dead simple. Simply create a class that extends the class "model.". This class and it's methods are in charge of retrieving data from a your database, doing data calculations, etc.  Here is an example of the model "user", stored in /models/user.php
```
class user extends model {
  public function login($email, $password) {
    if(!empty($email) && !empty($password)) return true; 
    else return false;
  }
}
```

## Views
Views are the easiest piece to write, as there is no class definition required to create a view. Simply call views in your  controllervia "$this->show('view_name', $vars);"  The variable $vars should be an array (or multidimensional array) where the keys are the variable names you wish to use in your view.  
For example, your controller calls up a view like so.
$vars['success'] = 'maybe if you ask nicely'; 
$this->show('authentication/login', $vars);
And /views/authentication/login.php's contents
```
<h1>Hello there</h1> 
<br/> 
Was it a success? 
<br/> 
<?= $success; >
```

In order to path back to the root of your application, just call <?= root(); ?> which will output the app's base url so you can link to something from the root of your application.
Layouts
There might be a few views you want to show on every page, like a header and a footer, etc. You can do this with Crumb.  In your controller just define the property $layout_top and $layout_bottom like so...
```
class authentication extends controller {
  public $layout_top = array('core/header'); 
  public $layout_bottom = array('core/footer'); 

  public function __layout() {
    $this->load('user'); 
    $this->layout_vars['logged'] = $this->user->isLogged();
  } public function login($name='') {
    ...
  }
}
```

The property $layout_vars will be passed when calling the layout views. The layout views will be able to access $logged above just like any other view.
Routing
Lastly, take a look at /config/routes.php.  To create a route, just call routing::addRoute('alias', 'route');

For example, say you have the controller "users" and the action "login", but you want your URI to simply be /login.  You'd add the route like so...
```
routing::addRoute('login', 'users/login'); 
routing::addRoute('hey/there/homedawg', 'users/login');
```

Both routes will load the controller "users" and call the action "login."

