$(document).ready(function () {

  $('.download_pdna').on('click', function() {

    window.open($('#pdna_select').val(), '_blank');
  });

  $('.btn-generate').on('click', function () {
    $('.btn-generate').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...');
  });
  $('.btn-login').on('click', function () {
    // $('.btn-login').preventDefault();
    $('.btn-login').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Login...');
    // $('#form_login').submit();
  });
  $('.btn-compare').on('click', function () {
    $('.btn-compare').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Comparation...');
  });

  $('#delete_folder').on('click', function () {
    $('#deleteConfirm .result').text($('#delete_file_select').val());

  });

  $('#delete_yes').on('click', function () {
    $.ajax({
      url: '/pimdna/public/delete/del',
      method: 'POST',
      data: {path: $('#delete_file_select').val()},
      complete: function (response) {
        $('#deleteConfirm').fadeOut();
        window.setTimeout(function(){location.reload()},2000)

      }
    })
  });


});


$(document).ready( function () {
  var table_modified = $('#files_changed_tabled').DataTable();
  var table_removed = $('#files_removed_tabled').DataTable();
  var table_added = $('#files_added_tabled').DataTable();
  var table_suspicious = $('#files_suspicious_tabled').DataTable();
} );
