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
      $(".todo-completed").click(function(e) {
        e.preventDefault();
        let id = $(this)[0].id;
        let item_id = id.split("-");
        $.ajax({
          url: drupalSettings.path.baseUrl + "todolist/completed",
          data: JSON.stringify({ compelted: item_id }),
          contentType: "application/json",
          type: "POST",
          success: function(data) {
            $("#" + id).addClass("active");
          },
          error: function(error) {
            console.log(error);
          }
        });
        console.log();
      });
    }
  };
})(jQuery, Drupal);
