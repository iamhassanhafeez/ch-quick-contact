jQuery(document).ready(function ($) {
  $("#qc-form").on("submit", function (e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.post(
      qc_ajax.ajax_url,
      {
        action: "qc_submit_form",
        nonce: qc_ajax.nonce,
        ...Object.fromEntries(new URLSearchParams(formData)),
      },
      function (response) {
        $("#qc-response").text(
          response.success ? response.data : response.data
        );
      }
    );
  });
});
