jQuery(function ($) {
  var $table = $('.wsuwp-multisite-information');

  if ($.fn.DataTable) {
    if (!$.fn.dataTable.isDataTable($table)) {
      $table.DataTable({
        paging: false,
        info: false
      });
    }
  }

  $('#wsuwp-per-page').on('change', function () {
    var perPage = $(this).val();
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('paged', '1');
    window.location.href = url.toString();
  });
});