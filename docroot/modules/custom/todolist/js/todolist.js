/**
 * @file
 * Behaviors for the To Do List.
 */

(function($, Drupal) {
  "use strict";

  Drupal.behaviors.todolist = {
    attach: function(context) {
      if (context != document) {
        return;
      }
      console.log("helo");
    }
  };
})(jQuery, Drupal);
