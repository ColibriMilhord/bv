# Mise en production — bellevuedaveyron.fr

Marche à suivre pour publier la version issue de la branche
`claude/dev-scan-seo-ia-nwugpt` sur l'hébergement Hostinger.

Comptez une quinzaine de minutes. **Suivez l'ordre des étapes** : l'étape 1
conditionne le fonctionnement du site.

---

## ⚠ À lire avant de commencer

Les mots de passe ont été sortis du code. `config/db.php` et
`config/mail_config.php` ne contiennent plus aucun identifiant : ils les lisent
désormais dans un fichier `config/secrets.php`, qui n'existe pas encore sur le
serveur et qui n'est volontairement pas dans le dépôt.

**Si vous téléversez les fichiers sans avoir créé `config/secrets.php` au
préalable, le site perdra l'accès à la base de données et à l'envoi de mails.**
D'où l'ordre : secrets d'abord, fichiers ensuite.

---

## Étape 1 — Créer `config/secrets.php` sur le serveur

Dans le gestionnaire de fichiers Hostinger (hPanel → Fichiers → Gestionnaire de
fichiers), ouvrir le dossier `config/` du site, créer un fichier nommé
**`secrets.php`** et y coller ceci, en remplaçant les valeurs :

```php
<?php
return [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'u424962071_rbellevue',
    'DB_USER' => 'u424962071_rbellevue',
    'DB_PASS' => 'le_mot_de_passe_de_la_base',

    'SMTP_HOST'      => 'smtp.hostinger.com',
    'SMTP_PORT'      => 465,
    'SMTP_USER'      => 'reservation@bellevuedaveyron.fr',
    'SMTP_PASS'      => 'le_mot_de_passe_de_la_boite',
    'SMTP_FROM'      => 'reservation@bellevuedaveyron.fr',
    'SMTP_FROM_NAME' => "Bellevue d'Aveyron",
];
```

> **Le piège de l'apostrophe.** Une valeur contenant une apostrophe doit être
> entre **guillemets doubles**, faute de quoi PHP s'arrête et le site renvoie
> une erreur 500 muette :
>
> ```php
> 'SMTP_FROM_NAME' => 'Bellevue d'Aveyron',   // ← casse tout
> 'SMTP_FROM_NAME' => "Bellevue d'Aveyron",   // ← correct
> ```
>
> Vérifier aussi qu'une **virgule** termine chaque ligne. Un oubli fait
> apparaître l'erreur sur la ligne *suivante*, ce qui égare la recherche.

Deux clés facultatives peuvent s'ajouter à cette liste pour synchroniser les
avis Google (note, compteur, trois derniers avis) — voir
`docs/seo-ia/avis-google.md`. Sans elles, le site affiche la sélection d'avis
de `config/avis-secours.php`.

Les valeurs actuelles se retrouvent, si besoin, dans les anciens fichiers
encore en ligne : `config/db.php` (variable `$password`) et
`config/mail_config.php` (constante `SMTP_PASS`). **Lisez-les avant de
téléverser quoi que ce soit**, puisque le téléversement va les remplacer.

> **Changez ces deux mots de passe.** Ils figuraient en clair dans le code, et
> restent lisibles dans l'historique du dépôt comme dans toute copie locale de
> celui-ci. Après la suspension de la boîte d'envoi par Hostinger, c'est une
> précaution qui referme d'un coup toutes les hypothèses d'accès direct.
> Changez-les dans hPanel (base de données, puis Emails → Comptes e-mail), et
> saisissez ici les nouvelles valeurs. Le détail de l'incident et la marche à
> suivre complète figurent dans `docs/securite-mail.md`.

---

## Étape 2 — Sauvegarder l'existant

Toujours dans le gestionnaire de fichiers Hostinger : sélectionner le dossier
du site, **Compresser** en `.zip`, puis télécharger l'archive sur votre
ordinateur. C'est votre filet de sécurité, à conserver quelques semaines.

