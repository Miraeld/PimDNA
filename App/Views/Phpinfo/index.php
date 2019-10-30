{% extends "base.html" %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
  <div class="row">
    <div class="col-lg-12 main-title">
      <h1>PhpInfo</h1>
      
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12">
    {{ phpinfo | raw }}



    </div>
  </div>
</div>


{% endblock %}
