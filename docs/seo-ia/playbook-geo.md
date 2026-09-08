# Playbook GEO — rendre un site citable par les IA

**Méthode générique, transposable telle quelle à artifacile.fr.**
Elle a été construite et éprouvée sur bellevuedaveyron.fr (site PHP artisanal,
4 pages). Les principes ne dépendent ni du langage, ni du CMS.

> GEO = *Generative Engine Optimization*. Objectif : ne pas se contenter d'être
> classé par un moteur de recherche, mais **être la source citée** dans la
> réponse d'un moteur génératif (Google AI Overviews, ChatGPT Search,
> Perplexity, Claude, Copilot, Gemini).

---

## Le principe en une phrase

> Un moteur de recherche indexe des pages. Un moteur de réponse **extrait des
> faits** et cite qui les a exprimés le plus clairement.

Tout le reste en découle. Un site bien référencé en SEO classique peut être
totalement absent des réponses génératives s'il exprime ses faits en images,
en iframes, en cartes interactives ou en prose allusive.

---

## Les 5 principes

### 1. Un fait, une phrase, une seule fois

Chaque information doit exister quelque part dans une phrase **autoportante** :
compréhensible sans le paragraphe qui précède, sans le titre, sans l'image.

- ✗ « Nous vous accueillons dans un cadre d'exception toute l'année. »
- ✓ « Bellevue d'Aveyron est un gîte 5 étoiles de 200 m² à Sainte-Eulalie-d'Olt
  (12130), qui accueille jusqu'à 10 personnes dans 5 chambres. »

La deuxième phrase est citable. La première ne l'est pas.

### 2. Le même fait ne doit jamais se contredire

Adresse, téléphone, e-mail, prix, capacité, horaires : une contradiction entre
deux pages fait perdre la citation, car le moteur ne sait pas quoi croire.

D'où la règle d'architecture : **une source unique de vérité en code**.
Sur ce projet, `config/seo.php` porte l'identité complète du site, et
`seo_faq()` alimente à la fois le HTML affiché et le balisage. Il est
impossible de désynchroniser les deux.

### 3. Ce que le robot ne peut pas lire n'existe pas

Zones aveugles classiques, toutes rencontrées ici :

| Zone aveugle | Correctif |
|---|---|
| Contenu dans un `<iframe>` tiers | Rendre l'essentiel côté serveur, garder l'iframe en complément |
| Image en `background-image` CSS | Balise `<img>` + texte alternatif descriptif |
| Contenu chargé en JavaScript après le premier rendu | Rendu serveur, ou balisage JSON-LD équivalent |
| Information portée par une icône ou une carte retournable | Doubler par une phrase en clair |
| Fait affiché uniquement en image (prix, horaires) | Texte + balisage |

### 4. Écrire aussi pour la machine

Trois fichiers, trois publics :

- `robots.txt` — **qui a le droit d'entrer.** Autoriser nommément les robots
  des moteurs de réponse (voir la liste plus bas), fermer les zones privées.
- `sitemap.xml` — **quelles pages existent**, avec leur date de mise à jour.
- `llms.txt` — **le résumé factuel du site**, en Markdown, à la racine. C'est
  ce que lit un agent qui ne veut pas analyser 60 Ko de HTML. Convention
  émergente, coût quasi nul, à tenir à jour comme une fiche d'identité.

Et dans chaque page : `<link rel="canonical">` et
`<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">`.
Sans `max-snippet:-1`, les extraits longs sont bridés — exactement ce dont un
AI Overview a besoin.

### 5. Le balisage JSON-LD est le format de sortie

C'est le seul endroit où un fait est **non ambigu**. Un graphe `@graph` unique
par page, qui relie l'entité principale, la page, le fil d'Ariane et la FAQ.

Nœuds qui rapportent le plus, dans l'ordre :

1. **L'entité principale** — ce que le site *est* : `LocalBusiness`,
   `Organization`, `Product`, `SoftwareApplication`, `VacationRental`…
   avec adresse, contact, identifiants (SIRET), notes, offres, caractéristiques.
2. **`FAQPage`** — l'unité de citation par excellence : une question, une
   réponse autoportante. Les réponses **doivent** être visibles sur la page.
3. **`BreadcrumbList`** et **`WebPage`** — situent la page dans le site.
4. Nœuds métier selon le domaine : `Offer`, `Event`, `Review`, `ItemList`,
   `HowTo`, `Service`.

**Générer le balisage depuis les données réelles**, jamais à la main : ici les
tarifs `Offer` viennent de la table `tarifs_saison`, donc ils ne périment pas.

---

## Le parcours d'audit, dans l'ordre

Une heure suffit sur un petit site. À faire dans cet ordre, chaque étape
conditionnant la suivante.

