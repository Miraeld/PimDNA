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
  $('.btn-compare_v2').on('click', function (e) {
    e.preventDefault();
    $('.generation_process .card-header h3').text('Comparing Process');
    $('.generation_process').slideDown();
    $('.ajax-res p').text('Initialization...');
    var jsonResponse = '', lastResponseLen = false;
    var start = new Date().getTime();
    $.ajax({
    	url: '/pimdna/public/md5/compare_init',
    	success: function (response)
    	{
    		jsonResponse = JSON.parse(response);

    		data_dna = JSON.stringify(jsonResponse['dna_array']);
    		data_folder = JSON.stringify(jsonResponse['content_folder']);

    		$.ajax({
    			url: '/pimdna/public/md5/compare_compare',
    			method: 'POST',
    			data : { dna: data_dna, folder:data_folder },
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

              for (var i in strLines) {
                try {
                  var jsonResponse = JSON.parse(strLines[i]);
                  console.log(jsonResponse);
                  if (jsonResponse.status == 0)
                  {
                    $('.ajax-res p').text('Processed '+jsonResponse.count+' of '+jsonResponse.total + ' - Step: ' + jsonResponse.type);
                    $(".progress-bar").css('width', jsonResponse.progress+'%').text(jsonResponse.progress+'%');
                  }
                  else if (jsonResponse.status == 1)
                  {
                    console.log('status == 1');
                    // jsonResponse = JSON.parse(response);
                    if (!jsonResponse['error']) {
                        data_file_added 		= JSON.stringify(jsonResponse['file_added']);
                        data_file_no_change = JSON.stringify(jsonResponse['file_no_change']);
                        data_file_changed 	= JSON.stringify(jsonResponse['file_changed']);
                        data_file_removed 	= JSON.stringify(jsonResponse['file_removed']);

                        $.ajax({
                          url: '/pimdna/public/md5/compare_analyze',
                          method: 'POST',
                          data : {file_added: data_file_added, file_no_change: data_file_no_change, file_changed: data_file_changed, file_removed: data_file_removed},
                          success: function (response)
                          {
                            jsonResponse = JSON.parse(response);
                            console.log(jsonResponse);

                            if (!jsonResponse['error']) {
                                data_file_added 			= JSON.stringify(jsonResponse['file_added']);
                                data_file_no_change 	= JSON.stringify(jsonResponse['file_no_change']);
                                data_file_changed 		= JSON.stringify(jsonResponse['file_changed']);
                                data_file_removed 		= JSON.stringify(jsonResponse['file_removed']);
                                data_suspicious_file 	= JSON.stringify(jsonResponse['suspicious_file']);
                                data_time_processed 	= new Date().getTime() - start +"ms";
                                $.ajax({
                                  url: '/pimdna/public/md5/compare_finalyze',
                                  method: 'POST',
                                  data: {file_added: data_file_added, file_no_change: data_file_no_change, file_changed: data_file_changed, file_removed: data_file_removed, suspicious_file: data_suspicious_file, time_processed: data_time_processed},
                                  success: function (response)
                                  {
                                    jsonResponse = JSON.parse(response);

                                    console.log(jsonResponse);
                                    if (!jsonResponse.error)
                                    {
                                      $('.args_data').val(JSON.stringify(jsonResponse.datas));

                                      $('#compare_result').submit();
                                    }

                                  }
                                })
                            }
                          }
                        })
                    }

                    console.log(jsonResponse);
                  }
                } catch (err) {

                }
              }
            }
          },


    			success: function (response)
    			{

          }
        });
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
