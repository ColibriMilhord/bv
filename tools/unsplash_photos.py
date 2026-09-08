#!/usr/bin/env python3
r"""
tools/unsplash_photos.py — Recherche et téléchargement des visuels manquants
de la page « Découvrir » via l'API Unsplash.

Contexte
--------
Les cartes de decouvrir.php sont pilotées par le registre config/medias.php.
Déposer un fichier images/decouvrir/<slug>.jpg suffit à remplacer l'image de
repli d'une carte : ce script automatise ce dépôt.

Prérequis
---------
1. Python 3.9 ou plus récent. Sous Windows, l'installateur de python.org avec
   la case « Add python.exe to PATH » cochée ; la commande est alors « py »
   (ou « python »), et non « python3 ».
2. Créer une application sur https://unsplash.com/oauth/applications
   (gratuit ; une application « Demo » suffit, limitée à 50 requêtes/heure).
3. Déclarer la clé « Access Key » dans l'environnement du terminal.
   Elle n'est jamais écrite sur le disque par ce script.

   Windows, PowerShell :
       $env:UNSPLASH_ACCESS_KEY = "votre_access_key"

   Windows, invite de commandes :
       set UNSPLASH_ACCESS_KEY=votre_access_key

   macOS et Linux :
       export UNSPLASH_ACCESS_KEY="votre_access_key"

   La variable ne vaut que pour la fenêtre de terminal en cours : elle est à
   redéclarer si vous fermez puis rouvrez le terminal.

Utilisation
-----------
Se placer à la racine du projet (le dossier qui contient index.php), puis :

    Windows                                   macOS / Linux
    py tools\unsplash_photos.py list          python3 tools/unsplash_photos.py list
    py tools\unsplash_photos.py search        python3 tools/unsplash_photos.py search
    py tools\unsplash_photos.py download      python3 tools/unsplash_photos.py download

Déroulé :
    1. « search » interroge Unsplash et écrit tools/unsplash_candidates.html —
       à ouvrir dans un navigateur pour choisir les photos à l'œil.
    2. Noter l'identifiant affiché sous chaque photo retenue.
    3. « download » dépose les fichiers dans images/decouvrir/.

    # Imposer une photo précise plutôt que le premier résultat
    py tools\unsplash_photos.py download --pick au-moulin-d-alexandre=AbC123xyz

    # Ne traiter que certaines cartes
    py tools\unsplash_photos.py search --only jardin-des-betes marches-de-saint-geniez-d-olt

    # Remplacer une photo déjà déposée
    py tools\unsplash_photos.py download --only jardin-des-betes --force

Les fichiers déposés dans images/decouvrir/ sont ensuite à téléverser sur le
serveur, dans le même dossier.

Conformité Unsplash
-------------------
L'API impose deux obligations, toutes deux respectées ici :
  • appeler le point d'entrée « download_location » à chaque téléchargement
    (comptabilisation côté photographe) ;
  • créditer le photographe et Unsplash, avec des liens porteurs des
    paramètres UTM. Les crédits sont écrits dans images/decouvrir/credits.json
    et affichés en pied de page de decouvrir.php.

Limites
-------
Unsplash ne contient aucune photo des lieux nommés (Au Moulin d'Alexandre,
Eulalie d'Art, marché de Saint-Geniez…). Ce script fournit donc une
illustration pertinente sur le sujet, pas une photo du lieu réel. Une photo
prise sur place restera toujours supérieure, pour le visiteur comme pour le
référencement.
"""

from __future__ import annotations

import argparse
import html
import json
import os
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

API = "https://api.unsplash.com"
UTM = "utm_source=bellevue_daveyron&utm_medium=referral"

RACINE = Path(__file__).resolve().parent.parent
DOSSIER_IMAGES = RACINE / "images" / "decouvrir"
FICHIER_CANDIDATS = Path(__file__).resolve().parent / "unsplash_candidates.json"
PLANCHE_CONTACT = Path(__file__).resolve().parent / "unsplash_candidates.html"
FICHIER_CREDITS = DOSSIER_IMAGES / "credits.json"

# Largeur de téléchargement : cadrage paysage cohérent avec les cartes du site.
LARGEUR, HAUTEUR = 1200, 760

PAUSE_ENTRE_APPELS = 1.2  # secondes — reste très en deçà des quotas Unsplash


