<?php
/*
 *TODO: lier tous les livres qui partagent les mêmes notices
 *TODO: alto2tei -> supprimer les <big>, <small>, revoir les erreurs de structures (</div> manquante), essayer de récupérer le taux OCR, la pagination, lien aux images
 *TODO: relier les livres qui partagent la même notice
 *
 **/

//ini_set('memory_limit', '-1');// réglage catastrophique pour le chargement des métas...
/*
 * quelques méthodes pour construire le corpus TGB (Gallica, BnF)
 * altoList()      dresser la liste des ALTOS livrés par la BnF ./echanges.bnf.fr/AAAA-MM-JJ/n/ALTO.zip
 * report()        valider les métadonnées livrées par Clémence Agostini pour les ALTOS listés ci-dessus
 * buildCorpus()   conversion TEI pilotée par la liste générée par altoList()
 * insertMeta()    insérer le teiHeader dans les TEI générés
 *
*/

if (php_sapi_name() == "cli") {
  Tools::doCli();
}

class Tools {
  //petits réglages utiles
  static $outDir = "out/";
  static $reportDir = "out/report/";
  //PDO utile pour l’insertion des TEI filePath
  private static $pdo;  
  private function connect($sqlFile) {
    if (!file_exists($sqlFile)) exit($sqlFile." doesn’t exist!\n");
    else {
      self::$pdo=new PDO("sqlite:".$sqlFile, "charset=UTF-8");
    }
  }

  /*
   * Dresser le TSV des ALTOS livrés par la BnF sur echanges.bnf.fr (2 champs: altoId, altoPath)
   * Passer un chemin absolu (les sources stockées sur un disque)
   * php -f Tools.php altoList bnf-src/alto
   * retourne "altoList.txt" dans self::$reportDir
   *
  */  
  static function altoList($echangeDir) {
    $timestart=microtime(true);//démarrer le chronomètre
    $handle = fopen(self::$reportDir."altoList.txt", "w+");
    foreach(glob("$echangeDir/*/*/*.zip") as $alto) {
      fwrite($handle, basename($alto, ".zip")."\t $alto\n");
    }
    fclose($handle);
    //temps d’exécution
    $timeend=microtime(true);
    $time=$timeend-$timestart;
    $page_load_time = number_format($time, 4);
    echo "exécution: ".$page_load_time." s".PHP_EOL;
  }
  
  /*
   * lister les notices livrées par la BnF
   * php -f Tools.php marcxList bnf-src/xmarc
   * retourne "marcxList.txt" dans self::$reportDir
   *
  */
  static function marcxList($marcxDir) {
    $handle = fopen(self::$reportDir."marcxList.txt", "w+");
    foreach(glob("$marcxDir/*.xml") as $marcx) {
      fwrite($handle, basename($marcx, ".xml").PHP_EOL);
    }
    fclose($handle);
  }
  
  /* extraire le mapping alto/notice marc du fichier de récolement livré par Clémence Agostini
   * php -f Tools.php mapping
   * on imprime la sortie dans out/alto-marc.tsv
   * 4 champs :
   *    ** idAlto (identifiant de l’alto)
   *    ** arkg (ark Gallica du volume)
   *    ** idCat (identifiant de la notice marcxchange)
   *    ** arkc (ark du catalogue général)
   */
  static function mapping() {
    $csvBnF = 'bnf-src/bnf-mapping.csv';
    $mapping = array(); //on charge le mapping (Gallica / Catalogue) des identifiants dans un tableau (1,7s)
    $file = fopen($csvBnF, 'r');
    $out = fopen(self::$outDir.'alto-marc.tsv', 'w');
    fgetcsv($file); //on passe la ligne des en-têtes
    while (($record = fgetcsv($file, 0, "\t")) !== FALSE) {
      $idAlto = trim($record[1]);
      $arkg = trim($record[0]);
      $idCat  = substr($record[2], -9, 8);
      $arkc = trim($record[2]);
      fwrite($out, $idAlto."\t".$arkg."\t".$idCat."\t".$arkc."\n");
    }
    fclose($file);
    fclose($out);
  }
  
