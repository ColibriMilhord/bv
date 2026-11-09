# Régénère, dans .htaccess, les 301 de l'ancien site bellevuedaveyron.com depuis le CSV.
import csv, re, sys, urllib.parse
# Usage : python3 tools/generer-redirections-com.py (depuis la racine du dépôt).
src = sys.argv[1] if len(sys.argv) > 1 else "docs/wix/redirections-bellevuedaveyron-com.csv"
ht = sys.argv[2] if len(sys.argv) > 2 else ".htaccess"
rows = list(csv.DictReader(open(src, encoding='utf-8')))
COND = "    RewriteCond %{HTTP_HOST} ^((www|en|es)\\.)?bellevuedaveyron\\.com$ [NC]\n"
lines = []
for r in rows:
    old, new = r['ancienne_adresse'], r['nouvelle_adresse']
    if old == '/':
        continue
    chemin = urllib.parse.unquote(old).lstrip('/')
    motif = '^' + re.escape(chemin).replace('\\-', '-').replace('\\/', '/') + '/?$'
    flags = '[R=301,NE,L]' if '#' in new else '[R=301,L]'
    lines.append(COND + f"    RewriteRule {motif} {new} {flags}\n")
lines.append("    # Toute autre adresse de l'ancien site (accueil compris) : accueil du .fr.\n" + COND + "    RewriteRule ^ https://bellevuedaveyron.fr/ [R=301,L]\n")
debut = "    # ── Migration de l'ancien site Wix bellevuedaveyron.com"
fin = "    # ── Fichiers et dossiers cachés"
s = open(ht, encoding='utf-8').read()
i, j = s.index(debut), s.index(fin)
entete = '''    # ── Migration de l'ancien site Wix bellevuedaveyron.com (2026-11-09) ─────
    # INERTES tant que le domaine .com pointe chez Wix : elles ne jouent que
    # pour une requête arrivée ICI avec un nom d'hôte en .com (www., en., es.).
    # Aujourd'hui ce sont les 301 posées chez Wix qui travaillent. Ces règles
    # prennent le relais le jour où le .com est rattaché à cet hébergement
    # (étape 2 de docs/migration-wix-com-vers-fr.md) : sans elles, couper Wix
    # ferait perdre d'un coup tout le référencement de l'ancien site.
    # GÉNÉRÉES depuis docs/wix/redirections-bellevuedaveyron-com.csv : ne pas
    # les retoucher à la main, modifier le CSV puis relancer
    # `python3 tools/generer-redirections-com.py`.
'''
s = s[:i] + entete + ''.join(lines) + '\n' + s[j:]
open(ht, 'w', encoding='utf-8').write(s)
print(len(lines), 'règles')
