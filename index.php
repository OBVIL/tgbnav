<?php
include (dirname(__FILE__).'/Tgb.php');
$tgb = new Tgb();
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>TGB</title>
    <link href="http://obvil.paris-sorbonne.fr/corpus/theme/obvil.css" rel="stylesheet">
    <link href="tgb.css" rel="stylesheet">
    <!-- D3 -->
    <script src="//d3js.org/d3.v3.min.js"></script>
  </head>
  <body>
    <?php readfile( dirname(__FILE__)."/header.php" ) ?>
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

    <?php readfile( dirname(__FILE__)."/footer.php" ) ?>
  </body>
</html>
