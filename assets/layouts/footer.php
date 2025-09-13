
    <footer id="myFooter">
        <div class="container">
                <p style="text-align: center;"><a href="../feedback/faq.php">FAQ</a> | <a href="../feedback/journal-editors.php">Journal editors</a> | <a href="../dashboards/disclaimer.php" title="Disclaimer">Disclaimer</a> | <a href="../contact" title="Contact us">Contact Us</a></p>
        </div>
        <div align="center">
            <a target="_blank" href="https://twitter.com/Rate_RegReports"><img width="20" height="20" src="../assets/images/twitter-logo.svg" border="0" alt="Twitter logo" /></a>
        </div>
        <div class="footer-copyright">
            <p>
                <a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International License</a>.
            </p>
        </div>
    </footer>

<?php if(isset($_SESSION['auth'])) { ?> 

<script src="../assets/js/check_inactive.js"></script>
    
<?php } ?>

<script>
$('.menu-toggle').click(function() {
    $('.site-nav').toggleClass('site-nav--open', 500);
    $(this).toggleClass('open');
});
</script>
</body>

</html>

<?php

if (isset($_SESSION['ERRORS']))
    $_SESSION['ERRORS'] = NULL;
if (isset($_SESSION['STATUS']))
    $_SESSION['STATUS'] = NULL;

?>