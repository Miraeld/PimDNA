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
            timeout:0,
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
                    console.log(strLines);
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


  //////////////////////////////////
  //////////////////////////////////
  /////////// COMPARE V3 ///////////
  //////////////////////////////////
  //////////////////////////////////


$('.btn-compare_v3').on('click', function (e) {

    e.preventDefault();
    // $('.compare_process .card-header h3').text('Comparing Process');
    $('.compare_process').slideDown();
    var current_step_name = '';
    var interval = setInterval(function() {
        counter++;
        // console.log('CURRENT STEP NAME = '+ current_step_name);
        $('.label_server_response p').text('Last server response : '+counter+'s');
        $.ajax({
          url: '/pimdna/public/md5/progress',
          method: 'POST',
          data: { action : current_step_name },
          beforeSend: function () {
            // console.log('/pimdna/public/md5/progress');
          },
          success: function (response)
          {
            if (response != '')
            {
              switch (current_step_name)
              {
                  case 'compare_init':
                  break;
                  case 'compare_compare':
                    percent = (response/total_files) * 100;
                    $('.ajax-res .label_step_2').text('Processed '+response+' of '+total_files+' - Step: Comparing' );
                    $(".compare_process .step_2_progress .progress-bar").css('width', percent.toFixed(2)+'%').text(percent.toFixed(2)+'%');
                    counter = 0;
                  break;
                  case 'compare_analyze':
                    response = JSON.parse(response);
                    percent = (response.current_entrie/response.total_files) * 100;
                    $('.ajax-res .label_step_3').text('Processed '+response.current_entrie+' of '+response.total_files+' - Step: Analyzing - '+response.step );
                    $(".compare_process .step_3_progress .progress-bar").css('width', percent.toFixed(2)+'%').text(percent.toFixed(2)+'%');
                    counter = 0;
                  break;
                  case 'compare_finalyze':

                    response = JSON.parse(response);
                    percent = (response.step/response.total_step)*100;

                    $('.ajax-res .label_step_4').text('Step ' + response.step + ' of '+ response.total_step + ' - '+response.text);
                    $(".compare_process .step_4_progress .progress-bar").css('width', percent.toFixed(2)+'%').text(percent.toFixed(2)+'%');
                    counter = 0;

                  break;
                  case 'redirection' :
                    clearInterval(interval);
                    break;

              }

            }
            // console.log(response);
            // console.log(current_step_name);
          }
        });

        if (counter == 5000) {

            clearInterval(interval);
        }
    }, 1000);

  var jsonResponse = '', lastResponseLen = false;
  var start = new Date().getTime();
  $.ajax({
    url: '/pimdna/public/md5/compare_init',
    timeout:0,
    beforeSend: function() {
      // setting a timeout
      current_step_name = 'compare_init';
      // console.log('PROCESS => current_step_name = ' + current_step_name);
    },
    success: function (response)
    {
      $(".compare_process .step_1_progress .progress-bar").css('width', '100%').text('100%');
      jsonResponse = JSON.parse(response);

      // data_dna = JSON.stringify(jsonResponse['dna_array']);
      // data_folder = JSON.stringify(jsonResponse['content_folder']);
      $('.ajax-res .label_step_1').text('Initialization - Done.');
      $(".step_1_progress .progress-bar").css({
          width:'100%',
          backgroundColor: 'green'
      });
      counter = 0;

      console.log(jsonResponse);
      if (!jsonResponse['error'] && jsonResponse['status'])
      {
        $.ajax({
          url: '/pimdna/public/md5/getInit',
          success: function (response)
          {
            if (response != '')
            {
              total_files = response;
            }
            console.log(response);
            // START COMPARE_COMPARE
            //
            console.log('COMPARE_COMPARE');
            $.ajax({
              url: '/pimdna/public/md5/compare_compare',
              beforeSend: function() {
                current_step_name = 'compare_compare';
                // console.log('PROCESS => current_step_name = ' + current_step_name);
              },
              timeout:0,
              success: function (response) {

                $('.ajax-res .label_step_2').text('Comparison - Done.');
                $(".step_2_progress .progress-bar").css({
                    width:'100%',
                    backgroundColor: 'green'
                });
                $(".compare_process .step_2_progress .progress-bar").text(100+'%');
                counter = 0;
                // current_step_name = 'compare_analyze';
                $.ajax({
                  url: '/pimdna/public/md5/compare_analyze',
                  timeout:0,
                  beforeSend: function () {
                    current_step_name = 'compare_analyze'
                  },
                  success: function () {

                    $('.ajax-res .label_step_3').text('Analyze - Done.');
                    $(".step_3_progress .progress-bar").css({
                        width:'100%',
                        backgroundColor: 'green'
                    });
                    $(".compare_process .step_3_progress .progress-bar").text(100+'%');

                    data_time_processed 	= new Date().getTime() - start +"ms";
                    $.ajax({
                      url: '/pimdna/public/md5/compare_finalyze',
                      beforeSend: function () {
                        current_step_name = 'compare_finalyze';
                      },
                      timeout:0,
                      method: 'POST',
                      data : {time_processed: data_time_processed},
                      success: function () {
                        console.log('COMPARE FINALYZE FINISHED');
                        $('.ajax-res .label_step_4').text('Comparison - Done.');
                        $(".step_4_progress .progress-bar").css({
                            width:'100%',
                            backgroundColor: 'green'
                        });
                        $(".compare_process .step_4_progress .progress-bar").text(100+'%');
                        clearInterval(interval);
                        current_step_name = 'redirection';
                        setTimeout(function(){
                          $('#compare_result').submit();
                        }, 2500);

                      }
                    })
                  }
                })
              }

            });

          }
        });
      }
    }
  });

});




  //////////////////////////////////
  //////////////////////////////////
  /////////// COMPARE V3 ///////////
  //////////////////////////////////
  //////////////////////////////////



















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
      async: true,
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
