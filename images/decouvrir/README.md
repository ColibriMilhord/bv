# Photos des cartes de la page « Découvrir »

Déposez ici vos propres photos : elles remplacent automatiquement l'image de
repli de la carte correspondante, **sans aucune modification de code**.

## Règle de nommage

Le fichier doit porter exactement le **slug** de la carte, avec l'extension
`.webp`, `.jpg`, `.jpeg` ou `.png` :

```
images/decouvrir/au-moulin-d-alexandre.jpg
images/decouvrir/marches-de-saint-geniez-d-olt.webp
```

La liste complète des slugs est dans
[`docs/seo-ia/inventaire-images.md`](../../docs/seo-ia/inventaire-images.md),
avec pour chacun le sujet attendu.

## Option : remplissage automatique via Unsplash

Faute de photos des lieux, le script `tools/unsplash_photos.py` va chercher une
illustration pertinente pour chacune des 15 cartes concernées :

```bash
export UNSPLASH_ACCESS_KEY="votre_access_key"   # unsplash.com/oauth/applications
python3 tools/unsplash_photos.py search         # candidats + planche-contact HTML
python3 tools/unsplash_photos.py download       # dépôt dans ce dossier
```

Le script écrit aussi `credits.json`, qui alimente la ligne de crédits en pied
de la page Découvrir — l'API Unsplash impose de citer les photographes.

Cela reste de la photo d'illustration : une photo prise sur place la remplace
avantageusement, et suffit à écraser le fichier déposé par le script.

## Format conseillé

- 1200 x 760 px, cadrage paysage
- moins de 250 Ko, `.webp` de préférence
- pas de texte incrusté ni de filigrane

## Après le dépôt

Si le sujet de la photo diffère de la description attendue, mettez à jour le
texte alternatif correspondant dans `config/medias.php` : c'est la seule
description du visuel que lisent Google Images et les assistants IA.

Les 16 cartes encore illustrées par une photo de banque d'images sont marquées
« générique » dans l'inventaire : ce sont celles à traiter en priorité.
