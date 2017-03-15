<?php
// lien à la base

error_reporting(E_ALL);
ini_set("display_errors", 1);
set_time_limit(-1);
Tgb::init();
class Tgb {
  /** Table de caractères pour mise à l’ASCII, chargée depuis frtr.php */
  static $frtr;
  static $conf;
  public static $pdo;

  public static function init()
  {
    self::$conf = include( dirname( __FILE__ )."/conf.php" );
    self::connect( self::$conf['sqlite'] );
  }

  public static function connect( $file ) {
    self::$frtr = include( dirname( __FILE__ )."/lib/frtr.php" ); // crée une variable $frtr
    if (!file_exists( $file )) exit( $file." doesn’t exist!\n");
    else {
      self::$pdo = new PDO("sqlite:".$file, "charset=UTF-8");
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING ); // get error as classical PHP warn
      self::$pdo->exec("PRAGMA temp_store = 2;"); // store temp table in memory (efficiency)
    }
  }


  /* nombre de livres par grandes catégories Dewey, en JSON */
  public function deweyCat2json() {
    self::connect(self::$tgb_sqlite);
    $sql="SELECT parent, label, count(book) FROM about, dewey
      WHERE parent IS NOT NULL
      AND about.dewey = dewey.code
      GROUP BY parent";
    $query = self::$pdo->prepare($sql);
    $query->execute();
    $cats = $query->fetchAll();
    $catsCount = count($cats);//compteur merdique pour assurer la syntaxe JSON
    $i = 0;
    echo '<script>var deweyAll = \'{"name": "tgb", "children": [';
    foreach($cats as $cat) {
      $i++;
      echo '{"name": "' . $cat['label'] . '", "size": ' . $cat['count(book)'] . "}";
      if ($i != $catsCount) echo ', ';
    }
    echo ']}\';</script>';
  }

  /*
   * sortir les valeurs pour tout dewey, classées par catégorie
   * code foutraque, tout revoir (preuve de concept OK)
   * */
  public function dewey2json() {
    self::connect(self::$tgb_sqlite);
    //la liste des catégories avec leur label et compte si catégorie vide
    $sql="SELECT parent, label, count(book) FROM about, dewey WHERE about.parent = dewey.code GROUP BY parent;";
    /*
    $sql="SELECT dewey, parent, label, count(book) FROM about, dewey
      WHERE dewey IS NOT NULL
      AND about.dewey = dewey.code
      GROUP BY dewey";
      */
    $query = self::$pdo->prepare($sql);
    $query->execute();
    $result = $query->fetchAll();
    $deweyCatCount = count($result);
    $n=0;
    //racine de la var JSON
    echo '<script>var deweyAll = \'{"name": "tgb", "children": [';
    //les catégories
    foreach ($result as $cat) {
      $n++;
      //la catégorie
      echo '{ "name": "'.str_replace("'", "’", $cat['label']).'", "children": [';
      $sql2="SELECT dewey, label, count(book) FROM about, dewey
      WHERE dewey IS NOT NULL
      AND about.parent = " . $cat['parent'] . "
      AND about.dewey = dewey.code
      GROUP BY dewey";
      $query2 = self::$pdo->prepare($sql2);
      $query2->execute();
      $result2 = $query2->fetchAll();
      //echo $cat['parent'] . ' = '. $cat['label'] ."\n";
      $deweyCount = count($result2);
      $i=0;
      //si résultats vides (pas d’enfant dewey) on renvoie le résultat de la catégorie parente (cas de '000' généralités)
      //TODO: tout reprendre ici in algo (test sur $result2 if/else)
      if (empty($result2)) echo '{"name": "'.$cat['label'].'", "size": '.$cat['count(book)'].'}';
      //TODO renommer en-dessous $cat pour éviter les confusions parent/enfant
      //les codes par catégorie
      foreach($result2 as $cat) {
        $i++;
        echo '{"name": "' . str_replace("'", "’", $cat['label']) . '", "size": ' . $cat['count(book)'] . "}";
        if ($i != $deweyCount) echo ', ';
      }
      echo ']} ';//test pour virer la dernière virgule
      if ($n != $deweyCatCount) echo', ';
    }
    echo ']}\';</script>';
    /*
    $cats = array_unique(array_map(function ($ar) {return $ar['parent'];}, $result));
    foreach ($cats as $cat) {
      echo $cat;
    }
    deweyCats = array_column($result, 'parent')); //à partir de PHP 5.5: http://stackoverflow.com/questions/7994497/how-to-get-an-array-of-specific-key-in-multidimensional-array-without-looping!
    */
  }


}
?>
