#!/usr/bin/env bash
# Rename the _s boilerplate to a project theme (README "Quick Start": Docker DEV + Theme).
#
# Usage:
#   bin/rename-theme.sh             interactive: asks for every value
#   bin/rename-theme.sh --project project-name --theme "Theme Name" [--slug theme-name] [--abbr abc]
#                       [--dev-url dev.example.de] [--stage-url https://stage.example.de]
#                       [--live-url https://www.example.de] [--root /path/to/project] [--dry-run]
#
#   --project  Docker project (container + DB name), required
#   --theme    theme name, required
#   --slug     text domain, default: derived from --theme
#   --abbr     DB table prefix, default: wp
#   --dev-url  dev host without http(s):// (stripped if given), default: <project>.localhost
#              (no /etc/hosts entry needed); stage/live URL: https:// added if missing;
#              trailing slashes are stripped
#   --root     project root to move THEME/docker/ to (only if no docker/ above yet),
#              default: parent of WordPress if that is public/, htdocs/, web/, …, else WordPress root
#
# Handles (`theme-style`, …) and languages/theme(+).pot are neutral – utilities/ relies on them.

set -euo pipefail

usage() { sed -n '2,20p' "$0" | sed 's/^# \{0,1\}//'; exit "${1:-1}"; }
die() { echo "Error: $*" >&2; exit 1; }

RE_PROJECT='^[a-z0-9][a-z0-9_-]*$'
RE_SLUG='^[a-z][a-z0-9-]*$'
RE_ABBR='^[a-z][a-z0-9_]*$'

# ASCII transliteration: German umlauts, then strip accents
translit() {
	printf '%s' "$1" | perl -CS -Mutf8 -MUnicode::Normalize -pe \
		's/Ä/Ae/g; s/Ö/Oe/g; s/Ü/Ue/g; s/ä/ae/g; s/ö/oe/g; s/ü/ue/g; s/ß/ss/g; $_ = NFD($_); s/\p{Mn}//g; s/[^\x00-\x7F]//g'
}
slugify() { translit "$1" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+|-+$//g'; }

# ask VAR "Label" "default" "regex" "hint" – repeats until valid; empty regex = optional free text
ask() {
	local var="$1" label="$2" default="$3" re="$4" hint="$5" val
	while :; do
		if [ -n "$default" ]; then read -r -p "$label [$default]: " val || die "aborted"; else read -r -p "$label: " val || die "aborted"; fi
		val="${val:-$default}"
		if [ -z "$re" ] || [[ "$val" =~ $re ]]; then printf -v "$var" '%s' "$val"; return; fi
		echo "  invalid – $hint" >&2
	done
}

PROJECT="" NAME="" SLUG="" ABBR="" DEV_URL="" STAGE_URL="" LIVE_URL="" ROOT="" DRY_RUN=0 INTERACTIVE=1

while [ $# -gt 0 ]; do
	case "$1" in
		--project|--theme|--slug|--abbr|--dev-url|--stage-url|--live-url|--root)
			[ $# -ge 2 ] || die "$1 needs a value"
			case "$1" in
				--project) PROJECT="$2" ;;
				--theme) NAME="$2" ;;
				--slug) SLUG="$2" ;;
				--abbr) ABBR="$2" ;;
				--dev-url) DEV_URL="$2" ;;
				--stage-url) STAGE_URL="$2" ;;
				--live-url) LIVE_URL="$2" ;;
				--root) ROOT="$2" ;;
			esac
			INTERACTIVE=0
			shift 2 ;;
		--dry-run) DRY_RUN=1; shift ;;
		-h|--help) usage 0 ;;
		*) die "unknown argument $1" ;;
	esac
done

THEME_DIR="$(cd "$(dirname "$0")/.." && pwd -P)"
cd "$THEME_DIR"

[ -f css/style.scss ] || die "css/style.scss not found – not an _s theme?"
grep -q '^Text Domain: _s$' css/style.scss || die "theme already renamed (no 'Text Domain: _s' in css/style.scss)"

# Docker: existing docker/ above WordPress, otherwise move THEME/docker/ to the project root
WP_ROOT="$(cd ../../.. && pwd -P)"
DOCKER_DIR=""
for d in "$WP_ROOT" "$WP_ROOT/.." "$WP_ROOT/../.."; do
	if [ -f "$d/docker/.env" ] && [ -f "$d/docker/docker-compose.yaml" ]; then
		DOCKER_DIR="$(cd "$d/docker" && pwd -P)"
		break
	fi
