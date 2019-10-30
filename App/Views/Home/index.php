{% extends "base.html" %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
  <div class="row">
    <div class="col-lg-12 main-title">
      <h1>Home</h1>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          Server Informations
        </div>
        <div class="card-body">
          <!-- <h5 class="card-title"></h5> -->
          <p class="card-text"><span class="font-bold">Hostname:</span> {{hostname}}</p>
          <p class="card-text"><span class="font-bold">OS:</span> {{os}}</p>
          <p class="card-text"><span class="font-bold">Kernel:</span> {{kernel}}</p>
          <p class="card-text"><span class="font-bold">Uptime:</span> {{uptime}}</p>
          <p class="card-text"><span class="font-bold">Last Boot:</span> {{last_boot}}</p>
          <p class="card-text"><span class="font-bold">Server Date & time:</span> {{server_date}}</p>

          <p class="card-text"><span class="font-bold">Server URL:</span> {{server_name}}</p>
          <!-- <p class="card-text"><span class="font-bold">Script filename:</span> {{script_filename}}</p> -->

          <!-- <p class="card-text"><span class="font-bold">Script name:</span> {{script_name}}</p> -->
          <p class="card-text"><span class="font-bold">Total Files:</span> {{total_files}}</p>

        </div>
      </div>

    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          Server Details
        </div>
        <div class="card-body">
          <p class="card-text"><span class="font-bold">Total Disk Space:</span> {{total_space}}</p>
          <p class="card-text"><span class="font-bold">Total Disk Free:</span> {{free_space}}</p>

          <p class="card-text font-bold">Space Used:</p>
          <div class="progress">
              <div class="progress-bar
              {% if space_percent < 50 %}
                bg-success
              {% elseif space_percent > 49 and space_percent < 75 %}
                bg-warning
              {% else %}
                bg-danger
              {% endif %}
              " style="width:{{space_percent}}%">{{space_percent}}%</div>
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
          CPU Details
        </div>
        <div class="card-body">
            <p class="card-text"><span class="font-bold">Model:</span> {{cpu_model}}</p>
            <p class="card-text"><span class="font-bold">Cores:</span> {{num_cores}}</p>
            <p class="card-text"><span class="font-bold">Speed:</span> {{cpu_frequency}}</p>
            <p class="card-text"><span class="font-bold">Cache:</span> {{cpu_cache}}</p>
            <p class="card-text"><span class="font-bold">Bogomips:</span> {{cpu_bogomips}}</p>
            <p class="card-text"><span class="font-bold">Temperature:</span> {{cpu_temp}}</p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          Memory
        </div>
        <div class="card-body">
            <p class="card-text"><span class="font-bold">Used:</span> {{memory_used}}</p>
            <p class="card-text"><span class="font-bold">Free:</span> {{memory_free}}</p>
            <p class="card-text"><span class="font-bold">Total:</span> {{memory_total}}</p>
            <p class="card-text font-bold">Used %:</p>
            <div class="progress">
                <div class="progress-bar
                {% if memory_p_used < 50 %}
                  bg-success
                {% elseif memory_p_used > 49 and memory_p_used < 75 %}
                  bg-warning
                {% else %}
                  bg-danger
                {% endif %}
                " style="width:{{memory_p_used}}%">{{memory_p_used}}%</div>
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
          Services Status
        </div>
        <div class="card-body">
          {% for service in services %}
            <p>
              {% if service.status %} 
                  <span class="online">online</span>
              {% else %}
                  <span class="offline">offline</span>
              {% endif %}
              <span class="font-bold">{{service.name}}</span> - {{service.port}}</p>

          {% endfor %}


        </div>
      </div>
    </div>
  </div>
</div>


{% endblock %}