Sauvegarder également la base : hPanel → Bases de données → phpMyAdmin →
onglet **Exporter** → Exécuter.

---

## Étape 3 — Récupérer les fichiers à publier

Sur GitHub, sélectionner la branche **`claude/dev-scan-seo-ia-nwugpt`**, puis
bouton vert **« Code » → « Download ZIP »**. Décompresser sur votre ordinateur.

---

## Étape 4 — Téléverser

Copier le contenu du dossier décompressé vers la racine du site sur le serveur
(`public_html/` ou équivalent), en écrasant les fichiers existants.

**Quatre points de vigilance :**

1. **Les fichiers commençant par un point.** `config/.htaccess` et
   `ARCHIVE/.htaccess` protègent des dossiers sensibles. La plupart des clients
   FTP les masquent par défaut : dans FileZilla, menu **Serveur → Forcer
   l'affichage des fichiers cachés**. Sans eux, la protection n'est pas posée.

2. **Ne pas écraser `config/secrets.php`.** Le fichier créé à l'étape 1 n'est
   pas dans l'archive : il doit rester tel quel.

3. **Téléverser le dossier `js/vendor/`.** Il contient la bibliothèque de la
   carte du monde de l'écran Audience, désormais servie par le site et non
   plus par un réseau de diffusion externe. Sans lui, la carte ne s'affiche
   pas — le reste de l'écran fonctionne.

4. **Créer le dossier `cache/`** à la racine du site s'il n'existe pas, et le
   laisser accessible en écriture (permissions 755). Il sert au cache de
   l'agenda. En cas d'impossibilité, le site fonctionne quand même, simplement
   sans cache.

5. **Les trois outils de diagnostic** (`diagnostic.php`, `debug-500.php`,
   `diagnostic-avis.php`) sont désormais protégés : ils exigent une session
   d'administrateur, ou la clé indiquée en clair au début de chaque fichier
   (`?cle=bellevue-diag`, `?cle=bellevue-debug`, `?cle=bellevue-avis`). Les
   laisser sur le serveur ne présente plus de risque ; les supprimer reste
   possible.

6. **Trois fichiers sont à supprimer** du serveur, s'ils y sont encore :
   `check_db.php`, `fetch_datatourisme.php` (scripts de debug qui exposaient le
   schéma de la base et la clé Datatourisme) et `bellevue_debug_mail.log` (il
   contient des adresses e-mail de clients, à la racine web).

Les dossiers `docs/` et `tools/` peuvent être téléversés ou non : ils ne
servent qu'à la documentation et à la maintenance, et `robots.txt` interdit
déjà leur indexation.

---

## Étape 5 — Vérifier

Dans l'ordre, en notant tout ce qui cloche :

