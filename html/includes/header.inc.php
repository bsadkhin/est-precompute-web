<!doctype html>

<head>

<title>EFI - Precomputed Enzyme Similarity Tool</title>
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="css/efi_tool.css">
<link rel="stylesheet" type="text/css" href="css/precompute.css">
<link rel="stylesheet" type="text/css" href="includes/chosen/chosen.css">
<link rel="shortcut icon" href="images/favicon_efi.ico" type="image/x-icon">
<script src='includes/main.inc.js' type='text/javascript'></script>
<script src='includes/chosen/chosen.jquery.min.js' type='text/javascript'></script>
<script src='//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js'></script>
<link rel='stylesheet'  href='//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css'>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-57264526-1', 'auto');
ga('send', 'pageview');

</script>


<script type='text/javascript'>

$( document ).ready(function() {
    $(".chosen-select").chosen(
        {
            allow_single_deselect: true,
                width: "20%"
        }
    );
    $('.family_table').DataTable({
        "searching": true,
            "paging":   true,
            "ordering": true,
            "info":     true,
            "iDisplayLength": 5,
            "aLengthMenu": [[5,10,50, 100, -1], [5,10, 50, 100, "All"]],

    } );
    $('#bob').DataTable({

        "searching": true,
            "paging":   true,
            "ordering": true,
            "info":     true,
            "iDisplayLength": -1,
            "aLengthMenu": [[5,10,50, 100, -1], [5,10, 50, 100, "All"]],

    } );



    $('.family_div').show();
    //        $(".chosen-select").toggle();   
    //     alert("toggled");
});

</script>
</head>

<body>

<!--<h1>Sequnce Similarity Networks Tool</h1>-->
<!--<h1>EFI - Enzyme Similarity Tool</h1>-->
<div id="container">

<div class="header_area">
<div class="efi_logo">
<a href="index.php">
<img src="images/efi-est-precompute-logo.png"  alt="Enzyme Function Initiative Logo"></a><a href="http://enzymefunction.org">
<img src="images/efi_logo.png" class="efi_logo_small" width="132" height="40" alt="Enzyme Function Initiative Logo"></a><div class="clear"></div></div>

<div class="clear"></div>
<div class="public_topnav">
<ul class="menu"><li class="leaf first">&nbsp;</li>
</ul>
</div>
<div class="clear"></div>
</div>

<div class="clear"></div>

</div>

<style>
#over
{
    position:absolute;
    width:100%;
    text-align: center; /*handles the horizontal centering*/
}
/*handles the vertical centering*/
.Centerer
{
    display: inline-block;
    <!--height: 100%; -->
    vertical-align: middle;
}
.Centered
{
    display: inline-block;
    vertical-align: middle;
}
</style>


<div id="over">
    <span class="Centerer">
    <a href='https://www.google.com/chrome/browser/desktop/'><img class='Centered' src="images/EFI-Precompute-Best-Viewed-In.png"></a>
    </span>
</div>



