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

  $('.btn-compare').on('click', function (e) {
    e.preventDefault();
    $('.generation_process .card-header h3').text('Analyzing the website');
    $('.btn-compare').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Comparation...');
    $('.generation_process').slideDown();
    var jsonResponse = '', lastResponseLen = false;

        $('.ajax-res').slideDown();
        $.ajax({
            url: '/pimdna/public/md5/compare',
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

                    // jsonResponse = JSON.parse(thisResponse);
                    // console.log(thisResponse);
                    var strLines = thisResponse.split("--");
                    strLines.pop();
                    console.log(strLines);
                    for (var i in strLines) {
                      try {
                        var jsonResponse = JSON.parse(strLines[i]);
                        // console.log(jsonResponse);
                        console.log('Status : ' +jsonResponse.status);
                        console.log(jsonResponse);
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

  //////////////////////////////////
  //////////////////////////////////
  /////////// COMPARE V2 ///////////
  //////////////////////////////////
  //////////////////////////////////
  var counter = 0;
  var total_files = 0;
  $('.btn-compare_v2').on('click', function (e) {
    e.preventDefault();
    // $('.compare_process .card-header h3').text('Comparing Process');
    $('.compare_process').slideDown();
    // $('.ajax-res .label_step_1').text('Initialization...');

      var interval = setInterval(function() {
          counter++;
          $('.label_server_response p').text('Last server response : '+counter+'s');
          $.ajax({
            url: '/pimdna/public/md5/progress',
            success: function (response)
            {
              if (response != '')
              {
                let percent = (response/total_files) * 100;
                $('.ajax-res .label_step_2').text('Processed '+response+' of '+total_files+' - Step: Comparing' );
                $(".compare_process .step_2_progress .progress-bar").css('width', percent.toFixed(2)+'%').text(percent.toFixed(2)+'%');
                counter = 0;
              }
              console.log(response);
            }
          });

          if (counter == 100000) {
              // Display a login box
              clearInterval(interval);
          }
      }, 1000);

    var jsonResponse = '', lastResponseLen = false;
    var start = new Date().getTime();
    $.ajax({
    	url: '/pimdna/public/md5/compare_init',
      timeout:0,
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
            }
          });

          console.log('compare_init status == 1');
    		  $.ajax({
    			url: '/pimdna/public/md5/compare_compare',
          timeout:0,
          xhrFields:
          {
            onprogress: function(e)
            {
              var thisResponse, response = e.currentTarget.response;
              if(lastResponseLen === false) {
                thisResponse = response;
                lastResponseLen = response.length;
              } else {
                thisResponse = response.substring(lastResponseLen);
                lastResponseLen = response.length;
              }

              var strLines = thisResponse.split("--");
              strLines.pop();

              // console.log(strLines);
              for (var i in strLines) {
                try {

                  var jsonResponse = JSON.parse(strLines[i]);

                  if (jsonResponse.status == 0)
                  {
                    counter = 0;
                    // $('.ajax-res .label_step_2').text('Processed '+jsonResponse.count+' of '+jsonResponse.total + ' - Step: ' + jsonResponse.type);
                    // $(".compare_process .step_2_progress .progress-bar").css('width', jsonResponse.progress+'%').text(jsonResponse.progress+'%');
                  }
                  else if (jsonResponse.status == 1)
                  {
                    console.log('compare_compare status == 1');
                    counter = 0;
                    // console.log('status == 1');
                    // jsonResponse = JSON.parse(response);
                    if (!jsonResponse['error']) {
                      $('.ajax-res .label_step_2').text('Compare Process - Done.');
                      $(".step_2_progress .progress-bar").css({
                          width:'100%',
                          backgroundColor: 'green'
                      });

                      $.ajax({
                          url: '/pimdna/public/md5/compare_analyze',
                          method: 'POST',
                          timeout:0,
                          xhrFields:
                          {
                            onprogress: function(e)
                            {
                              var thisResponse, response = e.currentTarget.response;
                              if(lastResponseLen === false) {
                                thisResponse = response;
                                lastResponseLen = response.length;
                              } else {
                                thisResponse = response.substring(lastResponseLen);
                                lastResponseLen = response.length;
                              }

                              var strLines = thisResponse.split("--");
                              strLines.pop();

                              // console.log(strLines);
                              for (var i in strLines) {
                                try
                                {
                                  var jsonResponse = JSON.parse(strLines[i]);
                                  console.log(jsonResponse);
                                  if (jsonResponse.status == 0)
                                  {
                                    counter = 0;
                                    $('.ajax-res .label_step_3').text('Processed '+jsonResponse.count+' of '+jsonResponse.total + ' - Step: ' + jsonResponse.type);
                                    $(".compare_process .step_3_progress .progress-bar").css('width', jsonResponse.progress+'%').text(jsonResponse.progress+'%');
                                  }
                                  else if (jsonResponse.status == 1)
                                  {
                                    console.log('compare_analyze status == 1');
                                    if (jsonResponse.error == false)
                                    {
                                      counter = 0;
                                      $('.ajax-res .label_step_3').text('Analyze Process - Done.');
                                      $(".step_3_progress .progress-bar").css({
                                          width:'100%',
                                          backgroundColor: 'green'
                                      });

                                      data_time_processed 	= new Date().getTime() - start +"ms";
                                      $.ajax({
                                        url: '/pimdna/public/md5/compare_finalyze',
                                        method: 'POST',
                                        timeout:0,
                                        data : {time_processed: data_time_processed},
                                        // data: {file_added: data_file_added, file_no_change: data_file_no_change, file_changed: data_file_changed, file_removed: data_file_removed, suspicious_file: data_suspicious_file, time_processed: data_time_processed},
                                        xhrFields:
                                        {
                                          onprogress: function(e)
                                          {
                                            var thisResponse, response = e.currentTarget.response;
                                            if(lastResponseLen === false) {
                                              thisResponse = response;
                                              lastResponseLen = response.length;
                                            } else {
                                              thisResponse = response.substring(lastResponseLen);
                                              lastResponseLen = response.length;
                                            }

                                            var strLines = thisResponse.split("--");
                                            strLines.pop();

                                            // console.log(strLines);
                                            for (var i in strLines) {
                                              try
                                              {
                                                var jsonResponse = JSON.parse(strLines[i]);
                                                // console.log('jsonResponse');
                                                // console.log(jsonResponse);
                                                if (jsonResponse.status == 0)
                                                {
                                                  counter = 0;
                                                  $('.ajax-res .label_step_4').text('Processed '+jsonResponse.count+' of '+jsonResponse.total + ' - Step: ' + jsonResponse.type);
                                                  $(".compare_process .step_4_progress .progress-bar").css('width', jsonResponse.progress+'%').text(jsonResponse.progress+'%');
                                                }
                                                else if (jsonResponse.status == 1)
                                                {
                                                  console.log('compare_finalyze status == 1');
                                                  $('.ajax-res .label_step_4').text('Preparing Results - Done.');
                                                  $(".step_4_progress .progress-bar").css({
                                                      width:'100%',
                                                      backgroundColor: 'green'
                                                  });
                                                  if (!jsonResponse.error)
                                                  {
                                                    console.log('compare_finalyze jsonResponse.error == false');

                                                    counter = 0;
                                                    // $('.args_data').val(JSON.stringify(jsonResponse.datas));
                                                    $(".compare_process .step_4_progress .progress-bar").css('width', '100%').text('100%');
                                                    $(".step_4_progress .progress-bar").css({
                                                        width:'100%',
                                                        backgroundColor: 'green'
                                                    });
                                                    clearInterval(interval);

                                                    // $('#compare_result').submit();

                                                  }
                                                }
                                                else {
                                                  console.log('compare_finalyze else');
                                                }

                                                // .compare_process .step_4_progress

                                              }
                                              catch (err)
                                              {
                                                alert('Error while finalyzing the process');
                                              }
                                            }
                                          }
                                        }
                                        // success: function (response)
                                        // {
                                        //   jsonResponse = JSON.parse(response);
                                        //
                                        //   console.log(jsonResponse);
                                        //   if (!jsonResponse.error)
                                        //   {
                                        //     $('.args_data').val(JSON.stringify(jsonResponse.datas));
                                        //
                                        //     $('#compare_result').submit();
                                        //   }
                                        //
                                        // }
                                      });
                                    } else {
                                      alert('Error while analyzing');
                                    }
                                  }
                                  else {
                                    console.log('compare_analyze else');
                                  }
                                }
                                catch (err)
                                {

                                }
                              }
                            }
                          }

                        });

                    }

                    console.log(jsonResponse);
                  }
                  else {
                    console.log('compare_compare else');
                  }

                } catch (err)
                {

                }
              }
            }
          },
          error: function (error) {
            console.log('Error : ');
            console.log(error);
            if (error.status == 503) {
              console.log('Error 503');
            }
          }
        });
        }
      }
    });
  })
  //////////////////////////////////
  //////////////////////////////////
  /////////// COMPARE V2 ///////////
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
