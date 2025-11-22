#!/bin/bash

# GitHub Release Creation Script for Bulk Payment WooCommerce
# Usage: ./scripts/create-release.sh [version] [release_notes]
# Example: ./scripts/create-release.sh 1.0.5 "Bug fixes and improvements"

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if version is provided
if [ -z "$1" ]; then
    echo -e "${RED}Error: Version number required${NC}"
    echo "Usage: ./scripts/create-release.sh [version] [release_notes]"
    echo "Example: ./scripts/create-release.sh 1.0.5 'Bug fixes and improvements'"
    exit 1
fi

VERSION=$1
RELEASE_NOTES=${2:-"Release version $VERSION"}

# Validate version format (should be like 1.0.4)
if ! [[ $VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo -e "${RED}Error: Invalid version format${NC}"
    echo "Version should be in format X.Y.Z (e.g., 1.0.5)"
    exit 1
fi

echo -e "${GREEN}Creating release v$VERSION${NC}"

# Check if we're on main branch
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo -e "${YELLOW}Warning: Not on main branch (currently on $CURRENT_BRANCH)${NC}"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo -e "${RED}Error: You have uncommitted changes${NC}"
    echo "Please commit or stash your changes first"
    exit 1
fi

# Pull latest changes
echo -e "${YELLOW}Pulling latest changes...${NC}"
git pull origin main

# Create temporary directory for release
RELEASE_DIR="bulk-payment-wc"
TMP_DIR=$(mktemp -d)
RELEASE_PATH="$TMP_DIR/$RELEASE_DIR"

echo -e "${YELLOW}Preparing release files...${NC}"

# Create release directory
mkdir -p "$RELEASE_PATH"

# Copy plugin files (exclude development files)
rsync -av --exclude='.git' \
          --exclude='.gitignore' \
          --exclude='.claude' \
          --exclude='node_modules' \
          --exclude='scripts' \
          --exclude='SETUP-GITHUB.md' \
          --exclude='UPDATE-WORKFLOW.md' \
          --exclude='CONTRIBUTING.md' \
          --exclude='RELEASE.md' \
          --exclude='.DS_Store' \
          --exclude='*.zip' \
          . "$RELEASE_PATH/"

# Create ZIP file
ZIP_NAME="bulk-payment-wc-$VERSION.zip"
echo -e "${YELLOW}Creating ZIP file: $ZIP_NAME${NC}"

cd "$TMP_DIR"
zip -r "$ZIP_NAME" "$RELEASE_DIR" -q

# Move ZIP to releases directory
RELEASES_DIR="./releases"
mkdir -p "$RELEASES_DIR"
mv "$ZIP_NAME" "$RELEASES_DIR/"

# Cleanup
cd - > /dev/null
rm -rf "$TMP_DIR"

echo -e "${GREEN}ZIP file created: $RELEASES_DIR/$ZIP_NAME${NC}"

# Create git tag
TAG_NAME="v$VERSION"
echo -e "${YELLOW}Creating git tag: $TAG_NAME${NC}"

if git rev-parse "$TAG_NAME" >/dev/null 2>&1; then
    echo -e "${RED}Error: Tag $TAG_NAME already exists${NC}"
    exit 1
fi

git tag -a "$TAG_NAME" -m "Version $VERSION"

# Push tag to GitHub
echo -e "${YELLOW}Pushing tag to GitHub...${NC}"
git push origin "$TAG_NAME"

# Create GitHub release
echo -e "${YELLOW}Creating GitHub release...${NC}"

# Get changelog for this version from CHANGELOG.md
CHANGELOG_SECTION=$(awk "/### $VERSION/,/^### [0-9]/" CHANGELOG.md | sed '1d;$d')

# If changelog section is empty, use provided release notes
if [ -z "$CHANGELOG_SECTION" ]; then
    RELEASE_BODY="$RELEASE_NOTES"
else
    RELEASE_BODY="$CHANGELOG_SECTION

---

## Installation

1. Download \`$ZIP_NAME\`
2. Go to WordPress Admin > Plugins > Add New > Upload Plugin
3. Choose the downloaded file and click Install Now
4. Activate the plugin

## Requirements

- WordPress 5.8 or higher
- WooCommerce 6.0 or higher
- PHP 7.4 or higher"
fi

# Create release using GitHub CLI
gh release create "$TAG_NAME" \
    "$RELEASES_DIR/$ZIP_NAME" \
    --title "Version $VERSION" \
    --notes "$RELEASE_BODY"

echo -e "${GREEN}Release created successfully!${NC}"
echo -e "${GREEN}Download URL: https://github.com/mmhfarooque/bulk-payment-woocommerce/releases/tag/$TAG_NAME${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Update Packagist (if configured): https://packagist.org/packages/jezweb/bulk-payment-wc"
echo "2. Announce the release to users"
echo "3. Update any documentation with new features"
