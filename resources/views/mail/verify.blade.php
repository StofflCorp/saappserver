<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="description" content="Bestätigen Sie Ihre E-Mail-Adresse">
    <meta name="author" content="Speck-Alm">
    <title>E-Mail verifizieren</title>
    <style>
    body {
      font-family: Verdana, sans-serif;
      margin: 0;
    }
    .header {
        position: relative;
        height: 200px;
    }
    .nav {
      height: 80px;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      background-color: #006600;
    }
    .logo {
      position: absolute;
      left: 10%;
    }
    .main {
      margin: 50px 10%;
      padding: 20px;
      box-shadow: 2px 2px 20px 0px lightgray;
    }
    .main .title {
      color: #006600;
    }
    .main .verification {
      margin: 20px 0;
      text-align: center;
    }
    .main .verification .verify-link {
      color: #e14575;
      text-decoration: none;
    }
    .main .verification .verify-link:hover {
      text-decoration: underline;
    }
    </style>
  </head>
  <body>
    <div class="header">
      <div class="nav"></div>
      <div class="logo">
        <a href="https://www.speck-alm.at">
          <img src="https://speckalm.htl-perg.ac.at/r/storage/logo/speckalm_logo.png" alt="Speck-Alm-Logo">
        </a>
      </div>
    </div>
    <div class="main">
      <div class="title">
        <h2>Hallo <?php echo $user->prename; ?>!</h2>
      </div>
      <div class="description">
        Willkommen auf der Speck-Alm! Bitte klicke auf den untenstehenden Link, um deine E-Mail-Adresse zu bestätigen.
      </div>
      <div class="verification">
        <a class="verify-link" href="<?php echo 'https://speckalm.htl-perg.ac.at/r/api/users/'.$user->id.'/verifyMail?token='.$token.'&hash='.$user->temp_hash; ?>">Hier klicken</a>
      </div>
    </div>
  </body>
</html>