| À ouvrir | Attendu |
|---|---|
| `https://bellevuedaveyron.fr/` | Page d'accueil complète, **tarifs et calendrier affichés** (preuve que la base répond) |
| `https://bellevuedaveyron.fr/decouvrir.php` | Page Découvrir, cartes et agenda de proximité affichés |
| `https://bellevuedaveyron.fr/robots.txt` | Le fichier s'affiche en texte |
| `https://bellevuedaveyron.fr/sitemap.xml` | Le fichier s'affiche |
| `https://bellevuedaveyron.fr/llms.txt` | Le fichier s'affiche |
| `https://bellevuedaveyron.fr/config/db.php` | **Erreur 403** — si le fichier se télécharge, le `.htaccess` n'est pas monté |
| `https://bellevuedaveyron.fr/.git/HEAD` | **Erreur 403 ou 404** — si le fichier s'affiche, tout l'historique du code est public |
| `https://bellevuedaveyron.fr/docs/securite-mail.md` | **Erreur 403** |
| `https://bellevuedaveyron.fr/admin/` | Page de connexion, puis tableau de bord |
| Administration → **Audience du site** | Les tuiles s'affichent et **la carte du monde apparaît**, la France colorée. Si le message « la carte n'a pas pu s'afficher » s'affiche, c'est que le dossier `js/vendor/` n'a pas été téléversé |
| Administration → **Annonces du site** | Le formulaire s'affiche, sans bandeau rouge |
| Administration → **Paramètres du Gîte** | Le champ « Destinataires des demandes du formulaire » est présent, sans bandeau orange |
| Administration → Paramètres du Gîte → **« Envoyer un message d'essai »** | Chaque destinataire passe au vert. C'est le contrôle le plus direct de la chaîne d'envoi : aucun filtre anti-robots, et la réponse du serveur est affichée telle quelle |
| Administration → **Audience du site** → « Demandes reçues par le formulaire » | Le bloc s'affiche, à zéro tant qu'aucune demande n'est passée |
| Administration → **Audience du site** → « Campagnes publicitaires » | Le bloc s'affiche ; sans campagne en cours, il explique comment étiqueter un lien d'annonce (voir `docs/campagnes-publicitaires.md`) |
| Section **Réserver votre séjour** | Le calendrier s'affiche, les semaines déjà louées apparaissent grisées et barrées. Cliquez une arrivée puis un départ : le récapitulatif se remplit et l'intitulé du bouton devient « Demander cette période » |
| Formulaire de réservation du site | **Faire un envoi de test**. Connectez-vous d'abord à l'administration dans le même navigateur : le propriétaire connecté échappe au délai minimal et au quota de trois demandes par heure, et peut donc enchaîner ses essais |

Si les tarifs n'apparaissent pas ou si l'administration répond « Service
temporairement indisponible » : `config/secrets.php` est absent, mal nommé, ou
une valeur est erronée. C'est la cause dans la quasi-totalité des cas.

---

## Étape 6 — Après la mise en ligne