  /*
   * Contrôler les métadonnées fournies par Clémence Agostini
   * Tous les ALTOS livrés sont-ils décrits dans son tableau ?
   * Tous les ALTOS décrits ont-ils une notice bibliographique ?
   * php -f Tools.php report out/report/altoList.txt
   *
  */
  static function report($altoList, $csvBnF) {
    $timestart=microtime(true);//démarrer le chronomètre
    
    // analyse de la liste des interxmarc disponibles (marcxList.txt) (dispose-t-on de toutes les notices utiles ?)
    $marcxList = array();
    $handle = fopen(self::$reportDir.'marcxList.txt', "r") or exit("marcxList.txt introuvale");
    if($handle) {
      while (($line = fgets($handle)) !== false) $marcxList[trim($line)] ='';
    }
    
    // analyse des métadonnées livrées par la BnF ($csvBnF)
    $mapping = array();// on charge le mapping (Gallica / Catalogue) des identifiants dans un tableau (1,7s)
    $csvBnF_recNB = 0;//compter les altos décrits dans $csvBnF (on passe la première ligne plus bas)
    $duplicates = $nomarcx = array();//stocker l’identifiant des doublons et l’identifiant des notices dont nous ne disposons pas.
    $file = fopen($csvBnF, 'r');
    fgetcsv($file);//on passe la ligne des en-têtes
    while (($record = fgetcsv($file, 0, "\t")) !== FALSE) {
      //$mapping[] = array("arkGal" => $record[0], "arkCat" => $record[2]);
      //$mapping[] = array("idAlto" => substr($record[0], -8, 7), "idCat" => substr($record[2], -8, 7));
      $idAlto = substr($record[0], -8, 7);
      $idCat  = substr($record[2], -9, 8);
      if(!array_key_exists($idCat, $marcxList)) $nomarcx[] = $idCat;// on teste si on dispose véritablement de la notice
      if(array_key_exists($idAlto, $mapping)) $duplicates[] = $idAlto;
      else $mapping[$idAlto] = $idCat;
      if(!empty($idAlto)) ++$csvBnF_recNB;//on ne compte pas les lignes vides
    }
    $uniqRec = count($mapping);
    //print_r($mapping);    
    fclose($file);
    
    // analyse de la liste des altos à traiter ($altoList)
    $todo = $doublon = $nothere = $nonotice = array();//stocker les id d’alto absents dans $csvBnF ($nothere) OU sans notice dans $csvBnF ($nonotice)
    $altoInCsv = $altoInList = 0;//initialiser du compteur des alto décrits dans $csvBnF et du compteur des alto à valider
    $handle = fopen("$altoList", "r") or exit("$altoList introuvable");
    if($handle) {
      while (($line = fgets($handle)) !== false) {
        //tester si les valeurs sont uniques dans la liste (des altos en doublons ?)
        $alto = explode("\t", trim($line));
        $idAlto = $alto[0];
        $pathAlto = $alto[1];
        $altoInList++;
        //stocker les doublons
        if(array_key_exists($idAlto, $todo)) $doublon[] = $idAlto;
        else $todo[$idAlto] = '';//hack merdique: on stocke en clé pour les perf
        //alto absent du tableau des métadonnées
        if(!array_key_exists($idAlto, $mapping)) {
          $nothere[] = $idAlto;
          continue;
        }
        if(empty($mapping[$idAlto])) $nonotice[] = $idAlto;//alto bien présent mais sans lien à une notice
        $altoInCsv++;
      }
      
      //impression du rapport
      echo "\n**********************\nRAPPORT **************\n**********************\n";
      print "\n*** ANALYSE DU MAPPING LIVRÉ ($csvBnF)".PHP_EOL;
      print "$csvBnF_recNB altos décrits".PHP_EOL;
      print "$uniqRec altos DISTINCTS décrits".PHP_EOL;
      print $csvBnF_recNB-$uniqRec." alto(s) répétés".PHP_EOL;
        if(!empty($duplicates)) print implode(PHP_EOL, $duplicates).PHP_EOL;
      print "\n*** ANALYSE DES NOTICES MARCXCHANGE livrées (marcxList.txt)".PHP_EOL;
      print count($marcxList)." notices disponibles".PHP_EOL;
      print count($nomarcx)." notices manquantes (notices figurant dans le mapping mais non livrées) : ".implode(", ", $nomarcx).PHP_EOL;
      print "\n*** ANALYSE DES ALTOS À TRAITER ($altoList)".PHP_EOL;
      echo "$altoInList identifiants ALTO soumis".PHP_EOL;
      echo "$altoInCsv des altos à traiter sont décrits dans $csvBnF".PHP_EOL;
      print count($nothere)." des altos à traiter sont absents de $csvBnF".PHP_EOL;
        if(!empty($nothere)) print implode(", ", $nothere).PHP_EOL;
      print count($nonotice)." des altos présent(s) dans $csvBnF ne sont pas liés à une notice".PHP_EOL;
        if(!empty($nonotice)) print implode(PHP_EOL, $nonotice).PHP_EOL;
      if(!empty($doublon)) print "altos livrés en doublons: ".implode(", ", $doublon).PHP_EOL;
      /*
      print "\n***". (count($nothere)+count($nomarcx)) ." ALTOS SANS MÉTADONNÉES ATTACHÉES".PHP_EOL;//les ALTOS qui n’ont pas de mapping (on ne peut pas retrouver la notice) et ceux dont nous n’avons pas la notice (non livrée)
      */
    }    
    fclose($handle);
    //temps d’exécution
    $timeend=microtime(true);
    $time=$timeend-$timestart;
    $page_load_time = number_format($time, 4);
    echo "\nexécution: ".$page_load_time." s".PHP_EOL;
  }
  
