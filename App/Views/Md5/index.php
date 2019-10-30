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
          <a href="/pimdna/public/md5/generate" class="btn btn-success btn-generate" role="button">
            Generate MD5
          </a>
          {% if (file_exist) %}
          <a href="/pimdna/public/md5/compare" class="btn btn-primary btn-compare" role="button">
            Compare MD5
          </a>
          {% endif %}


        </div>
      </div>
    </div>
  </div>
</div>
<!-- <div class="container" style="margin-top:100px;">
  <div class="row">
    <div class="col-lg-12">

        <div class="card">
          <div class="card-header">
            Debug
          </div>
          <div class="card-body">
            {% if (file_exist) %}
              true
            {% else %}
              false
            {% endif %}

            <ul>
              {% for content in content_dir %}
                <li>{{content}}</li>
              {% endfor %}
            </ul>

            {{test | raw}}


          </div>
        </div>




    </div>
  </div>
</div> -->


{% endblock %}
