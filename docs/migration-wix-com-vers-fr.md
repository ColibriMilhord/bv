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

Le nom de domaine `bellevuedaveyron.com` est **chez OVH** (propriétaire, 2026-11-09).

1. **Savoir qui gère la zone DNS.** Espace client OVH › Noms de domaine › `bellevuedaveyron.com` ›
   onglet **Serveurs DNS** :
   - des serveurs `dns…ovh.net` / `ns…ovh.net` : la zone est chez OVH, passer au point 2 ;
   - des serveurs Wix (`…wixdns.net`) : la zone, **courrier compris**, est chez Wix. Avant tout,
     relever dans Wix (Domaines › Gérer les enregistrements DNS) **tous** les enregistrements, les
     recréer à l'identique dans la zone OVH, puis seulement remettre les serveurs DNS d'OVH.
     Sinon, la suppression de Wix emporte la messagerie.
2. **Relever la zone avant de la toucher.** Onglet **Zone DNS** : faire une capture d'écran
   complète. Les enregistrements **MX**, **SPF** (TXT `v=spf1…`), **DKIM** et **DMARC** font vivre
   `accueil@bellevuedaveyron.com` : **on n'y touche pas**.
3. **Côté Hostinger** : rattacher `bellevuedaveyron.com`, `www`, `en` et `es` au **même site** que le
   .fr (domaine parqué ou alias, pas un site séparé), avec un certificat SSL qui couvre ces noms.
   hPanel donne l'adresse IP (enregistrement A) ou la cible (CNAME) à utiliser.
4. **Côté OVH, zone DNS** : modifier uniquement les enregistrements du site, qui pointent
   aujourd'hui vers Wix : `@` (A), `www`, `en`, `es` (CNAME ou A), avec les valeurs données par
   Hostinger. Supprimer l'éventuel second A de Wix sur `@`. Compter quelques heures de propagation.
5. Vérifier en navigation privée : `https://www.bellevuedaveyron.com/tarifs` doit répondre
   « 301 » vers `https://bellevuedaveyron.fr/#tarifs` (outil en ligne de vérification d'en-têtes
   HTTP), et l'accueil du .com doit répondre 301 vers l'accueil du .fr.
6. **Google Search Console** : ajouter la propriété `bellevuedaveyron.com` (si elle n'y est pas),
   puis **Paramètres › Changement d'adresse** vers `bellevuedaveyron.fr`. Ce n'est possible qu'avec
   la vraie 301 de l'accueil, donc après le point 5. C'est le signal le plus fort pour transférer le
   référencement. Bing Webmaster Tools a le même outil.
7. Seulement ensuite, résilier le plan Premium Wix et supprimer le site. Garder le nom de domaine
   chez OVH, renouvellement automatique activé : s'il expire, le référencement transmis s'arrête.
   Le garder au moins deux ans : les liens et les avis qui pointent vers lui continuent de servir.

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
