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