1. **Accès** — `robots.txt` et `sitemap.xml` existent-ils ? Que voit un robot
   qui arrive à la racine ? Les zones d'administration sont-elles fermées ?
2. **Identité** — un même nom, une même adresse, un même téléphone partout ?
   Un domaine unique ? (Cet audit-ci a révélé deux domaines concurrents.)
3. **Structure** — un seul `<h1>` par page ? La hiérarchie des titres
   raconte-t-elle le contenu ? Le HTML est-il valide (`</body>` présent…) ?
4. **Extractibilité** — lister les faits clés du site et, pour chacun,
   vérifier qu'il apparaît dans une phrase en clair. Ce qui manque devient
   souvent une FAQ.
5. **Balisage** — quels nœuds JSON-LD, alimentés par quelles données ?
6. **Zones aveugles** — iframes, images de fond, contenus JavaScript.
7. **Cohérence géographique et chiffrée** — recalculer ce qui est calculable
   (distances, totaux). Sur ce projet, ce contrôle a révélé des coordonnées
   GPS erronées de 12 km, cause d'un bug fonctionnel visible.
8. **Vérification** — rendre chaque page, extraire chaque bloc JSON-LD, le
   décoder, compter les `<h1>`, les `canonical`, les images sans `alt`.

---

## Robots des moteurs de réponse (à jour en 2026)

| Éditeur | Agents |
|---|---|
| OpenAI | `GPTBot` (entraînement), `OAI-SearchBot` (index ChatGPT Search), `ChatGPT-User` (navigation à la demande) |
| Anthropic | `ClaudeBot`, `Claude-SearchBot`, `Claude-User` |
| Perplexity | `PerplexityBot`, `Perplexity-User` |
| Google | `Googlebot`, `Google-Extended` (pilote l'usage génératif, distinct de l'indexation) |
| Microsoft | `bingbot` (alimente Copilot) |
| Apple | `Applebot`, `Applebot-Extended` |
| Autres | `MistralAI-User`, `meta-externalagent`, `Amazonbot`, `DuckAssistBot`, `YouBot`, `CCBot` |

**Décision à prendre explicitement**, projet par projet : un site commercial qui
veut être recommandé les autorise ; un site dont le contenu *est* le produit
(base documentaire payante, création originale) peut vouloir autoriser les
robots de *recherche* (`OAI-SearchBot`, `Claude-SearchBot`, `PerplexityBot`) et
refuser ceux d'*entraînement* (`GPTBot`, `CCBot`, `Google-Extended`). Ne jamais
laisser ce choix au hasard d'une absence de `robots.txt`.

---

## Le patron de code, transposable

Trois fichiers, un rôle chacun. La structure vaut pour n'importe quel langage.

```
config/seo.php      → identité du site + faits + rendu <head> + nœuds JSON-LD
config/medias.php   → registre des visuels : alt, repli, substitution locale
config/agenda.php   → données métier externes : cache, filtrage, normalisation
config/env.php      → secrets : environnement d'abord, fichier non versionné ensuite
```

**Le contrat de `seo.php`** — c'est celui à recopier en premier :

```php
// 1. Constantes d'identité : la seule chose à changer d'un site à l'autre
const SEO_SITE_URL = 'https://exemple.fr';
const SEO_SITE_NAME = '…';   // + téléphone, e-mail, adresse, géo, notes…

// 2. Rendu du <head> : titre, description, robots, canonical, OG, Twitter, geo
function seo_head(array $page): void

// 3. Sérialisation d'un graphe JSON-LD unique par page
function seo_jsonld(array $nodes): void

// 4. Faits : consommés à la fois par l'affichage ET par le balisage
function seo_faq(): array
function seo_amenities(): array

// 5. Constructeurs de nœuds
function seo_node_website(): array
function seo_node_webpage(string $path, string $name, string $desc): array
function seo_node_breadcrumb(array $items): array
function seo_node_faq(array $qa): array
function seo_node_<entite>(array $donnees): array   // spécifique au métier
```

Dans une page :

```php
<head>
  <?php seo_head(['title' => …, 'description' => …, 'path' => 'page.php']); ?>
</head>
…
<?php foreach (seo_faq() as [$q, $r]): ?>
  <h3><?= seo_e($q) ?></h3><p><?= seo_e($r) ?></p>
<?php endforeach; ?>
…
<?php seo_jsonld([seo_node_website(), seo_node_entite($data), seo_node_faq(seo_faq())]); ?>
```

**Le patron `medias.php`** — vaut pour tout site à visuels éditoriaux :
un registre `slug => [alt, url de repli, statut]`, une résolution qui privilégie
un fichier local s'il existe (`images/<dossier>/<slug>.webp|jpg|…`), un rendu
`<img>` avec `alt`, `loading="lazy"`, `decoding="async"`. Conséquence pratique :
remplacer une photo se fait en déposant un fichier, sans toucher au code — et
l'inventaire des visuels à remplacer se génère automatiquement depuis le
registre.

