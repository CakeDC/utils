<?php

/**
 * Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Utils Plugin
 *
 * Utils Priority Array Component that search priority elements from array and set those to start of the array. 
 *
 * @package utils
 * @subpackage utils.controllers.components
 */
class PriorityArrayComponent extends Component {

    /**
     * Controller object instance
     *
     * @var Controller
     */
    public $Controller;

    /**
     * Initialize Callback
     *
     * @param Controller object
     */
    public function initialize(Controller $controller) {
        $this->Controller = $controller;
    }

    /**
     * Startup Callback
     *
     * @param Controller object
     */
    public function startup(Controller $controller) {
        $this->Controller = $controller;
    }

    /**
     * 
     * Search the priority array and move that priority array elements 
     * to the start of array without losing the key values of array. 
     * 
     * @param type $priority_arr  set these element to the start of the array
     * @param type $arr array 
     */
    public function setPriorityElementsFirst($priority_arr = array(), $arr) {
        $search_arr = array_intersect($priority_arr, $arr);
        if (count($search_arr) > 0) {
            $priority_first_arr = array();
            foreach ($search_arr as $key => $value) {
                $key = array_search($value, $arr);
                if ($key) {
                    $priority_first_arr[$key] = $arr[$key];
                    unset($arr[$key]);
                }
            }
            if (count($priority_first_arr) > 0) {
                $arr = $priority_first_arr + $arr;
            }
        }
        return $arr;
    }

}
