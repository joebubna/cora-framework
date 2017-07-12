<?php
namespace Cora;
/**
* 
*/
class Path
{
    // If set to true, will output debugging info on failed match. 
    public $debug = false;
    
    ///////////////////////////////////////////////
    // Path Template
    ///////////////////////////////////////////////
    /** 
    *  This variable defines the user-friendly format for specifying custom URL paths.
    *  URL variables should be a name between brackets.
    *
    *  Example: $this->url = 'users/{action}-{subaction}/{id}'
    */
    public $url = '';

    ///////////////////////////////////////////////
    // Template variables definition
    ///////////////////////////////////////////////
    /** 
    *   This is for specifying custom rules for the bracket variables defined in a custom path template. 
    *   Rules should use regex syntax.
    *  
    *   Example: $this->def['{id}'] = '[0-9]+';
    */
    public $def = [];

    ///////////////////////////////////////////////
    // HTTP Actions to match
    ///////////////////////////////////////////////
    /** 
    *   By default, this path won't care what the HTTP request method is. If you want to limit 
    *   valid matches to this path to specific methods, they can be specified.
    *   Ex: $actions = 'GET|POST'
    */
    public $actions = 'all';

    ///////////////////////////////////////////////
    // Route to execute if URL matches format
    ///////////////////////////////////////////////
    /** 
    *   Below is route to execute if the URL matches the defined pattern.
    *   Can be left undefined if you just want to use the preExec function and 
    *   execute automatic routing if the preExec returns true.
    */
    public $route = false;

    ///////////////////////////////////////////////
    // Use RESTful routing?
    ///////////////////////////////////////////////
    /** 
    *   By default "POST", "PUT", etc is added to the end of the method name to be executed. 
    *   If you want disable this behavior for this specific route, set this to false.
    */
    public $RESTful = true;

    ///////////////////////////////////////////////
    // Pre-match function
    ///////////////////////////////////////////////
    /** 
    *   If you want to check additional things before matching a url to a path.
    *   If this returns FALSE, then a path is rejected as a match even if the URL would 
    *   otherwise have been a match.
    */
    public $preMatch;

    ///////////////////////////////////////////////
    // Pre-execution function
    ///////////////////////////////////////////////
    /** 
    *   If you want to do some permission checking before executing a route. 
    *   If this returns TRUE, then route executes, if FALSE, 
    *   then returns access denied.
    */
    public $preExec;

    ///////////////////////////////////////////////
    // Method Arguments
    ///////////////////////////////////////////////
    /** 
    *   This should be a function that returns an array of arguments to 
    *   be passed to the method this path resolves to.
    *  
    *   Example: $path->args = function($vars, $app) { return [$vars['id']]; }
    */
    public $args;

    ///////////////////////////////////////////////
    // Passive
    ///////////////////////////////////////////////
    /** 
    *   If you want the search for custom paths to continue even after matching this path,
    *   then define this path as passive. This could be useful if you wanted to perform authentication 
    *   on an entire section of a site. 
    *   I.E. "$path->url = 'users/{anything}'
    */
    public $passive = false;

    public function __construct($setup = false) 
    {
        // Setting default preMatch function.
        $this->preMatch = function() {
            return true;
        };
        
        // Setting default preExec function.
        $this->preExec = function() {
            return true;
        };

        // Load definition if passed in as an array.
        if (is_array($setup)) {
            foreach($setup as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function preMatchCheck($vars = [], $c = false) 
    {
        $preMatchClosure = $this->preMatch;
        return $preMatchClosure($vars, $c);
    }

    public function preExecCheck($vars = [], $c = false) 
    {
        $preExecClosure = $this->preExec;
        return $preExecClosure($vars, $c);
    }

    public function getArgs($vars = [], $c = false)
    {
        $argsClosure = $this->args;
        return $argsClosure($vars, $c);
    }
}