**Le patron `env.php`** — une fonction `secret('CLE')` qui consulte dans
l'ordre les variables d'environnement, un fichier non versionné, puis une
valeur par défaut ; un `secrets.example.php` versionné sans aucune valeur
réelle ; un `.htaccess` interdisant l'accès web au dossier de configuration.
Corollaire à ne pas oublier : un mot de passe déjà passé dans Git reste
lisible dans l'historique — sortir le secret du code **et** le changer.

**Le patron `agenda.php`** — vaut pour toute donnée externe affichée :
cache disque avec TTL, cache négatif après échec, délai réseau plafonné,
dégradation silencieuse vers un socle éditorial vérifié, **filtrage et tri
côté site plutôt que confiance aveugle au fournisseur**.

---

## Recette de vérification

Ne jamais livrer sans avoir passé ces contrôles — ils tiennent en un script.

```bash
# 1. Syntaxe
for f in $(find . -name '*.php' -not -path './vendor/*'); do php -l "$f" >/dev/null || echo "KO $f"; done

# 2. Rendu réel de chaque page publique
php -f index.php > /tmp/out.html
```

```python
# 3. Contrôles sur la page rendue
#    - exactement un <h1> et un <link rel=canonical>
#    - chaque bloc JSON-LD se décode
#    - aucune <img> sans alt
#    - les Q/R balisées sont bien les Q/R affichées
```

Ce dernier point est le plus important : **du balisage qui décrit autre chose
que la page est pire que pas de balisage** — c'est traité comme une tentative
de manipulation.

---

## Ce qui ne s'achète pas en code

Le balisage rend un site *extractible*. Il ne le rend pas *crédible*. Les
moteurs de réponse croisent systématiquement le site avec des sources tierces :

1. **Fiche Google Business Profile** revendiquée et à jour — première source
   des réponses locales.
2. **Avis** vérifiables, avec auteur et date.
3. **Mentions cohérentes** ailleurs sur le web (annuaires, presse locale,
   partenaires) : mêmes nom, adresse, téléphone, au caractère près.
4. **Pages de fond** qui répondent à une question réelle, une par page. C'est
   le format le plus cité : titre = la question, premier paragraphe = la
   réponse complète, suite = le détail.

---

## Adaptation à artifacile.fr

Le socle se transpose sans réécriture ; seuls changent les faits et l'entité.

| Élément | Bellevue d'Aveyron | artifacile.fr |
|---|---|---|
| Entité principale | `VacationRental` / `LodgingBusiness` | `SoftwareApplication` ou `Service` + `Organization` |
| Offres | `Offer` par saison, depuis la base | `Offer` par formule d'abonnement |
| Preuve sociale | `AggregateRating` Google | `AggregateRating` + `Review` clients artisans |
| FAQ | 9 questions séjour | Questions d'usage réelles : facturation, devis, TVA, obligations légales des artisans |
| `llms.txt` | Fiche du gîte | Ce que fait l'outil, pour qui, ce qu'il ne fait pas, tarifs, contact |
| Contenu de fond | Pages « où loger pour… » | Pages « comment faire un devis conforme », « mentions obligatoires d'une facture d'artisan » |

Le gisement propre à artifacile.fr : ce sont des **questions métier à forte
intention** (obligations légales, mentions obligatoires, taux de TVA
applicables). Ce sont exactement les questions que les artisans posent à un
assistant IA. Une page = une question = une réponse structurée, balisée en
`FAQPage` ou `HowTo`, et le site devient la source citée.

Points de vigilance spécifiques :

- Une application a beaucoup de contenu derrière authentification : ce qui doit
  être cité doit vivre sur des pages publiques.
- Le contenu chargé en JavaScript doit avoir un équivalent serveur ou JSON-LD.
- Sur des sujets réglementaires, dater les pages (`dateModified`) et citer les
  textes de référence : les moteurs de réponse privilégient les sources
  fraîches et vérifiables sur ces thématiques.

---

## Journal des décisions du projet de référence

| Décision | Motif |
|---|---|
| Autoriser tous les robots IA | Objectif commercial : être recommandé, pas protéger un contenu |
| Une source unique pour l'identité | Empêche structurellement les contradictions |
| FAQ visible plutôt que repliée | Extraction plus fiable, et exigence de Google |
| Offres générées depuis la base | Un prix balisé périmé est pire qu'un prix absent |
| Rendu serveur de l'agenda | Un iframe ne transmet aucune autorité au site |
| Images `<img>` plutôt que fond CSS | Sans `alt`, un visuel n'existe pas pour un moteur |
| Ne pas corriger les distances affichées | Contenu éditorial : à valider par le propriétaire |
| Ne pas modifier le texte légal | Engage juridiquement le propriétaire |
