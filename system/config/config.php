<?php
/**
 *  Debugging options.
 */
$config['debug'] = false;
$config['debugHide'] = false; // Hides debug info in HTML comments so you have to view source to see it.
$config['mode'] = 'development';

/**
 *  This is your base URL.
 *  I.E. www.MySite.com
 */
$config['base_url'] = 'localhost';

/**
 *  If site URL is www.MySite.com
 *  set this to '/'
 *  If site URL is www.MySite.com/app/
 *  set this to '/app/'
 *
 *  DONT FORGET ENDING SLASH!!!
 *
 *  Note: This has nothing (directly) to do with the directory structure on your server.
 *  This tells Cora where in the URL to start looking for a controller.
 *  WWW.MYSITE.COM/APP/controller/method/id
 *  If site_url is set to '/app/' then the uppercase part of the url above will be ignored
 *  by the router.
 */
$config['site_url'] = '/tts/';

/**
 *  Should URLs be converted to lowercase?
 *  If your project is hosted on a case-sensitive system (e.g. Linux) and you want a URL to
 *  work regardless of whether a user typed part of it as uppercase, then having this option on
 *  can be useful. If however you want your URLs to be case sensitive, then turn this off.
 */
$config['lowercase_url'] = true;


/**
 *  Email settings. Need to set these if you want to send emails using Cora's Mailer class.
 */
$config['smtp_host'] = '';
$config['smtp_port'] = 587;
$config['smtp_secure'] = 'tls';
$config['smtp_auth'] = 'true';
$config['smtp_username'] = '';
$config['smtp_password'] = '';

/**
 *  When in development mode, emails send using Cora's Mailer class will get sent
 *  to the admin email setup using the following:
 */
$config['admin_email'] = '';

/**
 *  If you want to extend the base Cora controller class, add
 *  a <name>.php file to the Cora\Extensions directory that is
 *  a class that extends Cora. Then enter <name> below.
 */
//$config['cora_extension'] = 'MyApp';

/**
 *  Default Controller to try and load if one's not specified.
 */
$config['default_controller'] = 'Home';

/**
 *  Default Method within a controller to try and load if one's not specified.
 */
$config['default_method'] = 'index';

// Default template to use.
$config['template'] = 'template';

/**
 *  Enable RESTful routing?
 *
 *  If turned on:
 *  POST request to "Users" class and "index" method will get routed to the "indexPOST" method within Users.
 *  PUT request to "Users" class and "index" method will get routed to the "indexPUT" method within Users.
 */
$config['enable_RESTful'] = true;

/**
 *  ONLY CHANGE THIS VARIABLE IF YOU KNOW WHAT YOU ARE DOING. READ BELOW.
 *  
 *  The RELATIVE filepath to this app's base directory on the server from where this file is located.
 *  Note that this should NOT need to get changed!
 *  The only conceivable reason you might want to change this is if you aren't using composer or the demo
 *  project to install Cora, and instead are placing the Cora system files in some custom place.
 */
$config['basedir'] = realpath(dirname(__FILE__).'/../../../../../').'/';

/**
 *  Path to models directory relative to this file.
 */
$config['pathToModels'] = $config['basedir'].'models/';

/**
 *  Path to views directory relative to this file.
 */
$config['pathToViews'] = $config['basedir'].'views/';

/**
 *  Path to controllers directory relative to this file.
 */
$config['pathToControllers'] = $config['basedir'].'controllers/';

/**
 *  Path to libraries directory relative to this file.
 */
$config['pathToLibraries'] = $config['basedir'].'libraries/';

/**
 *  Path to events directory relative to this file.
 */
$config['pathToEvents'] = $config['basedir'].'events/';

/**
 *  Path to listeners directory relative to this file.
 */
$config['pathToListeners'] = $config['basedir'].'listeners/';

/**
 *  Path to App's Cora directory relative to this file.
 */
$config['pathToCora'] = $config['basedir'].'cora/';


/**
 *  Model/Class file prefix. I.e. If your class files are named "class.MyClass.inc.php"
 *  then enter 'class.' for Prefix and '.inc' for postfix.
 */
$config['modelsPrefix'] = 'model.';
$config['modelsPostfix'] = '';

/**
 *  View file prefix / postfix.
 */
$config['viewsPrefix'] = 'view.';
$config['viewsPostfix'] = '';

/**
 *  Controller file prefix / postfix.
 */
$config['controllersPrefix'] = 'controller.';
$config['controllersPostfix'] = '';

/**
 *  Library file prefix / postfix.
 */
$config['librariesPrefix'] = '';
$config['librariesPostfix'] = '';

/**
 *  Event file prefix / postfix.
 */
$config['eventsPrefix'] = 'event.';
$config['eventsPostfix'] = '';

/**
 *  Listener file prefix / postfix.
 */
$config['listenerPrefix'] = 'listen.';
$config['listenerPostfix'] = '';