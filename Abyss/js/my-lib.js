// //Author @Abyss
//
// Client-side validation functions.
//
// Drupal.behaviors.Abyss = {
//
//   attach: function (context, settings) {
//
//     (function ($, Drupal) {
//       //Name validate
//       $($('[id*="abyss-form"]').find('[id*="edit-name"]')).on('change', function (param) {
//         let element = $(param.target);
//         if (element.val().length <= 2)
//         {
//           element.attr("placeholder", "Required field");
//           element.css("box-shadow","inset 0 0 13px rgba(228, 106, 106, 0.75)");
//           element.css("border","1px solid #f5c6c6");
//           return 0;
//         }
//         else if (element.val().length > 2)
//         {
//           element.attr("placeholder", "");
//           element.css("box-shadow","");
//           element.css("border","1px solid #e1e1e1");
//           return 1;
//         }
//       });
//
//       //Email validate
//       $($('[id*="abyss-form"]').find('[id*="edit-email"]')).on('change', function (param) {
//         let element = $(param.target);
//         var re = /^[\w-\.]+@[\w-]+\.[a-z]{2,4}$/i;
//         var valid = re.test(element.val());
//         if (element.val().length === 0 || !valid) {
//           element.attr("placeholder", "Required field");
//           element.css("box-shadow", "inset 0 0 13px rgba(228, 106, 106, 0.75)");
//           element.css("border", "1px solid #f5c6c6");
//         } else {
//           element.attr("placeholder", "");
//           element.css("box-shadow", "");
//           element.css("border", "1px solid #e1e1e1");
//         }
//       });
//
//       //Phone validate
//       $($('[id*="abyss-form"]').find('[id*="edit-phone"]')).on('change', function (param) {
//         let element = $(param.target);
//         var re = /^\d[\d\(\)\ -]{4,14}\d$/;
//         var valid = re.test(element.val());
//         if (element.val().length === 0 || !valid)
//         {
//           $(element).once().before('<span class="abyss-form-error">Required field</span>');//.css("box-shadow","inset 0 0 13px rgba(228, 106, 106, 0.75)");
//           element.css("border","1px solid #f5c6c6");
//         }
//         else
//         {
//           element.attr("placeholder", "");
//           element.css("box-shadow","");
//           element.css("border","1px solid #e1e1e1");
//         }
//       });
//
//       //Phone validate
//       $($('[id*="abyss-form"]').find('[id*="edit-response"]')).on('change', function (param) {
//         let element = $(param.target);
//         if (element.val().length === 0)
//         {
//           element.attr("placeholder", "Required field");
//           element.css("box-shadow","inset 0 0 13px rgba(228, 106, 106, 0.75)");
//           element.css("border","1px solid #f5c6c6");
//           return 0;
//         }
//         else
//         {
//           element.attr("placeholder", "");
//           element.css("box-shadow","");
//           element.css("border","1px solid #e1e1e1");
//           return 1;
//         }
//       });
//     }(jQuery, Drupal));
//   }
// };

Drupal.behaviors.Abyss = {

  attach: function (context, settings) {

    (function ($, Drupal) {
      //
      $($('[id*="abyss-form"]').find('[id*="edit-delete"]')).on('click', function (param) {
        let element = $(param.target);
        let route = $(element.parent('[id*="abyss-form"]'));
        route = $(route.parent('[id*="drupal-dialog-abyssform"]'));
        route = $(route.parent('.ui-dialog'));
        route.empty();
      });
      // Function to add / remove a style at the touch of a button.
      $('.abyss-hidden').once().on('click', function (param){
        let element = $(param.target);
        $($(element.parent()).children('ul')).toggleClass('abyss-active');
        element.toggleClass('abyss-button-active');
      });
      // Function to remove the style from the button, when you hover over the response.
      $('.guest').once().on('mouseleave', function (){
        let element = $('.guest');
        element = $(element.children('.contextual'));
        let element_list = $(element.children('ul'));
        element_list.removeClass('abyss-active');
        $(element.children('button')).removeClass('abyss-button-active');
      });
    }(jQuery, Drupal));
  }
};
