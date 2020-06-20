jQuery(document).ready(function ($) {
  $("input#billing_postcode")
    .change(function () {
      var pincode = $(this).val();
      if (pincode.length == 6) {
        $("#billing_city").val(php_vars[pincode]["district"]);
        // $("[name=billing_state]").val("");
        $("#billing_state option")
          .filter(function () {
            return (
              $(this).html().toLowerCase() ==
              php_vars[pincode]["state"].toLowerCase()
            );
          })
          .prop("selected", true)
          .change();
      }
    })
    .change();
});
