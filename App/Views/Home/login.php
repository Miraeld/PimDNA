{% extends "base.html" %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
  <div class="row">
    <div class="col-lg-12 main-title">
      <h1>Login</h1>
    </div>
  </div>
  {% if (error) %}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Error!</strong> {{msg}}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  {% endif %}
</div>


<div class="container">
  <div class="row">
    <div class="col-md-3">
    </div>
    <div class="col-md-6">
      <div class="form__box">
        <div class="form__container">
          <form action="/pimdna/public/checklogin" method="POST" id="form_login">
            <input type="text" name="pimdna_login" id="pimdna_login">
            <input type="password" name="pimdna_password" id="pimdna_password">
            <button type="submit" class="btn btn-success btn-login">
              Login
            </button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-3">
    </div>
  </div>
</div>


{% endblock %}
