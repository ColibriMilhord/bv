# Inventaire des visuels — page « Découvrir »

Généré depuis `config/medias.php`. Ce tableau sert de feuille de route photo.

## Comment remplacer une image

Déposer le fichier dans `images/decouvrir/` en le nommant exactement comme le
**slug** de la ligne, avec l'extension `.webp`, `.jpg`, `.jpeg` ou `.png` :

```
images/decouvrir/au-moulin-d-alexandre.jpg
```

La substitution est immédiate, sans aucune modification de code. Format
conseillé : 1200 x 760 px, recadrage paysage, moins de 250 Ko, `.webp` de
préférence. Si le sujet de la photo change, mettre à jour le texte alternatif
correspondant dans `config/medias.php`.

## Pourquoi cela compte pour le référencement IA

Une photo authentique d'un lieu nommé est un signal de première main : elle est
indexable dans Google Images, exploitable par les agents multimodaux, et elle
distingue le site des dizaines de pages illustrées avec les mêmes photos de
banque d'images. Le texte alternatif est, lui, la seule description du visuel
que lit un moteur de recherche.

## Statut des visuels

- **réel** — le visuel montre effectivement le sujet de la carte
- **générique** — photo d'illustration, à remplacer en priorité

| Slug (nom du fichier à déposer) | Sujet attendu | Statut | Repli actuel |
|---|---|---|---|
| `au-moulin-d-alexandre` | Table de restaurant en terrasse — Au Moulin d'Alexandre, Sainte-Eulalie-d'Olt | **générique** | banque d’images |
| `maison-de-severac` | Assiette gastronomique — Maison de Sévérac, Sévérac d'Aveyron | **générique** | banque d’images |
| `les-burons-de-l-aubrac` | Aligot de l'Aubrac, servi dans les burons du plateau | réel | Wikimedia Commons |
| `restaurant-bras-laguiole` | Cuisine gastronomique — restaurant Bras, Laguiole | **générique** | banque d’images |
| `festival-en-vallee-d-olt` | Le village de Sainte-Eulalie-d'Olt vu du château, cadre du Festival en Vallée d'Olt | réel | Wikimedia Commons |
| `eulalie-d-art` | Atelier d'artiste — parcours Eulalie d'Art à Sainte-Eulalie-d'Olt | **générique** | banque d’images |
| `musee-marcel-boudou-et-expos-estivales` | Le village de Sainte-Eulalie-d'Olt vu du château, où se tient le musée Marcel Boudou | réel | Wikimedia Commons |
| `maison-de-severac-art-et-gastronomie` | Salle de restaurant contemporaine — Maison de Sévérac | **générique** | banque d’images |
| `avenga-canoe-kayak-sur-le-lot` | Descente en canoë-kayak sur la rivière Lot avec Avenga | **générique** | banque d’images |
| `o-paddle-d-olt` | Stand-up paddle sur le Lot avec O'Paddle d'Olt | **générique** | banque d’images |
| `l-aubrac-grands-espaces-et-faune` | Le plateau de l'Aubrac, grands espaces à 25 km du gîte | réel | Wikimedia Commons |
| `marche-estival-de-sainte-eulalie-d-olt` | Étal de producteurs — marché estival de Sainte-Eulalie-d'Olt | **générique** | banque d’images |
| `de-faire-et-de-savoir` | Atelier d'artisanat local — De Faire et de Savoir | **générique** | banque d’images |
| `eulalie-d-art-ateliers-et-creations` | Atelier de céramique — Eulalie d'Art, Sainte-Eulalie-d'Olt | **générique** | banque d’images |
| `marches-de-saint-geniez-d-olt` | Marché hebdomadaire de Saint-Geniez-d'Olt, à 2 km du gîte | **générique** | banque d’images |
| `ping-pong-trampoline-velos-et-piscine` | Piscine chauffée et terrasse du gîte Bellevue d'Aveyron, face à la vallée du Lot | réel | **photo du gîte** |
| `jardin-des-betes` | Ferme pédagogique — Jardin des Bêtes, animaux de la ferme | **générique** | banque d’images |
| `o-paddle-d-olt-canoe-kayak-et-sup` | Canoë, kayak et paddle sur le Lot avec O'Paddle d'Olt | **générique** | banque d’images |
| `maison-de-la-chouette-sainte-eulalie-d-o` | Chouette — Maison de la Chouette à Sainte-Eulalie-d'Olt | **générique** | banque d’images |
| `air-globe-fun-e-bike` | Balade en vélo électrique tout-terrain avec Air Globe | **générique** | banque d’images |
| `bozouls-et-son-canyon-emblematique` | Le canyon de Bozouls et son méandre du Dourdou, Aveyron | réel | Wikimedia Commons |
| `les-secrets-du-tresor-de-conques` | Nef de l'abbatiale Sainte-Foy de Conques, qui abrite le Trésor | réel | Wikimedia Commons |
| `balades-au-bord-du-lot-et-village` | Le village médiéval de Sainte-Eulalie-d'Olt et les bords du Lot | réel | Wikimedia Commons |
| `theme-nature-activites` | Le plateau de l'Aubrac, terrain de randonnée à 25 km du gîte | réel | Wikimedia Commons |
| `theme-agenda-evenements` | Sainte-Eulalie-d'Olt vu du château, village des marchés et fêtes de l'été | réel | Wikimedia Commons |
| `theme-conques-patrimoine` | Le village de Conques et son abbatiale Sainte-Foy, à 55 km du gîte | réel | Wikimedia Commons |
| `theme-tables-gastronomie` | Aligot de l'Aubrac, spécialité des tables aveyronnaises | réel | Wikimedia Commons |

**15 visuels sur 27** sont encore des photos d’illustration, à remplacer par de vraies photos des lieux.

Déjà traité :

- La carte « Ping-Pong, Trampoline, Vélos & Piscine », qui parle du gîte, utilise
  désormais une vraie photo de la propriété (piscine et terrasse face à la vallée).
- Le Festival en Vallée d'Olt était illustré par la nef de l'abbatiale de Conques,
  à 70 km : il montre maintenant le village de Sainte-Eulalie-d'Olt.
- Le Trésor de Conques reçoit cette nef, où le trésor est effectivement conservé.
