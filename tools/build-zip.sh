#!/usr/bin/env bash
# Builds dist/open-newsletter-<version>.zip with the plugin inside a "newsletter/" folder
# (the slug used by the code, kept so data and assets keep working).
set -euo pipefail
cd "$(dirname "$0")/.."
version=$(sed -n "s/^define('NEWSLETTER_VERSION', '\(.*\)');/\1/p" plugin.php)
out="dist/open-newsletter-${version}.zip"
stage=$(mktemp -d)
trap 'rm -rf "$stage"' EXIT
mkdir -p "$stage/newsletter" dist
git ls-files -z | grep -zv -e '^tools/' -e '^\.git' -e '^dist/' | xargs -0 -I{} cp --parents {} "$stage/newsletter/"
rm -f "$out"
(cd "$stage" && zip -qr "$OLDPWD/$out" newsletter)
echo "Built $out"