1. **Google Search Console** (<https://search.google.com/search-console>) :
   ajouter la propriété `bellevuedaveyron.fr`, puis soumettre
   `https://bellevuedaveyron.fr/sitemap.xml`. Faire de même sur **Bing
   Webmaster Tools**, qui alimente Copilot et une partie de ChatGPT.
2. **Redirections 301** vers `https://bellevuedaveyron.fr` depuis `www` et,
   si le domaine `.com` sert encore le site, depuis celui-ci également
   (hPanel → Domaines → Redirections).
3. **Valider les distances** de la page Découvrir avec un calculateur
   d'itinéraire, et me signaler les écarts : elles alimentent aussi le balisage
   et `llms.txt`.
4. **Authentifier le domaine pour l'e-mail** : SPF, DKIM et DMARC, dans
   hPanel → Domaines → Zone DNS. C'est ce qui décide, chez Gmail et Outlook,
   du sort de vos messages, et ce qui rétablit le plus vite la réputation du
   domaine après une suspension. Marche à suivre dans
   `docs/securite-mail.md`. Contrôle ensuite avec un envoi de test vers
   <https://www.mail-tester.com> : viser 9 ou 10 sur 10.
5. **Photos** : voir `docs/seo-ia/installer-python-windows.md` et
   `images/decouvrir/README.md`. Rien d'urgent, le site est complet sans elles.

---

## Déploiement par Git — ce qu'il faut savoir

L'hébergement est relié au dépôt : le contenu de la branche est copié tel quel
dans le dossier public du site. Trois conséquences.

**1. Tout ce qui est versionné devient accessible par le web.** C'est pourquoi
un `.htaccess` a été ajouté à la racine : il ferme `.git/`, `docs/`, `tools/`,
`phpmailer/`, `partials/`, `cache/`, les fichiers commençant par un point et
les extensions sensibles. Sans lui, `/.git/HEAD` se télécharge — et avec lui
l'intégralité du code et de son histoire.

Après chaque déploiement, une vérification suffit :

```
https://bellevuedaveyron.fr/.git/HEAD        → doit renvoyer 403 ou 404
https://bellevuedaveyron.fr/docs/            → doit renvoyer 403
```

**2. `config/secrets.php` n'est pas dans le dépôt** — c'est voulu, et c'est le
piège principal de ce mode de déploiement : **un déploiement Git l'efface**,
puisqu'il remplace le dossier par le contenu du dépôt, où ce fichier n'existe pas.

> **Le remède définitif : placer les identifiants hors de la racine web.**
> Créez un fichier `secrets-bellevue.php` **à côté** du dossier du site — donc
> un niveau au-dessus de `public_html/`, par exemple
> `/home/u424962071/domains/bellevuedaveyron.fr/secrets-bellevue.php`. Le
> déploiement ne touche jamais à ce dossier : le fichier survit à toutes les
> mises en ligne.
>
> Son contenu est exactement celui de `config/secrets.php` (voir
> `config/secrets.example.php`). Une fois créé, `config/secrets.php` peut être
> supprimé — s'il subsiste, il reste prioritaire, ce qui permet une transition
> en douceur.
>
> La ligne « Secrets hors racine web » de `diagnostic.php` indique le chemin
> exact attendu et s'il est en place.

Les symptômes sont trompeurs : le site continue de s'afficher, mais les
identifiants sont vides. **Les envois de courrier échouent silencieusement**, et
les tarifs peuvent disparaître de la page d'accueil.

Pour savoir où vous en êtes, ouvrez :

```
https://bellevuedaveyron.fr/diagnostic.php?cle=bellevue-diag
```

La ligne `config/secrets.php` doit être verte. Si elle est rouge, le fichier est
absent : recréez-le à partir de `config/secrets.example.php`, qui est livré avec
le site dans le dossier `config/`. **Gardez-en une copie hors du serveur** —
vous en aurez besoin après chaque déploiement qui l'efface.

Pour tester l'envoi de courrier en lisant la réponse exacte du serveur :

```
https://bellevuedaveyron.fr/diagnostic.php?cle=bellevue-diag&smtp=1
```

Ce contrôle ouvre une vraie connexion et tente une authentification. Le mot de
passe n'est jamais affiché ; la réponse du serveur, si. C'est elle qui dit si la
boîte est suspendue, le mot de passe erroné, ou le réglage absent. Ne le lancez
pas en boucle : des tentatives répétées sont précisément ce qui fait fermer une
boîte d'envoi.

**3. Une erreur dans le `.htaccess` met tout le site en erreur 500.** Le
remède tient en un geste : dans le gestionnaire de fichiers, renommez
`.htaccess` en `.htaccess-hs`. Le site revient aussitôt, sans ses protections,
le temps de corriger.

---

## En cas de problème — erreur 500 ou page blanche

**Ouvrir d'abord `https://bellevuedaveyron.fr/diagnostic.php`.** Cette page
vérifie la version de PHP, les extensions, la présence et la **syntaxe** de
chaque fichier de configuration, la connexion à la base, les droits sur
`cache/`, et affiche les dernières erreurs PHP du serveur. Elle n'affiche
aucun mot de passe. **La supprimer une fois le problème réglé.**

Une erreur 500 vient presque toujours de l'une de ces trois causes :

| Cause | Signe dans le diagnostic |
|---|---|
| Erreur de frappe dans `config/secrets.php` (apostrophe dans une chaîne, virgule oubliée) | ligne `config/secrets.php` en rouge, avec le message de syntaxe |
| Fichier non téléversé | ligne « fichier absent » en rouge |
| Version de PHP trop ancienne | ligne « Version de PHP » en rouge |

Si le diagnostic ne suffit pas, téléverser **`debug-500.php`** et l'ouvrir :
il force l'affichage des erreurs PHP et charge les fichiers du site un par un.
La dernière étape affichée avant l'arrêt désigne le fichier fautif, avec son
numéro de ligne. À supprimer également après usage.

Si aucun des deux ne s'ouvre, le journal d'erreurs se consulte
dans hPanel → **Avancé → Journaux d'erreurs PHP** : la dernière ligne
« Fatal error » donne le fichier et le numéro de ligne exacts.

En dernier recours, restaurer l'archive `.zip` de l'étape 2, et me décrire le
message d'erreur exact. Le fichier `config/secrets.php` créé à l'étape 1, lui, peut rester en
place : il n'est utilisé que par la nouvelle version.

---

## Pour mémoire — ce que cette version change

- Données structurées, `robots.txt`, `sitemap.xml`, `llms.txt`, FAQ,
  métadonnées : référencement par les moteurs de réponse (voir
  `docs/seo-ia/audit-2026-09-08.md`).
- Coordonnées GPS corrigées et distances routières rectifiées.
- Agenda trié par proximité réelle, rendu côté serveur.
- Destinataires du formulaire réglables dans l'administration.
- Avis Google : note, compteur et trois derniers avis synchronisés
  automatiquement (facultatif, voir `docs/seo-ia/avis-google.md`).
- Audience du site dans l'administration : carte du monde, France et pays
  limitrophes, pages vues. Les tables se créent seules à la première
  ouverture de l'écran.
- Annonces : bandeau d'information publiable depuis l'administration.
- Tarifs présentés par saison (haute, moyenne, basse), avec équivalent par
  nuit et conditions de location explicites.
- Formulaire de réservation protégé : champ piège, délai minimal, limitation
  par réseau, contrôle du contenu, validation des destinataires et des
  en-têtes. C'est la réponse à la suspension de la boîte d'envoi — voir
  `docs/securite-mail.md`.
- Couche d'envoi reprise : échappement du point, en-têtes `Date` et
  `Message-ID`, réponses du serveur vérifiées, encodage conforme.
- Les deux messages du formulaire — celui des propriétaires et l'accusé de
  réception du client — sont désormais mis en forme à l'image du site, en
  HTML doublé d'une version texte.
- Le site n'annonce plus une demande « bien reçue » quand aucun message n'a pu
  partir : il dit ce qui s'est passé et invite à appeler. La demande reste
  enregistrée en base.
- `diagnostic.php?…&smtp=1` teste l'envoi de courrier et affiche la réponse du
  serveur de messagerie.
- Numéro de version en pied de page : il permet de vérifier d'un coup d'œil
  que le site en ligne correspond bien à la dernière version publiée.
- `.htaccess` à la racine : ferme `.git/`, `docs/`, `tools/`, `phpmailer/`,
  `partials/`, `cache/`, les fichiers cachés et les extensions sensibles —
  nécessaire depuis que l'hébergement déploie le dépôt tel quel. Ajoute aussi
  quelques en-têtes de sécurité, et propose en option la redirection vers
  l'adresse canonique.
- Suivi des campagnes publicitaires sans traceur ni cookie : les paramètres
  posés au bout d'un lien d'annonce sont comptés, et les demandes qui en
  découlent rattachées. Les colonnes se créent seules.
- Carte de l'écran Audience servie par le site : elle ne dépend plus d'un
  réseau de diffusion externe, qui était injoignable et laissait la zone vide.
- Section Réservation entièrement refaite : deux étapes numérotées, un seul
  bouton dont l'intitulé suit l'état de la sélection, semaines louées
  reconnaissables au premier coup d'œil, et calendrier utilisable au clavier
  comme au doigt.
- Mots de passe sortis du code ; les deux scripts de maintenance de
  l'administration désormais réservés aux administrateurs connectés.
- `index.php?show_log=1` et le fichier `bellevue_debug_mail.log` supprimés :
  ce journal siégeait à la racine du site avec les adresses de vos clients.

**Prérequis serveur** : PHP 7.4 ou plus récent (Hostinger propose 8.x par
défaut ; vérifiable dans hPanel → Avancé → Configuration PHP).
