# Migration de bellevuedaveyron.com (Wix) vers bellevuedaveyron.fr

Mise à jour du 2026-11-09. Objectif : que l'ancien site Wix transmette tout son référencement au
nouveau site, page par page, et que plus aucun visiteur ne s'arrête sur l'ancien.

## L'ancien site, mesuré

Site Wix **« Bellevue d'Aveyron »** (créé en 2013), plan **Premium**, domaine personnalisé
`www.bellevuedaveyron.com`, en trois langues : français (`www.`), anglais (`en.`), espagnol (`es.`).
Le compte Wix porte 16 autres sites (sauvegardes, essais), tous non publiés : rien à rediriger.

- 35 pages, dont une page 404 ;
- 45 articles de blog sous `/post/…` (2014 → 2022) ;
- 3 anciennes redirections internes (`/general-clean`, `/accueil2022`, `/copie-de-photos`).

## Étape 1 — FAITE le 2026-11-09 : les 301 chez Wix

Le site étant Premium avec un vrai domaine, Wix accepte de vraies redirections 301, posées par l'API
« SEO Redirects » (gestionnaire de redirections du tableau de bord) :

- **78 redirections 301 créées, 0 échec** : chaque page et chaque article vers sa page équivalente du
  .fr. Elles valent pour les trois langues. La table complète est dans
  [`wix/redirections-bellevuedaveyron-com.csv`](wix/redirections-bellevuedaveyron-com.csv).
- **L'accueil** (`www.bellevuedaveyron.com/`) : Wix interdit de rediriger la racine d'un site. Il porte
  donc une **canonique** vers `https://bellevuedaveyron.fr/` et une **redirection immédiate**
  (`meta refresh` à 0 seconde), que Google traite comme une redirection permanente. Ce n'est pas une
  301 du serveur : c'est la seule place que l'étape 2 rendra parfaite.
- Les 3 anciennes redirections internes sont restées en place. Elles mènent à des pages qui
  redirigent à leur tour.

Correspondances retenues : l'accueil et ses versions anglaise et espagnole → accueil ; villa, parc,
corps de ferme → `/#experience` ; contact → `/#reservation` ; tarifs → `/#tarifs` ; services →
`/#services` ; accessibilité → `/#accessibilite` ; livre d'or → `/#temoignages` ; alentours, coups de
cœur, pêche, vidéo du coin, blog → `/decouvrir.php` ; articles sur la région → `/decouvrir.php`, les
autres → accueil.

**Ne pas résilier le plan Premium Wix tant que l'étape 2 n'est pas faite** : sans plan, le domaine
.com ne pointe plus sur rien, et les 301 disparaissent avec lui.

## Étape 2 — À FAIRE AVANT de supprimer Wix : le .com chez Hostinger

But : servir le .com depuis le même hébergement que le .fr, avec le `.htaccess` du dépôt, qui porte
déjà **les mêmes redirections** (générées depuis le même CSV) **plus l'accueil en vraie 301** et un
renvoi de toute autre adresse vers l'accueil. Ces règles sont inertes aujourd'hui : elles ne jouent
que pour une requête arrivée chez Hostinger avec un nom en .com.

1. **Le courrier d'abord.** `accueil@bellevuedaveyron.com` est votre adresse de contact. Avant de
   toucher au domaine, relever chez son gestionnaire actuel ses enregistrements **MX, SPF (TXT),
   DKIM, DMARC** et les reproduire à l'identique là où le domaine sera géré. Ne modifier que les
   enregistrements du **site** (`@`, `www`, `en`, `es`). Une erreur ici coupe la réception des
   demandes de réservation.
2. Dans hPanel, ajouter `bellevuedaveyron.com` au même site que le .fr (domaine supplémentaire /
   alias, **pas** un site séparé), avec le certificat SSL, puis faire pointer `@`, `www`, `en` et
   `es` vers Hostinger.
3. Vérifier en navigation privée : `https://www.bellevuedaveyron.com/tarifs` doit répondre
   « 301 » vers `https://bellevuedaveyron.fr/#tarifs` (outil en ligne de vérification d'en-têtes
   HTTP), et l'accueil du .com doit répondre 301 vers l'accueil du .fr.
4. **Google Search Console** : ajouter la propriété `bellevuedaveyron.com` (si elle n'y est pas),
   puis **Paramètres › Changement d'adresse** vers `bellevuedaveyron.fr`. Ce n'est possible qu'avec
   la vraie 301 de l'accueil, donc après le point 3. C'est le signal le plus fort pour transférer le
   référencement. Bing Webmaster Tools a le même outil.
5. Seulement ensuite, résilier le plan Premium Wix et supprimer le site. Si le nom de domaine .com a
   été acheté chez Wix, c'est un abonnement distinct du plan : le garder, ou le transférer chez
   Hostinger, mais ne pas le laisser expirer. Garder le **nom de domaine** .com au moins deux
   ans : les liens et avis qui pointent vers lui continuent de servir.

## Côté .fr, en complément

- Rien n'est changé au fonctionnement du .fr : seules les requêtes arrivant avec un nom en .com sont
  concernées par les règles ajoutées.
- Remplacer le lien du site sur la fiche Google, Facebook, Instagram, Tourisme Aveyron, Petit Futé,
  Vivaweek, etc. : une 301 transmet, mais un lien direct vaut mieux.

## Modifier la table

1. Éditer [`wix/redirections-bellevuedaveyron-com.csv`](wix/redirections-bellevuedaveyron-com.csv).
2. `python3 tools/generer-redirections-com.py` pour régénérer le bloc du `.htaccess`.
3. Tant que le .com est chez Wix, reporter aussi le changement dans le gestionnaire de redirections
   Wix (SEO › Gestionnaire de redirections).