  /*
   * transformer en TEI les ALTOS listés dans altoList.txt, par paquets de 10 000, sans toucher au dossier des sources (echanges.bnf.fr/)
   * NB: le path des sources est spécifié dans le TSV des altos à traiter ($altoList)
   *
   */
  /*
   * TODO1: reprendre les variables pour lisibilités
   * TODO2: sortir les messages selon la réussite des opérations... + sortir les messages d’erreur!
   * TODO3: placer des bornes dans le code pour reprendre à un point précis de la séquence
   */
  static function buildCorpus($altoList) {
    //on charge toutes les métadonnées du mapping en mémoire ; pas classe mais efficace
    //on stocke le mapping, mais aussi les métadonnées au cas où on ne dispose pas de la notice marcxchange
    /*
    $mapping = array();
    $file = fopen("rsc/bnf-all.csv", 'r');
    fgetcsv($file);
    while (($record = fgetcsv($file, 0, "\t")) !== FALSE) {
      $idAlto = substr($record[0], -8, 7);
      $idCat  = substr($record[2], -9, 8);
      $mapping[$idAlto] = $idCat;
    }
    fclose($file);
    */
    $workDir = self::$outDir.'tei/';
    $c = $lot = $errline = 1; // compteurs des altos et des lots
    $reportFile = fopen('out/report/report.txt', "w"); //un fichier pour assurer le suivi
    $errLog     = fopen('out/report/errors.txt', "a"); //consigner les erreurs
    $handle     = fopen($altoList, "r") or exit("$altoList introuvable");
    while (($line = fgets($handle)) !== false) {
      /*
       * $status: le statut du TEI généré à imprimer dans $reportFile:
       *   done : TEI généré sans erreur
       *   wf   : "wrong formedness" (TEI généré non conforme XML)
       *   err  : "error" (se référer à $errLog)
       */
      $alto = explode("\t", trim($line));
      $altoId = trim($alto[0]);
      $altoPath = trim($alto[1]);
      $status = trim($alto[2]);
      //on traite ou non, selon le statut inscrit dans $altoList
      if(!empty($status)) {
        echo "$c > $altoId déjà traité".PHP_EOL;
        fwrite($reportFile, $line); // on reporte le status dans $reportFile
        ++$c; //incrémenter compteur alto avant skip
        continue;
      }
      /***********START DEBUG*************/
      //echo self::insertMeta($altoId).PHP_EOL;
      //continue;
      /***********END DEBUG*************/
      //création d’un nouveau dossier de sortie tous les 10000 altos (01/, 02/, 03/...)
      if($c % 10000 == 0) $lot++;
      $outDir = $workDir.$lot."/";
      //traitement des ALTOS
      if(!file_exists($outDir)) mkdir($outDir, 0777, true);
      if(preg_match('/[0-9]{7}/', $altoId)) echo "$c > Traitement de l’ALTO $altoId.zip".PHP_EOL;
      else {
        echo "ERREUR > ALTO id $altoId mal formé".PHP_EOL;
        fwrite($reportFile, trim($line)."\terr\n");
        fwrite($errLog, "ALTOID: $altoId ($altoList, l. $errline)\n");
        continue;
      }
      //par sécurité, copier la source echanges.bnf.fr dans le dossier de sortie
      $altoZip = $outDir.$altoId.".zip";
      if(!copy($altoPath, $altoZip)) {
        echo "ERREUR > La copie $altoPath du fichier a échoué".PHP_EOL;
        fwrite($reportFile, trim($line)."\terr\n");
        fwrite($errLog, "CP: $altoPath ($altoList, l. $errline)\n");
        continue;
      }
      echo "\tcopie de $altoPath dans $outDir".PHP_EOL;
      //dézipper
      echo "\textraction de $altoId.zip dans $outDir".PHP_EOL;
      $zip = new ZipArchive;
      $res = $zip->open($altoZip);//stocker le code erreur en cas d’échec
      if ($res === TRUE) {
        echo "\textraction de $altoZip".PHP_EOL;
        $zip->extractTo($outDir);
        $zip->close();
      } else {
        echo "ERREUR > unzip $altoId, code: $res".PHP_EOL;
        fwrite($reportFile, trim($line)."\terr\n");
        fwrite($errLog, "ZIP: $res, ($altoList, l. $errline)\n");
        continue;
      }
      //supprimer le zip
      if(!unlink($altoZip)) {
        echo "ERREUR > La suppression de $altoZip a échoué".PHP_EOL;
        fwrite($errLog, "UNLINK: $altoZip\n");
        //poursuivre tout de même la conversion
      } else echo "\tsuppression de $altoZip".PHP_EOL;
      //alto2tei
      if(!is_null(shell_exec("php -f alto/Alto.php ".$outDir.$altoId."/ ".$outDir))) echo "\tconversion TEI de $altoId/ > $altoId.xml".PHP_EOL;
      else fwrite($errLog, "ALTO2TEI: $altoId\n");
      //checker si le XML produit est valide
      libxml_use_internal_errors(true);
      if (simplexml_load_file($outDir.$altoId.'.xml')) $status = 'done';
      else $status = 'wf';
      //supprimer la source alto
      if(!is_null(shell_exec("rm -r $outDir$altoId/"))) echo "suppression de l’alto $altoId";//honteux mais efficace...
      //consigner dans $altoList que le status
      fwrite($reportFile, trim($line)."\t$status\n");
      ++$c;//alto suivant
    }
    fclose($handle);
    fclose($reportFile);
    fclose($errLog);
  }
  
