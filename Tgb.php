<?php
// lien à la base

error_reporting(E_ALL);
ini_set("display_errors", 1);
set_time_limit(-1);
//if (php_sapi_name() == "cli") Tgb::doCli();
Tgb::connect();
class Tgb {
  private static $tgb_sqlite = "data/tgb.sqlite";
  public static $pdo;
  private $reader; //analyseur XML (XMLReader)

  function __construct($path="") {
    $this->reader = new XMLReader();
  }

  public static function connect() {
    $file = self::$tgb_sqlite;
    if (!file_exists( $file )) exit( $file." doesn’t exist!\n");
    else {
      self::$pdo = new PDO("sqlite:".$file, "charset=UTF-8");
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING ); // get error as classical PHP warn
      self::$pdo->exec("PRAGMA temp_store = 2;"); // store temp table in memory (efficiency)
    }
  }

  /* nombre de livres par siècle de "création" */
  public function creation2json() {
    self::connect(self::$tgb_sqlite);
    $sql="SELECT creation, count(bookid) FROM book GROUP BY creation";
    $query = self::$pdo->prepare($sql);
    $query->execute();
    $dates = $query->fetchAll();
    print_r($dates);
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

  public function sqlTableSample() {
    self::connect(self::$tgb_sqlite);
    //$sql="SELECT heading, title, issued, fpath, arkg FROM author, book, writes WHERE book.bookid = writes.book AND author.nna = writes.author AND fpath!='' ORDER BY heading LIMIT 50";
    $sql="SELECT heading, title, issued, fpath, arkg FROM author, book, writes WHERE book.bookid = writes.book AND author.nna = writes.author AND fpath!='' AND author.nna = '11898585' ORDER BY heading";
    $sql="SELECT heading, title, issued, fpath, arkg FROM author, book, writes WHERE title LIKE '%littérature%' AND book.bookid = writes.book AND author.nna = writes.author";
    $query = self::$pdo->prepare($sql);
    $query->execute();
    $result = $query->fetchAll();
    //initialisation de la table
    $table = '
      <table style="width:90%">
        <tr>
          <th>Titre</th>
          <th>Auteur</th>
          <th>Date</th>
        </tr>';
    foreach($result as $rec) {
      $link = '<a href="'.$rec['fpath'].'" target="_blank">'.$rec['title'].'</a>';
      $gallica = '<a href="http://gallica.bnf.fr/'.$rec['arkg'].'" target="_blank">'.$rec['title'].'</a>';
      $table .= '
        <tr>
          <td>'.$gallica.'</td>
          <td>'.$rec['heading'].'</td>
          <td>'.$rec['issued'].'</td>
        </tr>
      ';
    }
    $table .= '</table>';
    print $table;
  }

}
?>
