<?php
//ini_set("display_errors",0);
error_reporting(E_ALL);
include (dirname(__FILE__).'/Tgb.php');
$tgb = new Tgb();
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>TGB</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="Présentation du corpus TGB (OBVIL/BnF)">
    <!-- Bootstrap core CSS -->
    <link href="theme/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="theme/bootstrap/starter-template/starter-template.css" rel="stylesheet">
    <!-- ?? faire quoi de ce zobi? -->
    <script src="theme/bootstrap/assets/js/ie-emulation-modes-warning.js"></script>
    <!-- D3 -->
    <link rel="stylesheet" type="text/css" href="viz/Treemap.css"/>
    <link rel="stylesheet" type="text/css" href="viz/Bubble.css"/>
    <link rel="stylesheet" type="text/css" href="viz/Bar.css"/>
    <script src="//d3js.org/d3.v3.min.js"></script>
  </head>
  <body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">TGB</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="#presentation">Présentation</a></li>
            <li><a href="#dewey">Thématiques</a></li>
            <li><a href="#authors">Auteurs</a></li>
            <li><a href="#licence">Licence</a></li>
            <li><a href="#credits">Crédits</a></li>
            <li><a href="#src">Mémo</a></li>
            <!---
            <li><a href="#done">Done</a></li>
            <li><a href="#todo">Todo</a></li>
            -->
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
    <div class="container">
      <h1>TGB (<a href="http://www.bnf.fr/fr/acc/x.accueil.html" target="_blank">BnF</a> – <a href="http://obvil.paris-sorbonne.fr/" target="_blank">OBVIL</a>)</h1>
      <p style="color:red">Ce site est provisoire, ne retenez pas cette adresse dans vos favoris.</p>
      <p class="lead">La TGB est une bibliothèque de <b>128 441 documents</b> en mode texte (reconnaissance automatique des caractères non relue), issus des collections <a href="http://gallica.bnf.fr/" target="_blank">Gallica</a> de la BnF, en cours de mise à disposition pour la recherche.
        <br/>Le corpus est en français, issu majoritairement de l’édition du XIX<sup>e</sup> siècle. Les imprimés de cette période
        sont à la fois libres de droits (contrairement aux XX<sup>e</sup> et XXI<sup>e</sup> s.), et mieux reconnus automatiquement que les imprimés plus anciens (s long ſ, ligatures…).
        <br/>Les <a href="http://gallica.bnf.fr/html/und/conditions-dutilisation-des-contenus-de-gallica" target="_blank">conditions d’utilisation des contenus</a> sont celles de Gallica ; réutilisation non-commerciale autorisée des textes pris séparément (loi n° 78-753 du 17 juillet 1978) ; l’ensemble n'est pas réutilisable librement, c’est une propriété publique régie par le droit des bases de données (L341-1) ; les contrevenants s’exposent aux poursuites prévues par la loi.</p>
      <div class="starter-template" id="presentation">
        <h2>La bibliothèque</h2>
        <ul>
          <li><b>Textes</b> — 128 441 (44,16 Go, XML/TEI)</li>
          <li><b>Auteurs</b> — 58 287</li>
          <li><b>Dates</b> — Les documents sont pour la grande majorité parus  au XIX<sup>e</sup> s. (XVII<sup>e</sup> : 24,  XVIII<sup>e</sup> : 7294, XIX<sup>e</sup> : 95479 documents, XX<sup>e</sup> : 54), beaucoup de documents sont des rééditions de textes plus anciens.</li>
            <!-- Comment est-ce possible d’avoir 4840 documents du XXe pour seulement 54 documents publiés au XXe ?
            <li>
            <br/>Nous pouvons inférer un siècle grâce aux dates de mort des auteurs pour 59 907 documents (47% du corpus).
            <br/>Le XIX<sup>e</sup> siècle est encore surreprésenté&nbsp;:
            <ul>
              <li>XX<sup>e</sup> s : 4840 documents</li>
              <li>XIX<sup>e</sup> s : 45429</li>
              <li>XVIII<sup>e</sup> s : 7052</li>
              <li>XVII<sup>e</sup> s : 1758</li>
              <li>XVI<sup>e</sup> s : 313</li>
              <li>XV<sup>e</sup> s : 51</li>
              <li>XIV<sup>e</sup> s : 114</li>
              <li>avant : 362</li>
            </ul>
          </li>
          -->
          <li><b>Référentiels et liens</b> — Tous les documents sont liés à leur notice dans le <a href="http://catalogue.bnf.fr/index.do" target="_blank">catalogue général</a> de la BnF et pointent vers leur instance Gallica.
            <br/>Les auteurs sont liés à leur <a href="http://catalogue.bnf.fr/recherche-auteurs.do?pageRech=rau" target="_blank">notice de personne</a> du catalogue général.</li>
          <li><b>Qualité du texte</b> — Le texte est issu d’un traitement OCR brut (sans relecture).
           <br/>La qualité (taux OCR) dépend donc de l’état de la source, de la langue, mais aussi de la campagne de numérisation.
           <br/>Certains textes ont cependant un taux avancé d’OCR de 100%.</li>
          <li><b>Structuration du texte</b> – très aléatoire et fautive, elle a été inférée autant que possible de l'information contenue dans les fichiers source ALTO.</li>
          <li>Les thématiques répertoriées sont nombreuses.</li>
        </ul>
      </div>
      <div class="starter-template" id="dewey">
        <h2>Thématiques</h2>
        <p>127 267 documents de la TGB (99% du corpus) ont été indexés par les catalogueurs de la BnF, selon la <a href="http://www.bnf.fr/fr/professionnels/anx_catalogage_indexation/a.referentiels_sujet.html#SHDC__Attribute_BlocArticle6BnF" target="_blank">classification Dewey</a>.
          <br/>On peut donc assez aisément constituer des corpus thématiques. Les 10 classes principales sont inégalement représentées.
          <ul>
            <li>Littérature (Belles-lettres) : 35710 documents</li>
            <li>Histoire de la France (depuis 486) : 28885</li>
            <li>Droit : 23776</li>
            <li>Economie domestique. Vie à la maison : 19622</li>
            <li>Les arts : 5653</li>
            <li>Astronomie et sciences connexes : 4307</li>
            <li>Journalisme, édition. Journaux : 3824</li>
            <li>Religion : 2576</li>
            <li>Langues romanes. Français : 1861</li>
            <li>Philosophie et disciplines connexes : 1491</li>
          </ul>
          <br/>La division (10 pour chaque classe) est précisément spécifiée mais caractérise aussi des pratiques de catalogage qui ont pu évoluer au fil des décennies.</p>
        <?php //$tgb->deweyCat2json(); ?><!-- sortir la source JSON -->
        <?php $tgb->dewey2json(); ?><!-- sortir la source JSON -->
        <script src="viz/Bubble.js"></script>
        <!--<script src="./code/viz/D3js/Treemap.js"></script>-->
        <p>L’<b>analyse lexical des titres</b> est sans doute une piste à suivre pour l’exploration thématique des documents.</p>
        <img alt="lexique des titres" src="viz/titres.png" style="max-width:800px;"/>
      </div>
      <div class="starter-template" id="authors"><!-- id indispensable pour l’ancrage des visualisations D3 -->
        <h2>Auteurs</h2>
        <p>Les 58 287 auteurs distincts se répartissent classiquement selon la loi de Zipf.
        <br/>38 665 auteurs sont associés à un unique document, 1649 à plus de 10 documents et seulement 28 à plus de 100.</p>
        <img alt="top 30 auteurs" src="viz/top-auteurs.png" style="max-width:800px;"/>
      </div>
      <div>
        <!-- Impression de la liste des livres -->
        <h2>Catalogue (exemple)</h2>
        <style type="text/css">td{padding:0 8px 0 0px;}</style>
        <?php $tgb->sqlTableSample() ?>
      </div>
      <div id="licence">
        <!-- Licence -->
        <h2>Licence</h2>
        <p>Tous droits réservés à la BnF et au labex OBVIL (enrichissement des données).</p>
        <p>La réutilisation des documents de la base est libre pour tout usage non commercial.<br/>
        La base dans son ensemble (les réutilisations d'une part substantielle du contenu de cette base) est protégée et toute copie ou diffusion interdite sauf autorisation spécifique.</p>
        <p>La licence retenue est celle de Gallica : <a href="http://gallica.bnf.fr/html/und/conditions-dutilisation-des-contenus-de-gallica" target="_blank">http://gallica.bnf.fr/html/und/conditions-dutilisation-des-contenus-de-gallica</a>.<br/>
        Un lien vers la licence d’utilisation est inscrit dans chaque fichier TEI.</p>
        <div style="border:solid 1px #ccc;border-radius:4px;background-color:#F5F5F5;padding:5px;width:90%;">
          <p>1/ Les contenus accessibles sur le site Gallica sont pour la plupart des reproductions numériques d'oeuvres tombées dans le domaine public provenant des collections de la BnF.<br>
        Leur réutilisation s'inscrit dans le cadre de la loi n°78-753 du 17 juillet 1978 :<br/>
        &nbsp;- La réutilisation non commerciale de ces contenus est libre et gratuite dans le respect de la législation en vigueur et notamment du maintien de la mention de source.<br>
        &nbsp;- La réutilisation commerciale de ces contenus est payante et fait l'objet d'une licence. Est entendue par réutilisation commerciale la revente de contenus sous forme de produits élaborés ou de fourniture de service. Cliquer ici <a href="http://www.bnf.fr/fr/collections_et_services/reproductions_document/a.repro_reutilisation_documents.html" target="_blank">pour accéder aux tarifs et à la licence</a></p>
          <p>2/ Quelques contenus sont soumis à un régime de réutilisation particulier. Il s'agit :<br/>
        &nbsp;- des reproductions de documents protégés par un droit d'auteur appartenant à un tiers. Ces documents ne peuvent être réutilisés, sauf dans le cadre de la copie privée, sans l'autorisation préalable du titulaire des droits.<br>
        &nbsp;- des reproductions de documents conservés dans les bibliothèques ou autres institutions partenaires. Ceux-ci sont signalés par la mention Source gallica.bnf.fr / Bibliothèque municipale de ... (ou autre partenaire). L'utilisateur est invité à s'informer auprès de ces bibliothèques de leurs conditions de réutilisation.</p>
          <p>3/ Gallica constitue une base de données, dont la BnF est producteur, protégée au sens des articles L341-1 et suivants du code de la propriété intellectuelle.</p>
          <p>4/ Les présentes conditions d'utilisation des contenus de Gallica sont régies par la loi française. En cas de réutilisation prévue dans un autre pays, il appartient à chaque utilisateur de vérifier la conformité de son projet avec le droit de ce pays.</p>
          <p>5/ L'utilisateur s'engage à respecter les présentes conditions d'utilisation ainsi que la législation en vigueur. En cas de non respect de ces dispositions, il est notamment passible d'une amende prévue par la loi du 17 juillet 1978.</p>
          <p>6/ Pour obtenir la reproduction d'un document de Gallica en haute définition, contacter <a href="mailto:reproduction@bnf.fr">reproduction@bnf.fr</a></p>
          <p>7/ Pour utiliser un document de Gallica sur un support de publication commercial, contacter <a href="mailto:utilisation.commerciale@bnf.fr">utilisation.commerciale@bnf.fr</a></p>
        </div>
      </div>
      <div class="starter-template" id="credits">
        <h2>Crédits</h2>
        <p>Le partenariat du labex OBVIL et de la BnF a permis la constitution de cette base de données. Emmanuelle Bermès (Adjointe pour les questions scientifiques et techniques auprès du Directeur des services et des réseaux, BnF) et Didier Alexandre (Professeur de littérature française, Université Paris-Sorbonne, directeur du labex OBVIL) en ont assuré la conception, Vincent Jolivet (ingénieur d’études, labex OBVIL) la réalisation.</p>
      </div>
      <div class="starter-template" id="src">
        <h2>Sources BnF</h2>
        <ul>
          <li>114 776 notices bibliographiques InterXMarc (954 Mo) ;</li>
          <li>131 015 textes au format alto (222,13 Go, en 27 livraisons) ;
            <ul>
              <li><b>128 441</b> (98%) altos convertis en XML/TEI, avec les métadonnées inscrites dans le fichier</li>
              <li>2 297 documents sans texte (généralement des illustrés)</li>
              <li>185 fichiers altos vides</li>
              <li>72 textes sans métadonnées (cf. plus bas)</li>
              <li>3 fichiers altos corrompus</li>
            </ul>
          </li>
          <li>un fichier de récolement entre textes et notices
            <ul>
              <li>130 943 textes sont liés à une notice</li>
              <li>114 notices manquent (mentionnées dans le récolement mais pas livrées)</li>
              <li>72 textes livrés ne sont pas décrits dans le récolement</li>
              <li>3 doublons dans le récolement</li>
            </ul>
          </li>
        </ul>
      </div>
    </div><!-- /.container -->



    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="theme/bootstrap/assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="theme/bootstrap/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="theme/bootstrap/assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
