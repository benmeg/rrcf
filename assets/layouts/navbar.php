<header>
<div class="container">

  <h1 class="logo"><a href="../">Registered <span>Reports</span></a><p>Community Feedback<img src="../assets/images/header_stars.png" alt="Image of stars, showing a four out of five stars rating" /></p></h1>

  <nav class="site-nav">
      <ul>
        <li><a href="../dashboards">Dashboards</a></li>
        <li><a href="../feedback">Feedback</a></li>
<?php

if (!isset($_SESSION['auth'])) {

?>
        <li><a href="../register">Register</a></li>
        <li><a href="../login">Login</a></li>

<?php
}

if (isset($_SESSION['auth'])) {

?>
        <li><a href="../profile">Profile</a></li>
        <li><a href="../logout">Logout</a></li>
<?php
}
?>
      </ul> 
  </nav>
  
  <div class="menu-toggle">
    <div class="hamburger"></div>
  </div>
  
</div>

</header>