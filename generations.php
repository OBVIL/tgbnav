<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Tgb.php' );
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1600;
if ( $from < 1452 ) $from = 1452;
if ( $from > 2014 ) $from = 2000;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 1880;
if ( $to < 1475 ) $to = 2014;
if ( $to > 2014 ) $to = 2014;

if ( isset($_REQUEST['smooth']) ) $smooth = $_REQUEST['smooth'];
else $smooth = 1;
if ( $smooth < 0 ) $smooth = 0;
if ( $smooth > 50 ) $smooth = 50;

if ( isset($_REQUEST['pagefloor']) ) $pagefloor = $_REQUEST['pagefloor'];
else $pagefloor = 100;

$log = NULL;
if ( isset($_REQUEST['log']) ) $log = $_REQUEST['log'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>TGB</title>
    <script src="lib/dygraph-combined.js">//</script>
    <link href="http://obvil.paris-sorbonne.fr/corpus/theme/obvil.css" rel="stylesheet"/>
    <link href="tgb.css" rel="stylesheet"/>
  </head>
  <body>
    <?php include( dirname(__FILE__)."/header.php" ) ?>
    <header style="min-height: 2.7em; ">
À la date de naissance de l’auteur principal, nombre de documents
      <form name="dates">
        De <input name="from" size="4" value="<?php echo $from ?>"/>
        à <input name="to" size="4" value="<?php echo  $to ?>"/>
        <label>Lissage <input name="smooth" size="1" value="<?php echo  $smooth ?>"/></label>
        <button type="submit">▶</button>
      </form>
    </header>
    <div id="chart" class="dygraph" style="width:100%; height:600px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php

$qbook = Tgb::$pdo->prepare( "SELECT count(*) AS count, sum(pages) AS pages FROM document WHERE birthyear = ?" );
$qpers = Tgb::$pdo->prepare( "SELECT count(*) AS count FROM person WHERE birthyear = ?" );

$lastpages = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $qbook->execute( array( $date ) );
  $row = $qbook->fetch( );
  echo "[".$date.", ".$row['count'];
  // if ( $row['count'] ) echo ", ".number_format( $row['pages']/$row['count'], 2, '.', '');
  // echo ", ".$row['pages'];
  echo "],\n";
}
       ?>],
      {
        labels: [ "Année de naissance", "Documents" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Documents",
        y2label: "Pages",
        showRoller: true,
        <?php if ($log) echo "logscale: true,";  ?>
        rollPeriod: <?php echo $smooth ?>,
        series: {
          "Documents": {
            drawPoints: false,
            pointSize: 0,
            color: "rgba( 128, 0, 128, 0.2 )",
            strokeWidth: 3,
          },
          "Pages": {
            axis: 'y2',
            color: "rgba( 0, 0, 0, 1)",
            strokeWidth: 1,
          },
        },
        axes: {
          x: {
            gridLineWidth: 1,
            gridLineColor: "rgba( 0, 0, 0, 0.2)",
            drawGrid: true,
            independentTicks: true,
          },
          y: {
            independentTicks: true,
            drawGrid: true,
            axisLabelColor: "rgba( 255, 0, 0, 0.9)",
            gridLineColor: "rgba( 255, 0, 0, 0.2)",
            gridLineWidth: 1,
          },
          y2: {
            independentTicks: true,
            drawGrid: true,
            gridLinePattern: [6,3],
            gridLineColor: "rgba( 0, 0, 0, 0.2)",
            gridLineWidth: 1,
          },
        }
      }
    );
    g.ready(function() {
      g.setAnnotations([
        { series: "Documents", x: "1606", shortText: "Corneille", width: "", height: "", cssClass: "ann", },
        // { series: "Documents", x: "1621", shortText: "La Fontaine", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1622", shortText: "Molière", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1636", shortText: "Boileau", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1639", shortText: "Racine", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1694", shortText: "Voltaire", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1712", shortText: "Rousseau", width: "", height: "", cssClass: "ann", },
        // { series: "Documents", x: "1713", shortText: "Diderot", width: "", height: "", cssClass: "ann", },
        // { series: "Documents", x: "1727", shortText: "Lhomond", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1737", shortText: "Bernardin de Saint-Pierre", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1746", shortText: "Genlis", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1755", shortText: "Florian", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1768", shortText: "Chateaubriand", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1783", shortText: "Stendhal", width: "", height: "", cssClass: "ann", },
        // { series: "Documents", x: "1799", shortText: "Balzac", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1802", shortText: "Hugo", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1811", shortText: "Gautier", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1829", shortText: "Ponson du Terail", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1840", shortText: "Zola", width: "", height: "", cssClass: "ann", },
        { series: "Documents", x: "1871", shortText: "Proust", width: "", height: "", cssClass: "ann", },
      ]);
    });
    var linear = document.getElementById("linear");
    var log = document.getElementById("log");
    var setLog = function(val) {
      g.updateOptions({ logscale: val });
      linear.disabled = !val;
      log.disabled = val;
    };
    linear.onclick = function() { setLog(false); };
    log.onclick = function() { setLog(true); };
    </script>
  <?php include ( dirname(__FILE__).'/footer.php' ) ?>
  </body>
</html>
