# Avis Google — mise à jour automatique

## Ce qui a changé

Avant, les trois avis affichés et le compteur « 102 avis » étaient écrits en
dur dans `index.php` : ils ne bougeaient jamais, et l'un des avis affichait
« il y a environ 7 ans et 6 mois ». La ligne « Dernière mise à jour »
affichait, elle, l'heure de chargement de la page — ce qui laissait croire à
une synchronisation qui n'existait pas.

Désormais, `config/avis.php` interroge l'API Google Places et en tire :

- la **note moyenne** réelle ;
- le **nombre total d'avis**, qui suit donc votre fiche Google ;
- les **trois avis les plus récents**, datés en relatif (« il y a 3 semaines »).

Le tout est mis en cache douze heures : Google est interrogé deux fois par
jour au maximum, quel que soit le trafic.

Ces mêmes valeurs alimentent le badge du bandeau d'accueil, l'en-tête du bloc
d'avis, la description de la page **et** le balisage `aggregateRating`. Ils ne
peuvent plus se contredire — condition de validité du balisage aux yeux de
Google.

## Tant que l'API n'est pas configurée

Le site affiche la sélection de `config/avis-secours.php`, modifiable à la
main, avec la mention « Sélection publiée par les propriétaires ». Rien ne
casse, et aucune date fantaisiste n'est affichée.

---

## Configuration, en trois étapes

### 1. Récupérer le Place ID de la fiche

Ouvrir l'outil officiel :
<https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder>

Chercher **« Bellevue d'Aveyron Sainte-Eulalie-d'Olt »**, cliquer sur le
repère : l'identifiant s'affiche, de la forme `ChIJ...`. Le copier.

### 2. Créer une clé d'API

1. Aller sur <https://console.cloud.google.com/> et créer un projet (ou en
   choisir un existant).
2. Menu **API et services → Bibliothèque** : activer **Places API (New)**.
   L'ancienne « Places API » fonctionne aussi, le code gère les deux.
3. Menu **Identifiants → Créer des identifiants → Clé API**. Copier la clé.
4. **Restreindre la clé**, c'est important : dans ses paramètres, section
   « Restrictions relatives aux API », limiter à Places API. Section
   « Restrictions relatives aux applications », choisir **Adresses IP** et
   saisir l'adresse IP du serveur Hostinger (visible dans hPanel). Sans
   restriction, une clé qui fuite peut être utilisée à vos frais.

> **Coût.** Google offre un crédit mensuel qui couvre très largement cet
> usage : avec un cache de douze heures, le site fait environ 60 appels par
> mois. Une carte bancaire reste exigée à la création du compte de
> facturation. Pensez à définir un plafond d'alerte dans **Facturation →
> Budgets et alertes**.

### 3. Renseigner les deux valeurs sur le serveur

Dans `config/secrets.php`, ajouter :

```php
    'GOOGLE_PLACES_API_KEY' => 'AIza…votre_clé…',
    'GOOGLE_PLACE_ID'       => 'ChIJ…votre_place_id…',
```

Recharger la page d'accueil. Sous les avis, la mention doit passer de
« Sélection publiée par les propriétaires » à « Google — synchronisé le … »,
et le badge de `SÉLECTION` à `GOOGLE`.

## Si le badge reste sur « SÉLECTION »

Téléverser **`diagnostic-avis.php`** à la racine du site et l'ouvrir :
`https://bellevuedaveyron.fr/diagnostic-avis.php`. Il affiche les deux
réglages tels qu'ils sont lus, l'état du cache, puis **interroge réellement
Google et montre sa réponse**, message d'erreur compris, avec la marche à
suivre correspondante. La clé n'y apparaît jamais en entier. À supprimer
ensuite.

Les trois causes habituelles :

| Cause | Ce que montre le diagnostic |
|---|---|
| Les deux clés sont écrites en dehors du `return [ … ];` de `config/secrets.php` | « ABSENTE » / « ABSENT » |
| API non activée, clé restreinte à d'autres adresses, facturation absente | le message de refus de Google, traduit en clair |
| Dossier `cache/` en lecture seule | signalé dans la section Cache |

**Un échec est mémorisé une heure** pour ne pas rappeler Google à chaque
visite : après avoir corrigé un réglage, utiliser le bouton « Vider le cache
et retester » du diagnostic, sinon l'ancien état persiste jusqu'à une heure.

Les échecs sont également consignés dans le journal d'erreurs PHP du serveur,
avec le code HTTP et le message renvoyés par Google.

---

## Points à connaître

**Google ne renvoie que cinq avis**, et c'est lui qui choisit lesquels — il
n'existe aucun paramètre pour demander « les plus récents ». Le code trie donc
par date décroissante les avis reçus et retient les trois plus récents. C'est
le maximum atteignable par l'API ; un affichage de l'intégralité des avis
supposerait un service tiers payant (Elfsight, Trustindex et équivalents).

**Les noms sont abrégés** : « Jean-Pierre Martin » devient « Jean-Pierre M. ».
Publier le nom complet d'un client sur une page publique n'est ni nécessaire
ni souhaitable.

**Les textes sont tronqués** à environ 260 caractères, sur un espace, avec des
points de suspension — pour que les trois cartes gardent la même allure.

**Balisage et avis tiers.** Les règles de Google sur les extraits enrichis
demandent que les avis balisés soient collectés par le site lui-même, et non
repris d'une plateforme tierce. Afficher la note Google sur son site est
courant et sans risque ; la baliser en `aggregateRating` l'est un peu moins.
Le balisage est en place car il pèse dans les réponses génératives, mais si
vous préférez la prudence côté Google, dites-le moi : il se retire en une
ligne dans `config/seo.php`.

---

## Entretien

| Situation | À faire |
|---|---|
| API configurée | Rien. Note, compteur et avis suivent Google. |
| API non configurée | Mettre à jour `config/avis-secours.php` de temps en temps (note, total, trois avis). |
| Changement de note ou de compteur | `llms.txt` mentionne « 102 avis au 8 septembre 2026 » : y reporter la valeur et la date lors d'une révision. |
