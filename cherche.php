<?php
include (dirname(__FILE__).'/Tgb.php');
$person = str_replace('"', '&quot;', @$_REQUEST['person']);
$persark = str_replace('"', '&quot;', @$_REQUEST['persark']);
$birthfrom = @$_REQUEST['birthfrom'];
$birthto = @$_REQUEST['birthto'];
if ( $birthto === null ) $birthto = $birthfrom;
?><!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8"/>
    <title>TGB</title>
    <link href="http://obvil.paris-sorbonne.fr/corpus/theme/obvil.css" rel="stylesheet"/>
    <link href="tgb.css" rel="stylesheet"/>
  </head>
  <body>
    <?php include( dirname(__FILE__)."/header.php" ) ?>
    <h1><a href="?">Recherche dans le Catalogue TGB</a></h1>
    <form>
      <label>Auteur
        <input size="50" name="person" placeholder="auteur" title="Un ou plusieurs auteurs, séparés par des espaces" value="<?= $person ?>"/>
      </label><br/>
      <label>ou né entre
        <input size="4" name="birthfrom" value="<?= $birthfrom ?>"/>
        et
        <input size="4" name="birthto" value="<?= $birthto ?>"/>
      </label>
      <button type="submit">Envoyer</button>
    </form>
    <?php
  $i=1;
  $limit = 2000;
  if ( trim($person.$birthfrom.$birthto) ) {
    echo "\n".'<table class="sortable">
      <tr>
        <th>N°</th>
        <th>Titre</th>
        <th>Auteur(s)</th>
        <th title="Date de naissance de l’auteur principal">Naissance</th>
        <th>Éditeur</th>
        <th title="Date de publication">Publié le</th>
        <th>Description</th>
        <th>Fichier(s)</th>
      </tr>
    ';
    if ( $person) {
      $person = trim( strtr( $person, Tgb::$frtr ) );
      $person = preg_replace(
        array( '@\s+@m', "@\*@", "@\?@"),
        array( " ",      "%",    "_"),
        $person
      );
      $values = explode( " ", $person );
      foreach ($values as &$val) {
        $val = $val."%";
      }
      unset($val);
      $sql="SELECT * FROM person WHERE ".trim ( str_repeat( " OR sort LIKE ? ", count( $values ) ), "OR ")." ORDER BY docs DESC";
      $authors = Tgb::$pdo->prepare( $sql );
      $authors->execute( $values );
      $docs = Tgb::$pdo->prepare( "SELECT document.* FROM contribution, document WHERE contribution.person = ? AND contribution.document = document.id ORDER BY year " );
      $i = 1;
      while( $author = $authors->fetch() ) {
        echo "\n<tr><td>$i</td>";
        echo "\n".'<th style="text-align:left; " colspan="6">';
        echo $author['family'];
        if ($author['given']) echo ", ".$author['given'];
        if ($author['date']) echo " (".$author['date'].")";
        echo "\n</th>";
        echo "\n</tr>";
        $docs->execute( array( $author['id'] ) );
        $i = docfetch( $docs, $i );
        if ( $i >= 2000 ) break;
      }
    }
    else if ( $birthfrom && $birthto ) {
      $docs = Tgb::$pdo->prepare( "SELECT * FROM document WHERE birthyear >= ? AND birthyear <= ? ORDER BY birthyear " );
      $docs->execute( array( $birthfrom, $birthto ) );
      docfetch( $docs );
    }
    echo "\n".'</table>';
  }
  /*
  else if ( $field == "title" ) {
    $q = preg_replace(
      array( "@$@m", '@^([^%])@m' ),
      array( "%", "%"),
      $q
    );
    $values = explode( "\n", trim($q) );
    $values = explode( "\n", trim($q) );
    $sql="SELECT * FROM document WHERE ".trim ( str_repeat( " OR title LIKE ? ", count( $values ) ), "OR ");
    $docs = Tgb::$pdo->prepare( $sql );
    $docs->execute( $values );
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
  }
  */
     ?>
    <?php include( dirname(__FILE__)."/footer.php" ) ?>
  </body>
</html>
<?php
function docfetch( $docs, $i=1 ) {
  $limit = 2000;
  $files = Tgb::$pdo->prepare( "SELECT * FROM gallica WHERE document = ? " );
  while( $document = $docs->fetch() ) {
    echo "\n<tr>";
    echo "\n<td>".$i."</td>";
    echo "\n<td>".$document['title']."</td>";
    echo "\n<td>".$document['byline']."</td>";
    echo "\n<td>".$document['birthyear'].'-'.$document['deathyear']."</td>";
    echo "\n<td>".$document['publisher']."</td>";
    echo "\n<td>".$document['date']."</td>";
    echo "\n<td>".$document['description'].' '.$document['pages']."</td>";
    // todo, fichiers
    echo "\n<td>";
    $files->execute( array( $document['id'] ) );
    $first = true;
    while( $gallica = $files->fetch() ) {
      if ( $first ) $first = false;
      else echo ", ";
      echo '<a target="_blank" href="http://gallica.bnf.fr/ark:/12148/'.$gallica['ark'].'">';
      if ( $gallica['title'] ) echo $gallica['title'];
      else echo $gallica['id'];
      echo '</a>';
    }
    echo "</td>";
    echo "\n</tr>";
    if ( $i >= $limit ) {
      echo '<tr><th colspan="7">Limite de '.$limit.' enregistrements atteinte.</th></tr>';
      break;
    }
    $i++;
  }
  return $i;
}


?>
