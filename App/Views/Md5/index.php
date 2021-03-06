{% extends "base.html" %}

{% block title %}Home{% endblock %}

{% block body %}

<div class="container">
  <div class="row">
    <div class="col-lg-12 main-title">
      <h1>MD5</h1>

    </div>
  </div>
  {% if (generation_done) %}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Success!</strong> MD5 File generated!
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  {% endif %}
</div>
<!-- Progress area -->
<div class="container generation_process" style="display:none;">
  <div class="row">
    <div class="col-md-12">
      <div class="ajax-res">
        <div class="card">
          <div class="card-header">
            <h3>Generation of .pdna file</h3>
          </div>
          <div class="card-body">
            <p class="progress-txt">Processing...</p>
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>
            <div class="label_server_response"><p></p></div>
            <div class="label_current_file"><p></p></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container compare_process" style="display:none;">
  <div class="row">
    <div class="col-md-12">
      <div class="ajax-res">
        <div class="card">
          <div class="card-header">
            <h3>Comparing Process</h3>
          </div>
          <div class="card-body">
            <p class="label_step_1">Initialization...</p>
            <div class="step_1_progress progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>
            <p class="label_step_2">Compare Process...</p>
            <div class="step_2_progress progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>
            <p class="label_step_3">Analyze Process...</p>
            <div class="step_3_progress progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>
            <p class="label_step_4">Finalization...</p>
            <div class="step_4_progress progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>

            <div class="label_server_response"><p></p></div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          Informations
        </div>
        <div class="card-body">
          <!-- <h5 class="card-title"></h5> -->
          <p class="card-text">
            <span class="font-bold">PDNA File existence: </span>
            {% if (file_exist) %}
              Yes
            {% else %}
              No
            {% endif %}
          </p>
          <p class="card-text">
            <span class="font-bold">Root Folder: </span>
            {{ document_root }}
          </p>
        </div>
      </div>

    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          PDNA Files
        </div>
        <div class="card-body">
          <!-- <h5 class="card-title"></h5> -->
          <!-- <p class="card-text"> -->
            {% if (file_exist) %}
              <select class="selectpicker" id="pdna_select" data-live-search="true">
                {% for file in list_pdna %}
                  <option value="{{file.path}}">{{file.filename}}</option>
                {% endfor %}
              </select>

              <button type="button" class="download_pdna btn btn-success">Download file</a>

            {% else %}
              We didn't find any PDNA file in your app.
            {% endif %}

        </div>
      </div>

    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Controls
        </div>
        <div class="card-body" style="text-align:center">
          <!-- <a href="/pimdna/public/md5/generate" class="btn btn-success btn-generate" role="button">
            Generate MD5
          </a> -->
          <a href="/pimdna/public/md5/generate" class="btn btn-success btn-generate-2" role="button">
            Generate MD5
          </a>
          {% if (file_exist) %}
          <a href="" class="btn btn-primary btn-compare_v3" role="button">
            Compare MD5
          </a>
          {% endif %}
          {% if (result_exist) %}
          <a href="/pimdna/public/md5/results" class="btn btn-success" role="button">
            Previous Results
          </a>
          {% endif %}


        </div>
      </div>
    </div>
  </div>
</div>
<form id="compare_result" action='/pimdna/public/md5/results' method="POST">

</form>



{% endblock %}
