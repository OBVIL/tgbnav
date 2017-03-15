<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Tgb.php' );
if (isset($_REQUEST['from'])) $from = $_REQUEST['from'];
else $from = 1780;
if ( $from < 1452 ) $from = 1452;
if ( $from > 2014 ) $from = 2000;
if (isset($_REQUEST['to'])) $to = $_REQUEST['to'];
else $to = 1900;
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

$qbook = Tgb::$pdo->prepare( "SELECT count(*) AS count, avg(pages) AS pages FROM document WHERE year = ?" );

$lastpages = 0;
for ( $date=$from; $date <= $to; $date++ ) {
  $qbook->execute( array( $date ) );
  $row = $qbook->fetch( );
  echo "[".$date.", ".$row['count'];
  echo ", ".number_format( $row['pages'], 2, '.', '');
  echo "],\n";
}
       ?>],
      {
        labels: [ "Année", "Documents", "Moy. pages" ],
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
          "Moy. pages": {
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
        { series: "Moy. pages", x: "1648", shortText: "La Fronde", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1789", shortText: "1789", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1793", shortText: "1793", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1815", shortText: "1815", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1830", shortText: "1830", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1848", shortText: "1848", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1870", shortText: "1870", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1914", shortText: "1914", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1939", shortText: "1939", width: "", height: "", cssClass: "ann", },
        { series: "Moy. pages", x: "1968", shortText: "1968", width: "", height: "", cssClass: "ann", },
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
