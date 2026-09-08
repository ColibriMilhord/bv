# Installer Python sur Windows et lancer le script photos

Marche à suivre complète, de l'ordinateur vierge aux photos en ligne.
Comptez une vingtaine de minutes la première fois.

---

## 1. Installer Python

1. Aller sur **<https://www.python.org/downloads/windows/>**.
2. Cliquer sur le bouton jaune **« Download Python 3.x.x »** (version 64-bit,
   proposée par défaut).
3. Lancer le fichier téléchargé (`python-3.x.x-amd64.exe`).
4. **Sur le premier écran, cocher la case « Add python.exe to PATH »**, en bas
   de la fenêtre. C'est l'étape que tout le monde rate : sans elle, Windows ne
   trouvera pas la commande `py` et rien ne fonctionnera.
5. Cliquer sur **« Install Now »**, puis attendre la fin.
6. Si l'écran final propose **« Disable path length limit »**, cliquer dessus.

> Variante : Python est aussi disponible dans le Microsoft Store (chercher
> « Python 3.12 »). L'installation y est plus simple, mais l'accès aux dossiers
> est parfois restreint. L'installateur de python.org reste préférable.

### Vérifier que ça marche

Ouvrir **PowerShell** : touche Windows, taper `powershell`, Entrée. Puis :

```powershell
py --version
```

La réponse doit ressembler à `Python 3.12.7`.

Si Windows répond que la commande est introuvable : fermer PowerShell, le
rouvrir (le PATH n'est lu qu'au démarrage), et réessayer. Si le problème
persiste, relancer l'installateur, choisir **« Modify »**, puis vérifier que
« Add Python to environment variables » est bien coché.

---

## 2. Récupérer les fichiers du site

Le script doit être lancé depuis le dossier du site, celui qui contient
`index.php`.

- **Si vous avez déjà une copie locale du site** (le dossier que vous
  téléversez sur Hostinger), utilisez-la — à condition qu'elle contienne le
  dossier `tools/`. Sinon, récupérez la version à jour ci-dessous.
- **Sinon**, télécharger depuis GitHub : ouvrir le dépôt, choisir la branche
  `claude/dev-scan-seo-ia-nwugpt`, bouton vert **« Code » → « Download ZIP »**,
  puis décompresser, par exemple dans `Documents\bellevue`.

---

## 3. Obtenir une clé Unsplash

1. Créer un compte (gratuit) sur <https://unsplash.com>.
2. Aller sur <https://unsplash.com/oauth/applications> → **« New Application »**.
3. Accepter les conditions, donner un nom (par exemple « Bellevue d'Aveyron »)
   et une description d'une ligne.
4. Copier la valeur **« Access Key »** (et non la « Secret Key »).

Une application « Demo » est limitée à 50 requêtes par heure — largement
suffisant pour les 15 cartes.

---

## 4. Lancer le script

Dans PowerShell, se placer dans le dossier du site puis déclarer la clé :

```powershell
cd "$HOME\Documents\bellevue"
$env:UNSPLASH_ACCESS_KEY = "collez_ici_votre_access_key"
```

> La clé n'est valable que pour cette fenêtre PowerShell. Si vous la fermez,
> il faudra la redéclarer. Elle n'est jamais enregistrée sur le disque.

### Étape 1 — chercher les photos

```powershell
py tools\unsplash_photos.py search
```

Le script interroge Unsplash pour les 15 cartes et écrit deux fichiers dans
`tools\`. Ouvrir **`tools\unsplash_candidates.html`** dans le navigateur :
6 propositions par carte, avec l'identifiant de la photo sous chaque vignette.

### Étape 2 — télécharger

Pour prendre la première proposition de chaque carte :

```powershell
py tools\unsplash_photos.py download
```

Pour imposer vos choix, reprendre les identifiants relevés sur la
planche-contact :

```powershell
py tools\unsplash_photos.py download --pick jardin-des-betes=AbC123xyz
```

Autres options utiles :

```powershell
py tools\unsplash_photos.py list                                   # état des 15 cartes
py tools\unsplash_photos.py search --only maison-de-la-chouette-sainte-eulalie-d-o
py tools\unsplash_photos.py download --only jardin-des-betes --force   # remplacer
```

---

## 5. Mettre les photos en ligne

Le script a rempli le dossier `images\decouvrir\` et créé `credits.json`.
Téléverser sur le serveur, dans le même dossier :

- tous les fichiers `.jpg` de `images\decouvrir\` ;
- le fichier `images\decouvrir\credits.json`.

Par FTP, ou depuis le gestionnaire de fichiers de Hostinger. Les cartes
changent immédiatement : aucune modification de code n'est nécessaire, et la
ligne de crédits photographes apparaît d'elle-même en pied de la page
« Découvrir » — c'est une obligation de l'API Unsplash.

---

## En cas de problème

| Message | Cause et remède |
|---|---|
| `py : le terme n'est pas reconnu` | La case « Add python.exe to PATH » n'était pas cochée. Relancer l'installateur → « Modify », ou réinstaller. Fermer et rouvrir PowerShell ensuite. |
| `Clé Unsplash absente` | La variable n'est pas déclarée dans **cette** fenêtre PowerShell. Relancer la ligne `$env:UNSPLASH_ACCESS_KEY = "…"`. |
| `Clé Unsplash refusée (401)` | Vous avez copié la « Secret Key » au lieu de l'« Access Key ». |
| `Quota horaire Unsplash épuisé (403)` | 50 requêtes par heure atteintes. Attendre, ou relancer avec `--only` sur quelques cartes. |
| `Aucun candidat en mémoire` | Lancer `search` avant `download`. |
| `Impossible de charger le fichier … car l'exécution de scripts est désactivée` | Ce message concerne les fichiers `.ps1`, pas ce script. Vérifier que la commande commence bien par `py`. |

---

## Rappel

Ces photos restent des images d'illustration : Unsplash n'a aucune photo du
Moulin d'Alexandre, d'Eulalie d'Art ou du marché de Saint-Geniez. Une photo
prise sur place vaut toujours mieux, pour le visiteur comme pour le
référencement — et il suffit de la déposer sous le même nom de fichier pour
qu'elle remplace celle du script.
