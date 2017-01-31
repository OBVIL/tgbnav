<?php
include (dirname(__FILE__).'/Tgb.php');
$q = "";
if ( isset($_REQUEST['q']) ) $q = $_REQUEST['q'];
$field = "";
if ( isset($_REQUEST['field']) ) $field = $_REQUEST['field'];
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>TGB</title>
    <link href="http://obvil.paris-sorbonne.fr/corpus/theme/obvil.css" rel="stylesheet">
    <link href="tgb.css" rel="stylesheet">
  </head>
  <body>
    <?php readfile( dirname(__FILE__)."/header.php" ) ?>
    <h1><a href="?">Recherche dans le Catalogue TGB</a></h1>
    <form>
      <label>Champ de recherche
        <select name="field">
          <option value="author">Auteur</name>
          <option value="title"<?php if( $field == "title" ) echo ' selected="selected"'; ?>>Titre</name>
          <option value="dewey"<?php if( $field == "dewey" ) echo ' selected="selected"'; ?>>Dewey</name>
        </select>
      </label>
      <br/>
      <textarea name="q" rows="5" cols="50" placeholder="Une valeur par ligne"><?= $q ?></textarea>
      <br/>
      <button type="submit">Envoyer</button>
    </form>
    <?php
  $q = trim($q);
  $q = preg_replace(
    array( '@^\s+@m', '@\s+$@m', '@^.{0,3}$@m',  "@[\n\r]+@", '@^[\%\*]+@m', "@\*@", "@\?@", "@%$@m"  ),
    array( "",        "",        "",             "\n",         "",           "%",    "_" ,   "",     ),
    $q
  );
  $i=1;
  $limit = 2000;
  if ( !trim($q) );
  else if ( $field == "title" ) {
    $q = preg_replace(
      array( "@$@m", '@^([^%])@m' ),
      array( "%", "%"),
      $q
    );
    $values = explode( "\n", trim($q) );
    echo "\n".'<table class="sortable">
      <tr><th>N°</th><th/><th>Titre</th><th>Date</th><th>Éditeur</th><th>Fichier</th></tr>
    ';
    $values = explode( "\n", trim($q) );
    $sql="SELECT * FROM book WHERE ".trim ( str_repeat( " OR title LIKE ? ", count( $values ) ), "OR ");
    $books = Tgb::$pdo->prepare( $sql );
    $books->execute( $values );
    while( $book = $books->fetch() ) {
      echo "\n<tr>";
      echo "\n<td>".$i."</td>";
      echo "\n<td/>";
      echo "\n<td>".$book['title']."</td>";
      echo "\n<td>".$book['issued']."</td>";
      echo "\n<td>".$book['publisher']."</td>";
      echo "\n<td>".$book['fpath']."</td>";
      echo "\n</tr>";
      $i++;
      if ( $i >= $limit ) {
        echo '<tr><th colspan="6">Limite de '.$limit.' enregistrements atteinte.</th></tr>';
        break;
      }
    }
    echo "\n".'</table>';
  }
  else if ( $field == "dewey" ) {
    echo "\n".'<table class="sortable">
      <tr><th>N°</th><th>Dewey</th><th>Titre</th><th>Date</th><th>Éditeur</th><th>Fichier</th></tr>
    ';
    $q = preg_replace(
      array( "@$@m", '@^([^%])@m' ),
      array( "%", "%"),
      $q
    );
    $values = explode( "\n", trim($q) );
    $sql="SELECT * FROM dewey WHERE ".trim ( str_repeat( " OR label LIKE ? ", count( $values ) ), "OR ");
    $cats = Tgb::$pdo->prepare( $sql );
    $cats->execute( $values );
    $count = Tgb::$pdo->prepare( "SELECT count() FROM about WHERE about.dewey = ? " );
    $books = Tgb::$pdo->prepare( "SELECT book.* FROM about, book WHERE about.dewey = ? AND about.book = book.bookid " );
    while( $cat = $cats->fetch() ) {
      echo "\n<tr><td/>";
      echo "\n<tr><td>".$cat['code']."</td>";
      echo "\n".'<th style="text-align:left; " colspan="4">';
      echo $cat['label'];
      echo "\n</th>";
      echo "<th>";
      $count->execute( array( $cat['code'] ) );
      echo current($count->fetch());
      echo "</th>";
      echo "\n</tr>";
      if ( $i >= $limit ) continue;
      $books->execute( array( $cat['code'] ) );
      while( $book = $books->fetch() ) {
        echo "\n<tr>";
        echo "\n<td>".$i."</td>";
        echo "\n<td>".$cat['code']."</td>";
        echo "\n<td>".$book['title']."</td>";
        echo "\n<td>".$book['issued']."</td>";
        echo "\n<td>".$book['publisher']."</td>";
        echo "\n<td>".$book['fpath']."</td>";
        echo "\n</tr>";
        $i++;
        if ( $i >= $limit ) {
          echo '<tr><th colspan="6">Limite de '.$limit.' enregistrements atteinte.</th></tr>';
          break;
        }
      }
    }
    echo "\n".'</table>';
  }
  else {
    $q = preg_replace(
      array( "@$@m"),
      array( "%"),
      $q
    );
    $values = explode( "\n", trim($q) );
    echo "\n".'<table class="sortable">
      <tr><th>N°</th><th>Auteur</th><th>Titre</th><th>Date</th><th>Éditeur</th><th>Fichier</th></tr>
    ';
    $sql="SELECT * FROM author WHERE ".trim ( str_repeat( " OR heading LIKE ? ", count( $values ) ), "OR ");
    $authors = Tgb::$pdo->prepare( $sql );
    $authors->execute( $values );
    $books = Tgb::$pdo->prepare( "SELECT book.* FROM writes, book WHERE writes.author = ? AND writes.book = book.bookid ORDER BY issued " );
    // $books = Tgb::$pdo->prepare( "SELECT * FROM writes WHERE writes.author = ?  " );


    while( $author = $authors->fetch() ) {
      echo "\n<tr><td/>";
      echo "\n".'<th style="text-align:left; " colspan="5">';
      echo $author['heading'];
      echo "\n</th>";
      echo "\n</tr>";
      $books->execute( array( $author['nna'] ) );
      while( $book = $books->fetch() ) {
        echo "\n<tr>";
        echo "\n<td>".$i."</td>";
        echo "\n<td>".$author['name']."</td>";
        echo "\n<td>".$book['title']."</td>";
        echo "\n<td>".$book['issued']."</td>";
        echo "\n<td>".$book['publisher']."</td>";
        echo "\n<td>".$book['fpath']."</td>";
        echo "\n</tr>";
        $i++;
        if ( $i >= $limit ) {
          echo '<tr><th colspan="6">Limite de '.$limit.' enregistrements atteinte.</th></tr>';
          break;
        }
      }
      if ( $i >= $limit ) break;
    }
    echo "\n".'</table>';
  }

     ?>
    <?php readfile( dirname(__FILE__)."/footer.php" ) ?>
  </body>
</html>
