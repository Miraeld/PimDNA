{% extends "base.html" %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
  <div class="row">
    <div class="col-lg-12 main-title">
      <h1>Delete Folder</h1>
    </div>
  </div>
</div>

<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Delete folder v1
        </div>
        <div class="card-body">

          <select class="selectpicker" id="delete_file_select" data-live-search="true">
            {% for directory in list_all_dir %}
              <option value="{{directory.filename}}">{{directory.path}}</option>
            {% endfor %}
          </select>

          <button type="button" id="delete_folder" class="btn btn-danger" data-toggle="modal" data-target="#deleteConfirm">Delete Folder</a>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal" id="deleteConfirm" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Confirmation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure to delete the folder named : <span class="result font-bold"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="delete_yes">Yes</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>


{% endblock %}
