#!/usr/bin/env bash
set -e

PLUGIN_SLUG="infomail-block"
VERSION=$(node -p "require('./package.json').version")
DEST=$(realpath "../")/${PLUGIN_SLUG}-${VERSION}.zip

echo "🔨 Building ${PLUGIN_SLUG} v${VERSION}..."

# Clean & build
npm run build

# Create zip respecting .distignore
rsync -rc \
  --exclude-from=".distignore" \
  --exclude=".git" \
  . /tmp/${PLUGIN_SLUG}/

cd /tmp
zip -r ${PLUGIN_SLUG}-${VERSION}.zip ${PLUGIN_SLUG}/
mv ${PLUGIN_SLUG}-${VERSION}.zip "${DEST}"

# Cleanup
#rm -rf /tmp/${PLUGIN_SLUG}

echo "✅ Package ready: ${DEST}"