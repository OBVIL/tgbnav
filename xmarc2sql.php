<?php
Xmarc2sql::connect( "tgb2.sqlite" );
Xmarc2sql::glob();

class Xmarc2sql {
  /** Table de prénoms (pour reconnaître le sexe), chargée depuis given.php */
  static $given;
  /** Table de caractères pour mise à l’ASCII, chargée depuis frtr.php */
  static $frtr;
    /** connection */
  static private $_pdo;
  /** où sommes nous ? */
  static private $_marc;
  /** texte */
  static private $_text;
  /** parseur */
  static private $_parser;
  /** colonnes */
  static private $_cols = array(
    "document" => array(
      "ark",
      "title",
      "date",
      "year",
      "place",
      "publisher",
      "dewey",
      "lang",
      "type",
      "description",
      "pages",
      "size",
      "byline",
      "bysort",
      "birthyear",
      "deathyear",
      "posthum",
      "id",
    ),
    "contribution" => array(
      "document",
      "person",
      "role",
      "writes",
    ),
    "person" => array(
      "family",
      "given",
      "sort",
      "gender",
      "date",
      "birthyear",
      "deathyear",
      "id",
    ),
    "gallica" => array(
      "ark",
      "title",
      "id",
    )
  );
  /** queries */
  static private $_q;
  /** enregistrements */
  static private $_rec;

  static function glob()
  {
    include(dirname(__FILE__).'/lib/given.php');
    self::$given = $given;
    include( dirname( __FILE__ )."/lib/frtr.php" ); // crée une variable $frtr
    self::$frtr = $frtr;
    self::$_parser = xml_parser_create();
    // use case-folding so we are sure to find the tag in $map_array
    xml_parser_set_option( self::$_parser, XML_OPTION_CASE_FOLDING, false );
    xml_set_element_handler( self::$_parser, "Xmarc2sql::_open", "Xmarc2sql::_close" );
    xml_set_character_data_handler( self::$_parser, "Xmarc2sql::_text" );
    self::$_q['document'] = self::$_pdo->prepare(
      "INSERT INTO document (".implode(", ", self::$_cols['document'] ).") VALUES (".rtrim(str_repeat("?, ", count( self::$_cols['document'] )), ", ").");"
    );
    self::$_q['title'] = self::$_pdo->prepare(
      "INSERT INTO title ( docid, text ) VALUES ( ?, ? )");
/*
<mxc:datafield tag="700" ind1=" " ind2=" ">
  <mxc:subfield code="3">11900134</mxc:subfield>
  <mxc:subfield code="w">0  b.....</mxc:subfield>
  <mxc:subfield code="a">Diderot</mxc:subfield>
  <mxc:subfield code="m">Denis</mxc:subfield>
  <mxc:subfield code="d">1713-1784</mxc:subfield>
  <mxc:subfield code="4">0360</mxc:subfield>
</mxc:datafield>
*/
    self::$_q['contribution'] = self::$_pdo->prepare(
      "INSERT INTO contribution (".implode(", ", self::$_cols['contribution'] ).") VALUES (".rtrim(str_repeat("?, ", count( self::$_cols['contribution'] )), ", ").");"
    );
    self::$_q['person'] = self::$_pdo->prepare(
      "INSERT INTO person (".implode(", ", self::$_cols['person'] ).") VALUES (".rtrim(str_repeat("?, ", count( self::$_cols['person'] )), ", ").");"
    );
    self::$_q['gallica'] = self::$_pdo->prepare(
      "INSERT INTO gallica (".implode(", ", self::$_cols['gallica'] ).") VALUES (".rtrim(str_repeat("?, ", count( self::$_cols['gallica'] )), ", ").");"
    );
    self::$_q['perstest'] = self::$_pdo->prepare( "SELECT * FROM person WHERE id = ?" );
    self::$_pdo->beginTransaction();
    foreach ( glob( "xmarc/*.xml" ) as $file ) {
      self::_file( $file );
      break;
    }
    self::$_pdo->commit();
  }