# ── Cartes à illustrer ─────────────────────────────────────────────────────
# slug : (requête Unsplash, sujet attendu tel que décrit dans config/medias.php)
# Les requêtes sont en anglais : l'indexation d'Unsplash y est bien meilleure.
CARTES: dict[str, tuple[str, str]] = {
    "au-moulin-d-alexandre": (
        "french restaurant terrace summer village",
        "Table de restaurant en terrasse dans un village",
    ),
    "maison-de-severac": (
        "gourmet plate fine dining french cuisine",
        "Assiette gastronomique",
    ),
    "restaurant-bras-laguiole": (
        "haute cuisine plating chef restaurant",
        "Cuisine gastronomique",
    ),
    "maison-de-severac-art-et-gastronomie": (
        "contemporary restaurant dining room interior",
        "Salle de restaurant contemporaine",
    ),
    "eulalie-d-art": (
        "artist studio painting workshop",
        "Atelier d'artiste",
    ),
    "eulalie-d-art-ateliers-et-creations": (
        "pottery ceramic workshop hands clay",
        "Atelier de céramique",
    ),
    "de-faire-et-de-savoir": (
        "artisan craft workshop handmade wood",
        "Atelier d'artisanat",
    ),
    "avenga-canoe-kayak-sur-le-lot": (
        "canoe river green valley summer",
        "Descente en canoë sur une rivière",
    ),
    "o-paddle-d-olt": (
        "stand up paddle board calm river",
        "Stand-up paddle sur une rivière",
    ),
    "o-paddle-d-olt-canoe-kayak-et-sup": (
        "kayak paddle lake summer family",
        "Canoë, kayak et paddle",
    ),
    "marche-estival-de-sainte-eulalie-d-olt": (
        "french village market stall vegetables",
        "Étal de producteurs sur un marché de village",
    ),
    "marches-de-saint-geniez-d-olt": (
        "french farmers market cheese charcuterie",
        "Marché de producteurs, fromages et charcuteries",
    ),
    "jardin-des-betes": (
        "petting farm goat children animals",
        "Ferme pédagogique, animaux de la ferme",
    ),
    "maison-de-la-chouette-sainte-eulalie-d-o": (
        "barn owl portrait",
        "Chouette",
    ),
    "air-globe-fun-e-bike": (
        "electric mountain bike forest trail",
        "Vélo électrique tout-terrain",
    ),
}


# ── Accès API ──────────────────────────────────────────────────────────────


def cle_api() -> str:
    cle = os.environ.get("UNSPLASH_ACCESS_KEY", "").strip()
    if not cle:
        sys.exit(
            "Clé Unsplash absente.\n"
            "  export UNSPLASH_ACCESS_KEY=\"votre_access_key\"\n"
            "  (à créer sur https://unsplash.com/oauth/applications)"
        )
    return cle


def appel_api(chemin: str, params: dict | None = None) -> dict:
    """Appel GET authentifié, avec messages d'erreur explicites."""
    url = f"{API}{chemin}"
    if params:
        url += "?" + urllib.parse.urlencode(params)

    requete = urllib.request.Request(
        url,
        headers={
            "Authorization": f"Client-ID {cle_api()}",
            "Accept-Version": "v1",
            "User-Agent": "BellevueAveyron-PhotoTool/1.0",
        },
    )
    try:
        with urllib.request.urlopen(requete, timeout=30) as reponse:
            restant = reponse.headers.get("X-Ratelimit-Remaining")
            if restant is not None and restant.isdigit() and int(restant) < 5:
                print(f"    ⚠ quota Unsplash presque atteint ({restant} requêtes restantes)")
            return json.loads(reponse.read().decode("utf-8"))
    except urllib.error.HTTPError as err:
        if err.code == 401:
            sys.exit("Clé Unsplash refusée (401). Vérifiez UNSPLASH_ACCESS_KEY.")
        if err.code == 403:
            sys.exit(
                "Quota horaire Unsplash épuisé (403).\n"
                "Une application « Demo » est limitée à 50 requêtes par heure : "
                "relancez plus tard, ou utilisez --only pour traiter quelques cartes."
            )
        sys.exit(f"Erreur HTTP {err.code} sur {chemin} : {err.reason}")
    except urllib.error.URLError as err:
        sys.exit(f"Réseau indisponible : {err.reason}")


def url_image(photo: dict) -> str:
    """URL de rendu recadrée à la taille des cartes du site."""
    base = photo["urls"]["raw"]
    separateur = "&" if "?" in base else "?"
    return (
        f"{base}{separateur}"
        f"w={LARGEUR}&h={HAUTEUR}&fit=crop&crop=entropy&fm=jpg&q=78"
    )


def credit(photo: dict) -> dict:
    auteur = photo.get("user") or {}
    return {
        "photo_id": photo["id"],
        "auteur": auteur.get("name") or "Photographe Unsplash",
        "auteur_url": f"{auteur.get('links', {}).get('html', 'https://unsplash.com')}?{UTM}",
        "photo_url": f"{photo.get('links', {}).get('html', 'https://unsplash.com')}?{UTM}",
        "description": (photo.get("description") or photo.get("alt_description") or "").strip(),
    }


# ── Commande « search » ────────────────────────────────────────────────────


