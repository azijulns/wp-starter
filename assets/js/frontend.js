let direction = jQuery("html").attr("dir");

jQuery(document).ready(function ($) {
  // Offcanvas
  jQuery(".hamburger .elementor-icon").on("click", function (e) {
    e.preventDefault();
    jQuery(".offcanvas").addClass("active");
  });
  jQuery(".offcanvas__close").on("click", function () {
    $("body").removeClass("offcanvas");
    jQuery(".offcanvas").removeClass("active");
  });


});

// jQuery(window).on("elementor/frontend/init", function () {
//   function handlerFn() {
//     // ********** Testimonials
//     jQuery(".ae-testimonials__slider").slick({

//     });
//   }

//   elementorFrontend.hooks.addAction(
//     "frontend/element_ready/dm_testimonials.default",
//     handlerFn
//   );
// });


// $("body").on("click", ".services__tab-btn", function (e) {
//     e.preventDefault();

//     // category;
//     let cat_number = jQuery(this).attr("data-filter");
//     let specialist = jQuery(this).attr("date-specialist");
//     let btn = $(this);
//     $(".services__tab-btn").removeClass("services__tab-btn--active");
//     $(this).addClass("services__tab-btn--active");

//     if (!is_filter && cat_number) {
//         // Ajax
//         jQuery.ajax({
//             url: ajax_helper.ajaxurl,
//             type: "POST",
//             data: {
//                 action: "get_post_by_meta_cat",
//                 security: ajax_helper.security,
//                 specialist,
//                 cat_number,
//             },

//             beforeSend: function () {
//                 // Set is_fach to true to prevent multiple requests
//                 is_filter = true;
//                 // Show loading state
//                 jQuery(btn).css({
//                     opacity: 0.5,
//                     cursor: "not-allowed",
//                 });
//             },
//             success: function (response) {
//                 // Reset is_fach to false after receiving response
//                 is_filter = false;
//                 jQuery(".services__slider").slick("unslick");

//                 jQuery(".services__slider").html(response.html);
//                 selickAc(".services__slider");
//                 // Restore button opacity
//                 jQuery(btn).css({
//                     opacity: 1,
//                     cursor: "pointer",
//                 });
//             },
//             error: function (error) {
//                 console.log(error);
//                 // Handle errors
//                 // Reset is_fach to false
//                 is_filter = false;
//                 // Restore button opacity
//                 jQuery(btn).css({
//                     opacity: 1,
//                     cursor: "pointer",
//                 });
//             },
//         });
//     }
// });