  // pour éviter de charger en mémoire à chaque itération les données, on lance le traitement sur le lot ?
  static function insertHeader() {
    $teiDir = 'out/tei/';
    $headerDir = 'out/header/';
    $errfile = fopen(self::$reportDir.'badteiList.txt', 'w+');
    //charger le mapping disponible en mémoire
    $mapping = array();
    $file = fopen('out/alto-marc.tsv', 'r');
    while(($record = fgetcsv($file, 0, "\t")) !== FALSE) {
      $altoId = trim($record[0]);
      $noticeId = trim($record[2]);
      $mapping[$altoId] = $noticeId;
    }
    //print_r($mapping);
    fclose($file);
    //méthode XML conforme (on insère le teiHeader seulement si le fichier est bien formé)
    foreach(glob("$teiDir/*/*.xml") as $teiFile) {
      echo $teiFile;
      $altoId = basename($teiFile,'.xml');
      $headerFile = $headerDir.$mapping[$altoId].".xml";
      $bodyDOM = new DOMDocument;
      //si la conversion TEI est valide on insère le header ('@' pour ne pas sortir les erreurs)
      if(@$bodyDOM->load($teiFile)) {
        $bodyDOM->formatOutput = true;//formatage du doc dans lequel on fait l’insertion
        print "traitement de $teiFile -> insertion de $headerFile".PHP_EOL;
        // http://php.net/manual/fr/domdocument.importnode.php
        $headerDOM = new DOMDocument;
        if(!$headerDOM->load($headerFile)) fwrite($errfile, $teiFile."\n");
        else {
          $headerDOM->load($headerFile);
          $header = $headerDOM->getElementsByTagName('teiHeader')->item(0);// le noeud que je veux importer renvoie un DOMElement
          $header = $bodyDOM->importNode($header, true);
          //on contextualise
          $xpath = new DOMXPath($bodyDOM);//on va s’insérer dans ce DOM avec xpath
          $xpath->registerNamespace('tei', 'http://www.tei-c.org/ns/1.0');
          $text = $xpath->query('//tei:text');//on va insérer le teiHeader juste avant le noeud text
          $bodyDOM->documentElement->insertBefore($header, $text->item(0));
          if($bodyDOM->save($teiFile)) print "insertion de $headerFile dans $teiFile\n";
          else print "echec de l’insertion\n";
        }
      }
      else {
        print "$teiFile mal formé".PHP_EOL;
        fwrite($errfile, $teiFile."\n");//produire le rapport d’erreur dans le dossier de rapport
      }
    }
    fclose($errfile);
  }
  
