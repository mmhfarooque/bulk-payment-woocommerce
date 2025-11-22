# GitHub Repository Setup Guide

## Step 1: Create GitHub Repository

1. Go to https://github.com/new
2. Fill in the repository details:
   - **Repository name**: `bulk-payment-for-woocommerce`
   - **Description**: "A WooCommerce plugin that allows customers to pay any amount for a symbolic product without shipping information. Perfect for donations, custom payments, and flexible pricing."
   - **Visibility**: Public
   - **DO NOT** initialize with README, .gitignore, or license (we already have these)
3. Click "Create repository"

## Step 2: Push Your Local Repository

After creating the repository on GitHub, run these commands from your plugin directory:

```bash
# Navigate to your plugin directory (if not already there)
cd "/home/mmhf/Jezweb/Plugin-DEV/Bulk Payment WC"

# Add the remote repository (replace YOUR_USERNAME with your GitHub username)
git remote add origin https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce.git

# Push your code and tags
git push -u origin main
git push origin --tags
```

## Step 3: Create First Release on GitHub

1. Go to your repository: `https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce`
2. Click on "Releases" (right sidebar)
3. Click "Create a new release"
4. Configure the release:
   - **Choose a tag**: Select `v1.0.4` from the dropdown
   - **Release title**: `Version 1.0.4 - Initial Release`
   - **Description**: Copy the content from CHANGELOG.md for v1.0.4
   - **Attach ZIP**: Create and upload the plugin ZIP (see instructions below)
5. Click "Publish release"

## Step 4: Create Plugin ZIP for Distribution

Run this command to create a distributable ZIP file:

```bash
cd "/home/mmhf/Jezweb/Plugin-DEV"
zip -r "bulk-payment-wc-1.0.4.zip" "Bulk Payment WC" \
  -x "*/.git/*" \
  -x "*/node_modules/*" \
  -x "*/tests/*" \
  -x "*/.DS_Store" \
  -x "*/.gitignore" \
  -x "*/CONTRIBUTING.md" \
  -x "*/RELEASE.md" \
  -x "*/SETUP-GITHUB.md"
```

The ZIP file will be created at: `/home/mmhf/Jezweb/Plugin-DEV/bulk-payment-wc-1.0.4.zip`

Upload this ZIP file to your GitHub release.

## Repository Settings (Optional but Recommended)

### Add Repository Topics
Go to your repository → Click the gear icon next to "About" → Add topics:
- `wordpress`
- `woocommerce`
- `wordpress-plugin`
- `woocommerce-plugin`
- `payment`
- `donations`
- `flexible-pricing`

### Set Repository Description
Add the same description as mentioned in Step 1.

### Add Repository Website
If you have a demo or documentation site, add it here.

## Updating Repository URL in Code

After creating the repository, update these files with your actual GitHub URL:

1. **bulk-payment-wc.php** (Line 4): Update Plugin URI
2. **CHANGELOG.md**: Update the Releases link
3. **README.md**: Update any GitHub links

## Users Can Now Install Via:

### Method 1: Download from GitHub Releases
1. Go to `https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce/releases`
2. Download the latest ZIP file
3. In WordPress admin → Plugins → Add New → Upload Plugin
4. Choose the ZIP file and install

### Method 2: Git Clone (For Developers)
```bash
cd wp-content/plugins
git clone https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce.git bulk-payment-wc
```

### Method 3: Direct Download
Share this URL for direct download of latest version:
`https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce/releases/latest/download/bulk-payment-wc-1.0.4.zip`

## Next Steps

1. Create the GitHub repository
2. Push your code using the commands above
3. Create the first release with the ZIP file
4. Update CHANGELOG.md, bulk-payment-wc.php with your actual GitHub URL
5. Share the repository URL with users

## Future Version Updates

When you release a new version, follow the process in RELEASE.md:
1. Update version numbers in code
2. Update CHANGELOG.md
3. Commit and tag
4. Push to GitHub
5. Create new release with updated ZIP

For questions, see CONTRIBUTING.md or RELEASE.md.
