#!/bin/bash

# Configure local git for the first time
git remote add upstream git@github.com:PrestaShop/PrestaShop.git
git remote set-url --push upstream DISABLE

# Verify remote URLs
echo "Before proceeding, make sure the remotes are set correctly:"
git remote -v

# Fetch updates from upstream
git fetch upstream

echo "Configuration completed. You can now proceed with your tasks."