def rechercher(slugs: list[str], par_page: int) -> dict:
    resultats: dict[str, list[dict]] = {}

    for index, slug in enumerate(slugs, 1):
        requete, sujet = CARTES[slug]
        print(f"[{index}/{len(slugs)}] {slug}\n    « {requete} »")

        donnees = appel_api(
            "/search/photos",
            {
                "query": requete,
                "per_page": par_page,
                "orientation": "landscape",
                "content_filter": "high",
            },
        )
        photos = donnees.get("results", [])
        if not photos:
            print("    aucun résultat — reformulez la requête dans CARTES")

        resultats[slug] = [
            {
                "sujet": sujet,
                "requete": requete,
                "apercu": photo["urls"]["small"],
                "telechargement": url_image(photo),
                "download_location": photo["links"]["download_location"],
                **credit(photo),
            }
            for photo in photos
        ]
        for photo in resultats[slug]:
            print(f"      {photo['photo_id']}  {photo['auteur']:<22} {photo['description'][:52]}")

        time.sleep(PAUSE_ENTRE_APPELS)

    FICHIER_CANDIDATS.write_text(
        json.dumps(resultats, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    ecrire_planche_contact(resultats)
    print(f"\n→ {FICHIER_CANDIDATS.relative_to(RACINE)}")
    print(f"→ {PLANCHE_CONTACT.relative_to(RACINE)}  (à ouvrir dans un navigateur)")
    return resultats


def ecrire_planche_contact(resultats: dict) -> None:
    """Page HTML locale permettant de choisir visuellement chaque photo."""
    morceaux = [
        "<!doctype html><meta charset='utf-8'>",
        "<title>Candidats Unsplash — Bellevue d'Aveyron</title>",
        "<style>",
        "body{font:14px/1.6 system-ui,sans-serif;margin:0;padding:32px;background:#faf8f4;color:#26221c}",
        "h1{font-size:1.3rem;margin:0 0 6px}",
        "p.intro{color:#6a6258;max-width:70ch;margin:0 0 32px}",
        "section{margin-bottom:38px;border-top:1px solid #e2dccd;padding-top:18px}",
        "h2{font-size:.95rem;margin:0 0 2px}",
        "code{background:#efe9dc;padding:1px 6px;border-radius:3px;font-size:.85em}",
        ".sujet{color:#8a8172;margin:0 0 14px}",
        ".grille{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px}",
        "figure{margin:0;background:#fff;border:1px solid #e2dccd;border-radius:5px;overflow:hidden}",
        "img{width:100%;height:150px;object-fit:cover;display:block}",
        "figcaption{padding:9px 11px;font-size:.76rem;color:#6a6258}",
        "figcaption b{display:block;color:#26221c;font-size:.8rem}",
        "</style>",
        "<h1>Candidats Unsplash</h1>",
        "<p class='intro'>Repérez l'identifiant sous la photo retenue, puis lancez&nbsp;: "
        "<code>python3 tools/unsplash_photos.py download --pick slug=IDENTIFIANT</code>. "
        "Sans <code>--pick</code>, le premier résultat de chaque carte est téléchargé.</p>",
    ]

    for slug, photos in resultats.items():
        sujet = photos[0]["sujet"] if photos else CARTES[slug][1]
        morceaux.append(f"<section><h2><code>{html.escape(slug)}</code></h2>")
        morceaux.append(f"<p class='sujet'>{html.escape(sujet)}</p><div class='grille'>")
        for photo in photos:
            morceaux.append(
                "<figure>"
                f"<img src='{html.escape(photo['apercu'])}' alt='' loading='lazy'>"
                f"<figcaption><b>{html.escape(photo['photo_id'])}</b>"
                f"{html.escape(photo['auteur'])}<br>"
                f"{html.escape(photo['description'][:70])}</figcaption>"
                "</figure>"
            )
        morceaux.append("</div></section>")

    PLANCHE_CONTACT.write_text("\n".join(morceaux), encoding="utf-8")


# ── Commande « download » ──────────────────────────────────────────────────


def telecharger(slugs: list[str], choix: dict[str, str], forcer: bool) -> None:
    if not FICHIER_CANDIDATS.exists():
        sys.exit(
            "Aucun candidat en mémoire.\n"
            "Lancez d'abord : python3 tools/unsplash_photos.py search"
        )

    candidats = json.loads(FICHIER_CANDIDATS.read_text(encoding="utf-8"))
    DOSSIER_IMAGES.mkdir(parents=True, exist_ok=True)
    credits = charger_credits()
    telecharges = 0

    for slug in slugs:
        propositions = candidats.get(slug) or []
        if not propositions:
            print(f"— {slug} : aucun candidat, relancez « search »")
            continue

        if slug in choix:
            photo = next((p for p in propositions if p["photo_id"] == choix[slug]), None)
            if photo is None:
                print(f"— {slug} : identifiant {choix[slug]} absent des candidats")
                continue
        else:
            photo = propositions[0]

        destination = DOSSIER_IMAGES / f"{slug}.jpg"
        if destination.exists() and not forcer:
            print(f"— {slug} : déjà présent ({destination.name}), --force pour écraser")
            continue

        # Obligation Unsplash : signaler le téléchargement au photographe.
        chemin = photo["download_location"].replace(API, "")
        appel_api(chemin)

        try:
            requete = urllib.request.Request(
                photo["telechargement"],
                headers={"User-Agent": "BellevueAveyron-PhotoTool/1.0"},
            )
            with urllib.request.urlopen(requete, timeout=60) as reponse:
                contenu = reponse.read()
        except (urllib.error.URLError, urllib.error.HTTPError) as err:
            print(f"— {slug} : téléchargement impossible ({err})")
            continue

        destination.write_bytes(contenu)
        credits[slug] = {
            "fichier": f"images/decouvrir/{slug}.jpg",
            **{c: photo[c] for c in ("photo_id", "auteur", "auteur_url", "photo_url", "description")},
        }
        telecharges += 1
        print(f"✓ {slug}.jpg  {len(contenu) // 1024} Ko  © {photo['auteur']}")

        time.sleep(PAUSE_ENTRE_APPELS)

    if telecharges:
        enregistrer_credits(credits)
        print(f"\n{telecharges} photo(s) déposée(s) dans images/decouvrir/")
        print(f"Crédits mis à jour : {FICHIER_CREDITS.relative_to(RACINE)}")
        print(
            "\nÀ faire ensuite :\n"
            "  1. Vérifier le rendu de la page Découvrir.\n"
            "  2. Ajuster si besoin les textes alternatifs dans config/medias.php.\n"
            "  3. php tools/generer-inventaire-images.php"
        )


def charger_credits() -> dict:
    if FICHIER_CREDITS.exists():
        try:
            return json.loads(FICHIER_CREDITS.read_text(encoding="utf-8"))
        except json.JSONDecodeError:
            pass
    return {}


def enregistrer_credits(credits: dict) -> None:
    FICHIER_CREDITS.write_text(
        json.dumps(credits, ensure_ascii=False, indent=2, sort_keys=True), encoding="utf-8"
    )


# ── Interface en ligne de commande ─────────────────────────────────────────


def main() -> None:
    analyseur = argparse.ArgumentParser(
        description="Recherche et téléchargement des visuels Unsplash de la page Découvrir.",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__,
    )
    sous = analyseur.add_subparsers(dest="commande", required=True)

    p_search = sous.add_parser("search", help="chercher des candidats et bâtir la planche-contact")
    p_search.add_argument("--only", nargs="+", metavar="SLUG", help="limiter à ces cartes")
    p_search.add_argument("--per-page", type=int, default=6, help="candidats par carte (défaut : 6)")

    p_dl = sous.add_parser("download", help="télécharger les photos retenues")
    p_dl.add_argument("--only", nargs="+", metavar="SLUG", help="limiter à ces cartes")
    p_dl.add_argument(
        "--pick",
        nargs="+",
        default=[],
        metavar="SLUG=ID",
        help="choisir explicitement une photo (sinon le premier candidat)",
    )
    p_dl.add_argument("--force", action="store_true", help="écraser un fichier existant")

    sous.add_parser("list", help="lister les cartes gérées par ce script")

    args = analyseur.parse_args()

    if args.commande == "list":
        for slug, (requete, sujet) in CARTES.items():
            etat = "déposée" if (DOSSIER_IMAGES / f"{slug}.jpg").exists() else "à illustrer"
            print(f"{slug:<42} {etat:<12} {sujet}")
        return

    slugs = list(CARTES)
    if getattr(args, "only", None):
        inconnus = [s for s in args.only if s not in CARTES]
        if inconnus:
            sys.exit("Slug inconnu : " + ", ".join(inconnus))
        slugs = args.only

    if args.commande == "search":
        rechercher(slugs, args.per_page)
    elif args.commande == "download":
        choix = {}
        for paire in args.pick:
            if "=" not in paire:
                sys.exit(f"Format attendu SLUG=ID, reçu : {paire}")
            slug, photo_id = paire.split("=", 1)
            if slug not in CARTES:
                sys.exit(f"Slug inconnu : {slug}")
            choix[slug] = photo_id
        telecharger(slugs, choix, args.force)


if __name__ == "__main__":
    try:
        main()
    except BrokenPipeError:
        # Sortie tronquée par un « | head » : arrêt silencieux.
        sys.stdout = None
    except KeyboardInterrupt:
        sys.exit("\nInterrompu.")