  //méthode jetable pour réordonner le corpus TEI
  static function sortCorpus() {
    $teiDir  = "out/tei/";
    $destDir = "/Volumes/gallica/tgb/out/tei/";
    $all = array(); // stocker tous les path des TEI
    //$duplicates = array();// on ne sait jamais...
    foreach(glob("$teiDir*/*.xml") as $tei) {
      $id = basename($tei, '.xml');
      /*
      if(array_key_exists($id, $all)) $duplicates[] = $id;
      else $all[$id] = $tei;
      */
      $all[$id] = $tei;
    }
    ksort($all);//on trie les tei par identifiant
    //print_r($all);
    $c = $lot = 1;//création du dossier (un dossier tous les 5000)
    foreach($all as $id => $oldpath) {
      if($c % 5000 == 0) $lot++;
      $outDir = "$destDir/$lot/";
      if(!file_exists($outDir)) mkdir($outDir, 0777, true);
      //echo "$id => $oldpath\n";
      copy($oldpath, "$outDir/$id.xml");
      $c++;
    }
    //print_r($duplicates);
    //echo count($all).PHP_EOL;
  }
  
  /*
   * php -f Tools.php sqlInsertTeiPath out/tei/
   * inscrire le path des fichiers TEI dans la base SQLite
   * prototype assez sale... TODO: reprendre
   */
  static function sqlInsertTeiPath($teiDir) {
    self::connect('out/sqlite/tgb.sqlite');
    foreach(glob($teiDir.'*/*.xml') as $tei) {
      $bookid = basename($tei, '.xml');
      $sql = "UPDATE book SET fpath = '$tei' WHERE bookid = $bookid";
      $query = self::$pdo->prepare($sql);
      $count = $query->execute();
    }
  }
  
  
  public static function doCli() {
    array_shift($_SERVER['argv']);//shift arg 1, the script filepath
    if (!count($_SERVER['argv'])) exit("usage : php -f Tools.php (altoList | report | buildCorpus | controlBnF)\n");
    $method=null;
    $args=array();
    while ($arg=array_shift($_SERVER['argv'])) {
      if ($arg=="buildCorpus" || $arg=="altoList" || $arg=="marcxList" || $arg== "controlBnF" || $arg=="report" || $arg=="sortCorpus" || $arg=="insertHeader" || $arg=="mapping" || $arg=="sqlInsertTeiPath") $method=$arg;
      else $args[]=$arg;
    }
    switch ($method) {
      //construire le corpus (lots de 10 000, à partir des dossiers livrés par la BnF, cf buildCorpus.sh)
      case "buildCorpus":
        Tools::buildCorpus($args[0]);
        break;
      //construire la liste plate des alto livrés à partir des livraisons de la BnF
      case "altoList":
        Tools::altoList($args[0]);
        break;
      //construire la liste plate notices marcxchange livrées par la BnF
      case "marcxList":
        Tools::marcxList($args[0]);
        break;
      //valider le fichier de métadonnées de la BnF : enregistrements uniques ? tous les champs obligatoires sont-ils renseignés ?
      case "controlBnF":
        Tools::controlBnF();
        break;
      case "mapping":
        Tools::mapping();
        break;
      case "insertHeader":
        Tools::insertHeader();
        break;
      case "sortCorpus":
        Tools::sortCorpus();
        break;
      case "sqlInsertTeiPath":
        Tools::sqlInsertTeiPath($args[0]);
        break;
      //rapport d’erreur: s’assurer que tous les alto sont bien décrit dans le tableau de Agostini (au moins une correspondance vers un id de notice)
      case "report":
        Tools::report($args[0], 'bnf-src/bnf-mapping.csv');
    }
  }
  
}


?>