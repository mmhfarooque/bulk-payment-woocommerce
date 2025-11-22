#!/bin/bash

# Version Bump Script for Bulk Payment for WooCommerce
# Usage: ./scripts/bump-version.sh [patch|minor|major]

set -e

PLUGIN_FILE="bulk-payment-wc.php"
CURRENT_VERSION=$(grep "Version:" "$PLUGIN_FILE" | awk '{print $3}')

if [ -z "$CURRENT_VERSION" ]; then
    echo "Error: Could not detect current version"
    exit 1
fi

echo "Current version: $CURRENT_VERSION"

# Parse version
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR="${VERSION_PARTS[0]}"
MINOR="${VERSION_PARTS[1]}"
PATCH="${VERSION_PARTS[2]}"

# Determine bump type
BUMP_TYPE="${1:-patch}"

case "$BUMP_TYPE" in
    patch)
        PATCH=$((PATCH + 1))
        ;;
    minor)
        MINOR=$((MINOR + 1))
        PATCH=0
        ;;
    major)
        MAJOR=$((MAJOR + 1))
        MINOR=0
        PATCH=0
        ;;
    *)
        echo "Usage: $0 [patch|minor|major]"
        exit 1
        ;;
esac

NEW_VERSION="$MAJOR.$MINOR.$PATCH"
echo "New version: $NEW_VERSION"

# Confirm with user
read -p "Bump version from $CURRENT_VERSION to $NEW_VERSION? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

# Update version in plugin file header
sed -i "s/Version: $CURRENT_VERSION/Version: $NEW_VERSION/" "$PLUGIN_FILE"

# Update version constant
sed -i "s/BULK_PAYMENT_WC_VERSION', '$CURRENT_VERSION/BULK_PAYMENT_WC_VERSION', '$NEW_VERSION/" "$PLUGIN_FILE"

echo "Updated version numbers in $PLUGIN_FILE"

# Get today's date
TODAY=$(date +%Y-%m-%d)

# Prepare changelog entry
echo ""
echo "Add this to CHANGELOG.md:"
echo ""
echo "## [$NEW_VERSION] - $TODAY"
echo ""
echo "### Added"
echo "- "
echo ""
echo "### Changed"
echo "- "
echo ""
echo "### Fixed"
echo "- "
echo ""

read -p "Open CHANGELOG.md for editing? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    ${EDITOR:-nano} CHANGELOG.md
fi

# Ask to commit
read -p "Commit version bump? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    git add "$PLUGIN_FILE" CHANGELOG.md
    git commit -m "Bump version to $NEW_VERSION"
    echo "Committed version bump"

    read -p "Create git tag v$NEW_VERSION? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git tag -a "v$NEW_VERSION" -m "Version $NEW_VERSION"
        echo "Created tag v$NEW_VERSION"
        echo ""
        echo "Next steps:"
        echo "1. Push changes: git push origin main --tags"
        echo "2. Create release on GitHub"
        echo "3. Create and upload plugin ZIP"
    fi
fi

echo ""
echo "Version bump complete!"