done
MOVE_DOCKER=0
if [ -z "$DOCKER_DIR" ] && [ -f "$THEME_DIR/docker/.env" ]; then
	MOVE_DOCKER=1
	case "$(basename "$WP_ROOT")" in
		public|htdocs|httpdocs|web|www|html) ROOT_DEFAULT="$(dirname "$WP_ROOT")" ;;
		*) ROOT_DEFAULT="$WP_ROOT" ;;
	esac
fi

# root_error PATH – prints why PATH can't take docker/ (empty = ok)
root_error() {
	local r="${1/#\~/$HOME}"
	[ -d "$r" ] || { echo "not a directory"; return; }
	r="$(cd "$r" && pwd -P)"
	case "$WP_ROOT" in "$r"|"$r"/*) ;; *) echo "WordPress ($WP_ROOT) is not inside it"; return ;; esac
	[ ! -e "$r/docker" ] || echo "$r/docker already exists"
}

if [ "$INTERACTIVE" = 1 ]; then
	[ -t 0 ] || die "no TTY – pass the values as options (see --help)"
	ask PROJECT "Project name (Docker, a-z 0-9 - _)" "" "$RE_PROJECT" "a-z, 0-9, '-', '_'"
	ask NAME "Theme name" "" '[^[:space:]]' "required"
	ask SLUG "Slug / text domain" "$(slugify "$NAME")" "$RE_SLUG" "a-z, 0-9, '-', must start with a letter"
	ask ABBR "DB table prefix (abbr)" "wp" "$RE_ABBR" "a-z, 0-9, '_', must start with a letter"
	ask DEV_URL "Dev host (e.g. dev.example.de – http(s):// is stripped)" "${PROJECT//_/-}.localhost" "" ""
	ask STAGE_URL "Stage URL (optional)" "" "" ""
	ask LIVE_URL "Live URL (optional)" "" "" ""
	if [ "$MOVE_DOCKER" = 1 ]; then
		while :; do
			ask ROOT "Project root for docker/" "$ROOT_DEFAULT" "" ""
			err="$(root_error "$ROOT")"
			[ -n "$err" ] || break
			echo "  invalid – $err" >&2
		done
	fi
	echo
else
	[ -n "$PROJECT" ] || { echo "Error: --project missing" >&2; usage; }
	[ -n "$NAME" ] || { echo "Error: --theme missing" >&2; usage; }
fi

if [ "$MOVE_DOCKER" = 1 ]; then
	ROOT="${ROOT:-$ROOT_DEFAULT}"
	err="$(root_error "$ROOT")"
	[ -z "$err" ] || die "--root '$ROOT': $err"
	ROOT="$(cd "${ROOT/#\~/$HOME}" && pwd -P)"
elif [ -n "$ROOT" ]; then
	echo "Note: --root ignored (${DOCKER_DIR:-no THEME/docker/})" >&2
fi

[[ "$PROJECT" =~ $RE_PROJECT ]] || die "invalid project '$PROJECT' (a-z, 0-9, '-', '_')"
DEV_URL="${DEV_URL:-${PROJECT//_/-}.localhost}"

# Derive slug/prefix/package from name.
[ -n "$SLUG" ] || SLUG="$(slugify "$NAME")"
[[ "$SLUG" =~ $RE_SLUG ]] || die "invalid slug '$SLUG' (a-z, 0-9, '-', must start with a letter)"
PREFIX="${SLUG//-/_}"
PACKAGE="$(translit "$NAME" | sed -E 's/[^A-Za-z0-9]+/_/g; s/^_+|_+$//g')"
[ -n "$PACKAGE" ] || PACKAGE="$PREFIX"

ABBR="${ABBR:-wp}"
[[ "$ABBR" =~ $RE_ABBR ]] || die "invalid abbr '$ABBR' (a-z, 0-9, '_', must start with a letter)"

export NAME SLUG PREFIX PACKAGE

# Theme folder → slug
OLD_DIR="$(basename "$THEME_DIR")"
NEW_DIR="$(dirname "$THEME_DIR")/$SLUG"
[ "$OLD_DIR" = "$SLUG" ] || [ ! -e "$NEW_DIR" ] || die "folder '$NEW_DIR' already exists"

echo "Project: $PROJECT   (Docker)"
if [ "$OLD_DIR" = "$SLUG" ]; then echo "Folder:  $OLD_DIR   (unchanged)"; else echo "Folder:  $OLD_DIR → $SLUG"; fi
echo "Name:    $NAME"
echo "Slug:    $SLUG   (text domain)"
echo "Prefix:  ${PREFIX}_   (functions)"
echo "Package: $PACKAGE   (@package)"
echo "Abbr:    $ABBR   (DB table prefix)"
[ -z "$DEV_URL$STAGE_URL$LIVE_URL" ] || echo "URLs:    ${DEV_URL:--} | ${STAGE_URL:--} | ${LIVE_URL:--}"
if [ "$MOVE_DOCKER" = 1 ]; then echo "Docker:  move docker/ → $ROOT/docker"
elif [ -n "$DOCKER_DIR" ]; then echo "Docker:  $DOCKER_DIR"
else echo "Docker:  none found – skipped"; fi
[ "$DRY_RUN" = 1 ] && echo "(dry run – nothing is written)"
echo

if [ "$INTERACTIVE" = 1 ] && [ "$DRY_RUN" = 0 ]; then
	read -r -p "Apply? [y/N] " ok || ok=""
	[[ "$ok" =~ ^[yYjJ]$ ]] || { echo "Aborted."; exit 0; }
	echo
fi

# --- Theme ---------------------------------------------------------------

# Ordered like the README steps.
PERL_RENAME='
	s/^(\s*)Theme Name: _s(?![\w-])/$1Theme Name: $ENV{NAME}/;
	s/Text Domain: _s(?![\w-])/Text Domain: $ENV{SLUG}/;
	s/X-Domain: _s(?![\w-])/X-Domain: $ENV{SLUG}/;
	s/Project-Id-Version: _s(?![\w-])/Project-Id-Version: $ENV{NAME}/;
	s{(support/theme/)_s(?![\w-])}{$1$ENV{SLUG}};
	s/^msgid "_s"$/msgid "$ENV{NAME}"/;
	s/(["\x27])_s\1/$1$ENV{SLUG}$1/g;
	s/(?<![\w-])_s_(?=\w)/$ENV{PREFIX}_/g;
	s/(?<![\w-])_s-(?=\w)/theme-/g;
	s/\@package _s(?![\w-])/\@package $ENV{PACKAGE}/g;
	s/^(\s*\*\s*)_s (functions|theme)/$1$ENV{NAME} $2/;
'

# Project files only: docs (*.md), utilities/ and vendor stay untouched.
FILES=()
while IFS= read -r -d '' f; do
	grep -Iq '_s' "$f" 2>/dev/null && FILES+=("$f")
done < <(find . \
	\( -path ./utilities -o -path ./node_modules -o -path ./.git -o -path ./bin -o -path ./docker \) -prune -o \
	-type f \
	! -name 'package-lock.json' ! -name '*.md' ! -name 'LICENSE' ! -name '*.min.js' ! -name '*.map' ! -name '*.mo' \
	-print0)

changed=0
for f in "${FILES[@]}"; do
	new="$(perl -pe "$PERL_RENAME" "$f")"
	if [ "$new" != "$(cat "$f")" ]; then
		n="$(diff <(cat "$f") <(printf '%s\n' "$new") | grep -c '^>' || true)"
		echo "  ${f#./}  ($n lines)"
		[ "$DRY_RUN" = 1 ] || perl -pi -e "$PERL_RENAME" "$f"
		changed=$((changed + 1))
	fi
done
echo "Theme: $changed files changed."

# --- Docker .env -----------------------------------------------------------

echo
PROJECT_ROOT=""
if [ "$MOVE_DOCKER" = 1 ]; then
	PROJECT_ROOT="$ROOT"
	if [ "$DRY_RUN" = 1 ]; then
		echo "Docker: would move docker/ → $ROOT/docker"
		DOCKER_DIR="$THEME_DIR/docker"
	else
		mv "$THEME_DIR/docker" "$ROOT/docker"
		echo "Docker: moved docker/ → $ROOT/docker"
		DOCKER_DIR="$ROOT/docker"
	fi
fi

if [ -z "$DOCKER_DIR" ]; then
	echo "Docker: no docker/.env found above $WP_ROOT – skipped."
else
	PROJECT_ROOT="${PROJECT_ROOT:-$(dirname "$DOCKER_DIR")}"
	case "$WP_ROOT" in
		"$PROJECT_ROOT") WP_REL="../" ;;
		"$PROJECT_ROOT"/*) WP_REL="../${WP_ROOT#"$PROJECT_ROOT"/}/" ;;
		*) WP_REL="" ;;
	esac

	strip_schema() { printf '%s' "$1" | sed -E 's#^https?://##; s#/+$##'; }
	with_schema() { local u; u="$(printf '%s' "$1" | sed -E 's#/+$##')"; case "$u" in http://*|https://*) printf '%s' "$u" ;; *) printf 'https://%s' "$u" ;; esac; }

	export E_PROJECT="$PROJECT" E_ABBR="$ABBR" E_WP_ROOT="$WP_REL"
	export E_HOST="" E_STAGE="" E_LIVE=""
	[ -z "$DEV_URL" ] || E_HOST="$(strip_schema "$DEV_URL")"
	[ -z "$STAGE_URL" ] || E_STAGE="$(with_schema "$STAGE_URL")"
	[ -z "$LIVE_URL" ] || E_LIVE="$(with_schema "$LIVE_URL")"

	# Replace placeholders only (keeps manual values).
	PERL_ENV='
		s/^PROJECT=PROJECT_NAME$/PROJECT=$ENV{E_PROJECT}/;
		s/^ABBR=PROJECT_ABBR$/ABBR=$ENV{E_ABBR}/;
		$ENV{E_HOST}  ne "" and s/^HOST=DEV_URL_WITHOUT_SCHEMA$/HOST=$ENV{E_HOST}/;
		$ENV{E_STAGE} ne "" and s/^STAGE_URL=STAGE_URL_WITH_SCHEMA$/STAGE_URL=$ENV{E_STAGE}/;
		$ENV{E_LIVE}  ne "" and s/^LIVE_URL=LIVE_URL_WITH_SCHEMA$/LIVE_URL=$ENV{E_LIVE}/;
		$ENV{E_WP_ROOT} ne "" and s/^WORDPRESS_ROOT=.*$/WORDPRESS_ROOT=$ENV{E_WP_ROOT}/;
	'
	ENV_FILE="$DOCKER_DIR/.env"
	echo "Docker: ${ENV_FILE}"
	env_diff="$(diff "$ENV_FILE" <(perl -pe "$PERL_ENV" "$ENV_FILE") | grep '^[<>]' || true)"
	if [ -n "$env_diff" ]; then printf '%s\n' "$env_diff" | sed 's/^/  /'; else echo "  nothing to change"; fi
	[ "$DRY_RUN" = 1 ] || perl -pi -e "$PERL_ENV" "$ENV_FILE"
	[ -n "$WP_REL" ] || echo "  ! WORDPRESS_ROOT not set: WordPress root is outside $PROJECT_ROOT"
	[ "$DRY_RUN" = 1 ] || grep -E '=(PROJECT_NAME|PROJECT_ABBR|DEV_URL_WITHOUT_SCHEMA|STAGE_URL_WITH_SCHEMA|LIVE_URL_WITH_SCHEMA)$' "$ENV_FILE" \
		| sed 's/^/  ! still placeholder: /' || true
fi

# --- Leftovers / next steps --------------------------------------------------

echo
if [ "$DRY_RUN" = 0 ]; then
	left="$(grep -rnIE --exclude-dir=utilities --exclude-dir=node_modules --exclude-dir=bin --exclude-dir=docker \
		--exclude=package-lock.json --exclude='*.md' --exclude='*.min.js' --exclude='*.map' \
		'(^|[^A-Za-z0-9_$-])_s([^A-Za-z0-9_]|$)|_s_|_s-' . || true)"
	if [ -n "$left" ]; then
		echo "Remaining '_s' – check manually (README: 'replace selectively'):"
		printf '%s\n' "$left" | sed 's/^/  /'
		echo
	fi
fi

HINTS=()

# Theme folder
if [ "$OLD_DIR" != "$SLUG" ]; then
	if [ "$DRY_RUN" = 1 ]; then
		echo "Folder: would rename '$OLD_DIR' → '$SLUG'"
	else
		mv "$THEME_DIR" "$NEW_DIR"
		echo "Folder: renamed '$OLD_DIR' → '$SLUG'"
		HINTS+=("cd ../$SLUG   (your shell is still in the old path)")
	fi
fi

if [ -n "$DOCKER_DIR" ]; then
	# /etc/hosts stays manual – hint only if the entry is missing (*.localhost resolves by itself)
	HOST_NAME="$(perl -pe "$PERL_ENV" "$DOCKER_DIR/.env" | sed -n 's/^HOST=//p' | head -1)"
	case "$HOST_NAME" in
		""|DEV_URL_WITHOUT_SCHEMA) HINTS+=("set HOST in docker/.env") ;;
		localhost|*.localhost) ;;
		*) awk -v h="$HOST_NAME" '!/^[[:space:]]*#/ { for ( i = 2; i <= NF; i++ ) if ( $i == h ) f = 1 } END { exit !f }' /etc/hosts \
			|| HINTS+=("add '127.0.0.1 $HOST_NAME' to /etc/hosts") ;;
	esac
fi

if [ ${#HINTS[@]} -gt 0 ]; then
	echo
	echo "Next steps:"
	printf '  - %s\n' "${HINTS[@]}"
fi
