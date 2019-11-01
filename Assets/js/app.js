$(document).ready(function () {

  $('.download_pdna').on('click', function() {

    window.open($('#pdna_select').val(), '_blank');
  });

  $('.btn-generate').on('click', function (e) {
    e.preventDefault();
    $('.generation_process .card-header h3').text('Generation of .pdna file');
    $('.btn-generate').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...');
    $('.generation_process').slideDown();
    var jsonResponse = '', lastResponseLen = false;

        $('.ajax-res').slideDown();
        $.ajax({
            url: '/pimdna/public/md5/generate',
            xhrFields: {
                onprogress: function(e) {
                    var thisResponse, response = e.currentTarget.response;
                    if(lastResponseLen === false) {
                        thisResponse = response;
                        lastResponseLen = response.length;
                    } else {
                        thisResponse = response.substring(lastResponseLen);
                        lastResponseLen = response.length;
                    }

                    console.log(thisResponse);
                    var strLines = thisResponse.split("--");
                    strLines.pop();
                    for (var i in strLines) {
                      try {
                        var jsonResponse = JSON.parse(strLines[i]);
                        $('.ajax-res p').text('Processed '+jsonResponse.count+' of '+jsonResponse.total);
                        $(".progress-bar").css('width', jsonResponse.progress+'%').text(jsonResponse.progress+'%');
                      }
                      catch (err) {

                      }

                    }


                }
            },
            success: function(text) {

                $('.ajax-res p').text('Process completed successfully');
                $(".progress-bar").css({
                    width:'100%',
                    backgroundColor: 'green'
                });
                window.location.href="/pimdna/public/md5/generated";
            }
        });

  });

  $('.btn-compare').on('click', function (e) {
    e.preventDefault();
    $('.generation_process .card-header h3').text('Analyzing the website');
    $('.btn-compare').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Comparation...');
    $('.generation_process').slideDown();
    var jsonResponse = '', lastResponseLen = false;

        $('.ajax-res').slideDown();
        $.ajax({
            url: '/pimdna/public/md5/compare',
            xhrFields: {
                onprogress: function(e) {
                    var thisResponse, response = e.currentTarget.response;
                    if(lastResponseLen === false) {
                        thisResponse = response;
                        lastResponseLen = response.length;
                    } else {
                        thisResponse = response.substring(lastResponseLen);
                        lastResponseLen = response.length;
                    }

                    // jsonResponse = JSON.parse(thisResponse);
                    // console.log(thisResponse);
                    var strLines = thisResponse.split("--");
                    strLines.pop();

                    for (var i in strLines) {
                      try {
                        var jsonResponse = JSON.parse(strLines[i]);
                        // console.log(jsonResponse);
                        console.log('Status : ' +jsonResponse.status);
                        if (jsonResponse.status == 1)
                        {

                          // console.log(JSON.stringify(jsonResponse.datas));
                          $('.args_data').val(JSON.stringify(jsonResponse.datas));

                          $('#compare_result').submit();

                        }
                        else
                        {
                          // console.log(jsonResponse);
                          $('.ajax-res p').text('Processed '+jsonResponse.count+' of '+jsonResponse.total + ' - Step: ' + jsonResponse.type);
                          $(".progress-bar").css('width', jsonResponse.progress+'%').text(jsonResponse.progress+'%');
                        }
                      } catch(err) {

                      }
                    }
                }
            },
            success: function(text) {

                $('.ajax-res p').text('Process completed successfully');
                $(".progress-bar").css({
                    width:'100%',
                    backgroundColor: 'green'
                });
            }
        });
  });


  $('.btn-login').on('click', function () {
    // $('.btn-login').preventDefault();
    $('.btn-login').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Login...');
    // $('#form_login').submit();
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
