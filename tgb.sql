PRAGMA encoding = 'UTF-8';

-- base SQLite des notices InterXMarc des titres Gallica
CREATE TABLE book (
  bookid      INTEGER UNIQUE,           -- ! TODO NOT NULL alto and TEI filename, 937$j (chaîne numérique précédent le caractère de contrôle de l’ark Gallica)
  notice      INTEGER NOT NULL,  -- ! id de la notice marcxchange bnf (pour seule référence) ; plusieurs alto peuvent pointer vers une même notice.
  arkg        TEXT,                     -- ! TODO UNIQUE NOT NULL ark gallica (937$j)
  arkc        TEXT,                     -- ! TODO UNIQUE NOT NULL ark catalogue général (srw:recordIdentifier)
  title       TEXT,                     -- ! 245$a TODO ajouter NOT NULL
  issued      INTEGER,                  -- ? date de publication (première occurrence de [0-9]{4} de 260$d)
  creation    INTEGER,                  -- ? siècle de "création" du texte inféré
  publisher   TEXT,                     -- ? premier éditeur listé (260$c)
  fpath       TEXT             -- ! le chemin vers le fichier XML/TEI TODO NOT NULL
);
CREATE UNIQUE INDEX book_bookid ON book(bookid);
CREATE UNIQUE INDEX book_sort ON book(date, title);
CREATE INDEX book_title ON book(title);
CREATE INDEX book_notice ON book(notice);

CREATE TABLE author (
  nna         INTEGER UNIQUE NOT NULL,  -- ! id notice autorité bnf (100$3) ; TODO: un cas non renseigné dans les notices ; placer une valeur par défaut dans la génération de la table
  arkn        TEXT UNIQUE,              -- ! ark notice autorité (extraction de la table nna/ark)
  name        TEXT NOT NULL,            -- ! nom (de famille pour les personnes: 100$a, 700$a SI 700$4='0070'), (de la collectivité: 110$a, 710$a SI 710$4='0070')
  firstname   TEXT,                     -- ? prénom (uniquement pour les personnes: 100$m, 700$m SI 700$4='0070')
  heading     TEXT NOT NULL             -- ! clé normalisée, "100$a, 100$m (100$d ; 100$e)" ex: Leroy, Charles (18..-19.. ; professeur de rhétorique)
);
CREATE INDEX author_name       ON author(name);
CREATE INDEX author_firstname  ON author(firstname);
CREATE INDEX author_heading   ON author(heading);
CREATE INDEX author_nna   ON author(nna);

CREATE TABLE writes (
  author      INTEGER NOT NULL REFERENCES author(nna), -- ! lien auteur (utiliser nna ?)
  book        INTEGER REFERENCES book(notice),            -- ! lien livre (utiliser bookid ?)
  UNIQUE(author, book) ON CONFLICT REPLACE
);
CREATE INDEX writes_author ON writes(author);
CREATE INDEX writes_book ON writes(book);

CREATE TABLE dewey (
  -- TODO gérer le champ 'parent' (cat dewey -- 1er chiffre du code) pour filtrer les résultats
  code        TEXT UNIQUE NOT NULL,     -- ! code dewey sur 3 chiffres (conserver les préfixes'0') (680$a)
  label       TEXT NOT NULL             -- ! libellé du code
);
CREATE UNIQUE INDEX dewey_code ON dewey(code);
CREATE INDEX dewey_label ON dewey(label);
--CREATE INDEX dewey_parent ON dewey(parent);

CREATE TABLE about (
  -- relation book/dewey
  book        INTEGER NOT NULL REFERENCES book(notice),   -- ? about a book
  dewey       TEXT NOT NULL REFERENCES dewey(code), -- ! lien dewey (type TEXT pour conserver code sur 3 chiffres (cas de préfixes '0'))
  parent      TEXT, -- ? catégorie dewey (code centaine [000, 100, 200, etc.], seulement si code bien renseigné, càd construit sur 3 chiffres)
  plabel      TEXT  -- ? parentLabel: le label du code parent, pour faciliter les traitements (requêtes de visualisation du corpus)
);
CREATE INDEX about_dewey ON about(dewey);
CREATE INDEX about_book ON about(book);