  private static function _file( $file )
  {
    $fp = fopen( $file, "r" );
    while ( $data = fread( $fp, 1000000 ) ) {
      if ( !xml_parse( self::$_parser, $data, feof($fp)) ) {
        die( sprintf(
          "XML error: %s at line %d",
          xml_error_string( xml_get_error_code( self::$_parser ) ),
          xml_get_current_line_number($xml_parser))
        );
      }
    }
  }

  private static function _open( $parser, $name, $atts )
  {
    self::$_text = "";
    if ( $name == "mxc:record" ) {
      self::$_rec['document'] = array_fill_keys ( self::$_cols['document'], null );
      // id="ark:/12148/cb30089185p"
      $ark = substr( $atts['id'], 11);
      self::$_rec['document']['ark'] = $ark;
      self::$_rec['document']['id'] = substr($ark, 2, -1);
    }
    if ( $name == "mxc:datafield" ) {
      self::$_marc = array();
      self::$_marc[0] = $atts['tag'];
      if ( self::$_marc[0] == 937 ) self::$_rec['gallica'] =  array_fill_keys ( self::$_cols['gallica'], null );
      if ( self::$_marc[0] == 100 || self::$_marc[0] == 700 ) {
        self::$_rec['person'] =  array_fill_keys ( self::$_cols['person'], null );
        self::$_rec['contribution'] =  array_fill_keys ( self::$_cols['contribution'], null );
        self::$_rec['contribution']['document'] = self::$_rec['document']['id'];

      }
    }
    else if ( $name == "mxc:subfield" ) {
      self::$_marc[1] = $atts['code'];
    }
  }

