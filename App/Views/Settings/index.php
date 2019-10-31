{% extends "base.html" %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
  <div class="row">
    <div class="col-lg-12 main-title">
      <h1>Settings</h1>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Authentification
        </div>
        <div class="card-body">
          API token: 
          <code>{{token}}</code>

        </div>
      </div>
    </div>
  </div>
</div>

{% endblock %}
