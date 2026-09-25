# Habillage de l'administration

Les écrans d'administration ne dépendent plus d'aucun réseau de diffusion
externe. Tout ce qui les met en forme est servi par le site lui-même, depuis
`admin/assets/` :

| Fichier | Rôle | Origine |
|---|---|---|
| `tailwind.css` | toute la mise en page | produit ici, voir plus bas |
| `icones.css` + `icones.woff2` | les icônes (Material Symbols) | sous-ensemble téléchargé chez Google |
| `inter/inter.css` + `inter-latin*.woff2` | la police de caractères | Google Fonts |
| `fullcalendar/*` | le planning de l'écran Calendrier | paquet npm `fullcalendar` |

Ces fichiers sont **produits**, pas écrits à la main : ne les modifiez pas
directement, la prochaine régénération effacerait la correction.

Ils doivent être téléversés avec le reste du site. Sans eux, l'administration
s'affiche en texte brut.

## Régénérer la feuille de style

À faire **chaque fois qu'une classe nouvelle apparaît dans un écran**
d'administration : elle n'existe pas encore dans le fichier produit, et
l'élément concerné s'affichera sans style.

```bash
cd tools/habillage-admin
npm install          # une seule fois
npm run build        # écrit ../../admin/assets/tailwind.css
```

L'analyse ne voit que les classes écrites en toutes lettres dans les fichiers
`admin/*.php`. Une classe assemblée en PHP (`'bg-' . $couleur`) lui échappe :
écrivez la classe entière dans chaque branche du test.

## Régénérer les icônes

Les icônes sont un sous-ensemble : seules celles employées par les écrans
figurent dans le fichier de police, ce qui le ramène de 4 Mo à 4,7 Ko. Ajouter
une icône dans un écran ne suffit donc pas — il faut refaire le sous-ensemble,
sinon le nom de l'icône s'affiche en toutes lettres à la place du dessin.

```bash
NOMS=$(php tools/habillage-admin/liste-icones.php)
curl -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120" \
     "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&icon_names=$NOMS&display=block" \
     -o /tmp/ms.css
curl "$(grep -oE 'https://fonts.gstatic.com/[^)]*' /tmp/ms.css)" \
     -o admin/assets/icones.woff2
```

`liste-icones.php` lit les écrans. Les icônes choisies par une condition PHP
lui sont invisibles : elles sont déclarées en tête du script, et il signale sur
la sortie d'erreur chaque expression qu'il n'a pas su lire — vérifiez cette
liste quand vous en ajoutez une.

`icones.css` est écrit à la main (il ne contient que la déclaration de police
et la classe `.material-symbols-outlined`) : il n'y a qu'à y corriger le
nombre d'icônes mentionné en commentaire.

## Remettre à niveau FullCalendar ou la police

```bash
# FullCalendar : prendre le paquet « fullcalendar », pas « @fullcalendar/core »,
# qui ne contient aucune vue — le planning resterait vide.
npm pack fullcalendar@6.1.10
tar xzf fullcalendar-6.1.10.tgz
cp package/index.global.min.js package/LICENSE.md admin/assets/fullcalendar/
```

La locale française vient de `@fullcalendar/core@6.1.10/locales/fr.global.min.js`.

Pour la police Inter, Google sert la même fonte variable pour les quatre
graisses : ne garder qu'un fichier par jeu de caractères (latin et
latin-ext), sans quoi le même fichier est téléchargé quatre fois.

## Vérifier

Servir le dossier `admin/` avec une session ouverte et regarder chaque écran
à 390 px et à 1280 px de large. Deux contrôles valent la peine d'être
automatisés :

- `document.documentElement.scrollWidth <= clientWidth` sur chaque page : rien
  ne dépasse horizontalement ;
- aucun élément `.material-symbols-outlined` plus large que 40 px : une icône
  absente du sous-ensemble s'affiche en toutes lettres, donc large.
