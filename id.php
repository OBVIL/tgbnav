<?php
// header('Content-type: text/plain; charset=utf-8');
include ( dirname(__FILE__).'/Tgb.php' );

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
    <div id="chart" class="dygraph" style="width:100%; height:600px;"></div>
    <script type="text/javascript">
    g = new Dygraph(
      document.getElementById("chart"),
      [
<?php
$max = 1000;
// $q = Tgb::$pdo->prepare( "SELECT count(*) AS count FROM document WHERE id % 1000 = ?   " );
$sql = "SELECT count(*) AS count FROM document WHERE id % $max = ";
for ( $i=0; $i < $max; $i++ ) {
  // $q->execute( array( $i ) );
  $req = Tgb::$pdo->query( $sql.$i );
  echo "[".$i.", ".current($req->fetch());
  echo "],\n";
}
       ?>],
      {
        labels: [ "Modulo", "Documents" ],
        legend: "always",
        labelsSeparateLines: "true",
        ylabel: "Documents",
        y2label: "Pages",
        showRoller: true,
        series: {
          "Documents": {
            drawPoints: false,
            pointSize: 0,
            color: "rgba( 128, 0, 128, 0.2 )",
            strokeWidth: 3,
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