  private static function _close( $parser, $name )
  {
    if ( $name == 'mxc:subfield' ) {
      if ( self::$_marc == array( 937, "j" ) ) {
        // ark:/12148/bpt6k5655164n
        preg_match( '@bpt6k(.......)@', self::$_text, $matches);
        self::$_rec['gallica']['id'] = $matches[1];
      }
      if ( self::$_marc == array( 245, "a" ) ) self::$_rec['document']['title'] = self::$_text;
      if ( self::$_marc == array( 245, "d" ) ) self::$_rec['document']['type'] = self::$_text;
      if ( self::$_marc == array( 260, "a" ) ) self::$_rec['document']['place'] = self::$_text;
      if ( self::$_marc == array( 260, "c" ) ) self::$_rec['document']['publisher'] = self::$_text;
      if ( self::$_marc == array( 260, "d" ) ) {
        self::$_rec['document']['date'] = self::$_text;
        preg_match( '@(\d{1,4})@', self::$_text, $matches);
        self::$_rec['document']['date'] = $matches[0];
      }
      if ( self::$_marc == array( 280, "a" ) ) {
        self::$_rec['document']['description'] = self::$_text;
        preg_match_all( '/([0-9]+)(-[0-9IVXLC]+)? [pf]\./', self::$_text, $matches, PREG_PATTERN_ORDER );
        if ( count($matches[1]) > 0 ) {
          self::$_rec['document']['pages'] = 0;
          foreach( $matches[1] as $p ) self::$_rec['document']['pages'] += $p;
        }
        else if ( preg_match( "/ pièce /u", self::$_text ) ) {
          self::$_rec['document']['pages'] = 1;
        }
      }
      if ( self::$_marc == array( 680, "a" ) ) self::$_rec['document']['dewey'] = self::$_text;
      if ( self::$_marc == array( 41, "a" ) ) self::$_rec['document']['lang'] = self::$_text;

      if ( self::$_marc == array( 100, "3" ) || self::$_marc == array( 700, "3" ) ) {
        self::$_rec['person']['id'] = self::$_text;
        self::$_rec['contribution']['person'] = self::$_text;
      }
      if ( self::$_marc == array( 100, "4" ) || self::$_marc == array( 700, "'3'" ) ) {
        $role = 0+self::$_text;
        self::$_rec['contribution']['role'] = $role;
        if ( $role == 70 || $role == 71 || $role == 72 || $role == 73 || $role == 980 || $role == 990 ) self::$_rec['contribution']['writes'] = 1;
      }
      if ( self::$_marc == array( 100, "a" ) || self::$_marc == array( 700, "a" ) ) self::$_rec['person']['family'] = self::$_text;
      if ( self::$_marc == array( 100, "m" ) || self::$_marc == array( 700, "m" ) ) {

        self::$_rec['person']['given'] = self::$_text;
      }
    }
    if ( $name == 'mxc:datafield' ) {
      if ( self::$_marc[0] == 937 ) {
        self::$_rec['gallica']['id'] = 0+substr($cote, 5, -1);
        self::$_q['gallica']->execute( array_values( self::$_rec['gallica'] ) );
      }
      if ( self::$_marc[0] == 100 || self::$_marc[0] == 700 ) {
        // auteur principal ?
        if ( self::$_marc[0] == 100 && !self::$_rec['document']['byline'] ) {
          self::$_rec['document']['birthyear'] = self::$_rec['person']['birthyear'];
          self::$_rec['document']['deathyear'] = self::$_rec['person']['deathyear'];
        }
        // ajouter à la ligne auteur
        if ( self::$_rec['document']['byline'] ) self::$_rec['document']['byline'] .= " ; ";
        self::$_rec['document']['byline'] .= self::$_rec['person']['family'];
        if ( self::$_rec['person']['given'] ) self::$_rec['document']['byline'] .= ", ".self::$_rec['person']['given'];
        if ( self::$_rec['person']['given'] ) {
          $key = mb_strtolower( self::$_rec['person']['given'] );
          $key = reset( preg_split('@[ -]+@', $key) );
          if ( isset( self::$given[$key] ) ) self::$_rec['person']['gender'] = self::$given[$key];
        }
        self::$_rec['person']['sort'] = strtr( self::$_rec['person']['family'].self::$_rec['person']['given'], self::$frtr );
        // record
        self::$_q['perstest']->execute( array( self::$_rec['person']['id'] ) );
        if ( !self::$_q['perstest']->fetch() ) {
          self::$_q['person']->execute( array_values( self::$_rec['person'] ) );
        }
        self::$_q['contribution']->execute( array_values( self::$_rec['contribution'] ) );
      }
    }
    if ( $name == 'mxc:record' ) {
      self::$_rec['document']['bysort'] =  strtr( self::$_rec['document']['byline'], self::$frtr );
      self::$_q['document']->execute( array_values( self::$_rec['document'] ) );
      self::$_q['title']->execute( array( self::$_rec['document']['id'], self::$_rec['document']['title'] ) );
    }
  }

  private static function _text( $parser, $data )
  {
    self::$_text .= $data;
  }

  /**
   * Connexion à la base de données
   */
  static function connect( $sqlfile, $create=false )
  {
    $dsn = "sqlite:" . $sqlfile;
    if($create && file_exists($sqlfile) ) unlink( $sqlfile );
    // create database
    if (!file_exists($sqlfile)) { // if base do no exists, create it
      if (!file_exists($dir = dirname($sqlfile))) {
        mkdir($dir, 0775, true);
        @chmod($dir, 0775);  // let @, if www-data is not owner but allowed to write
      }
      self::$_pdo = new PDO($dsn);
      self::$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      @chmod($sqlfile, 0775);
      self::$_pdo->exec( file_get_contents( dirname(__FILE__)."/marc.sql" ) );
      return;
    }
    else {
      // echo getcwd() . '  ' . $dsn . "\n";
      // absolute path needed ?
      self::$_pdo = new PDO($dsn);
      self::$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }
  }
}

?>
