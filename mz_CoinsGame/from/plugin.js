$(function() {
  $("#jwt-btn-submit").click(function() {
    const $name = $("#jwt-name");
    $name.val($name.val().replace(/\\|\/|:|\*|\?|"|<|>|\|/g, ""));
  });
  $("#jwt-btn-sign").click(function() {
    $("#jwt-form .act").val("sign");
    $("#jwt-form").submit();
  });
  $(".mz-note.fade-out").delay(730).fadeOut(730);
});
