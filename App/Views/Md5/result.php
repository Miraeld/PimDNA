{% extends "base.html" %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
  <div class="row">
    <div class="col-lg-12 main-title">
      <h1>MD5 - Comparison Results</h1>
    </div>
  </div>
  {% if (mail_result) %}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Success!</strong> Mail sent successfully!
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  {% else %}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Error!</strong> We got a problem while sending the email!
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  {% endif %}
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Results Summary
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <!-- <h5 class="card-title"></h5> -->
              <p class="card-text">
                <span class="font-bold"><span class="font-success">File not changed: </span></span>
              {{file_no_change_count}} files
              </p>
              <p class="card-text">
                <span class="font-bold"><span class="font-warning">File changed: </span></span>
              {{file_changed_count}} files
              </p>
              <p class="card-text">
                <span class="font-bold"><span class="font-danger">File removed: </span></span>
              {{file_removed_count}} files
              </p>
              <p class="card-text">
                <span class="font-bold"><span class="font-warning">File added: </span></span>
              {{file_added_count}} files
              </p>
              <p class="card-text">
                <span class="font-bold">Time processed: </span>
              {{time_processed}}
              </p>
            </div>
            <div class="col-md-6">
              <canvas id="stats_chart_1"></canvas>
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
          List of Modified Files
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="files_changed_tabled" class="display nowrap" width="100%">
            <thead>
              <tr>
                <th>Path</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Path</th>
              </tr>
            </tfoot>
            <tbody>
              {% for file in file_modified %}
                {% if file.path is not empty %}
                  <tr>
                    <td>{{file.path}}</td>
                  </tr>
                {% endif %}
              {% endfor %}
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          List of Removed Files
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="files_removed_tabled" class="display nowrap" width="100%">
            <thead>
              <tr>
                <th>Path</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Path</th>
              </tr>
            </tfoot>
            <tbody>
              {% for file in file_removed %}
                {% if file.path is not empty %}
                  <tr>
                    <td>{{file.path}}</td>
                  </tr>
                {% endif %}
              {% endfor %}
            </tbody>
          </table>
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
          List of Added Files
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="files_added_tabled" class="display nowrap" width="100%">
            <thead>
              <tr>
                <th>Path</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Path</th>
              </tr>
            </tfoot>
            <tbody>
              {% for file in file_added %}
                {% if file.path is not empty %}
                  <tr>
                    <td>{{ file.path|replace({'../..': ''}) }}</td>
                  </tr>
                {% endif %}
              {% endfor %}
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          List of Suspicious Files
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="files_suspicious_tabled" class="display nowrap" width="100%">
            <thead>
              <tr>
                <th>Path</th>
                <th>Detection</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Path</th>
                <th>Detection</th>
              </tr>
            </tfoot>
            <tbody>
              {% for file in files_suspicious %}
                {% if file.path is not empty %}
                  <tr>
                    <td>{{ file.path|replace({'../..': ''}) }}</td>
                    <td>{{ file.suspicious }}</td>
                  </tr>
                {% endif %}
              {% endfor %}
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<script>
var ctx = $('#stats_chart_1');
data = {
  datasets: [{
      data: [{{file_removed_count}}, {{file_changed_count}}, {{file_added_count}}],
      backgroundColor: [
        'red',
        'orange',
        'green',
      ]
  }],

  // These labels appear in the legend and in the tooltips when hovering different arcs
  labels: [
      'Removed',
      'Modified',
      'Added',
  ],
// 51686
};

var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: data
});
</script>

{% endblock %}
