#!/bin/bash

if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <new_version_tag>"
    exit 1
fi

# Fetch updates from upstream
git fetch --all --prune --tags

# Specify the new version tag from the command-line argument
new_version_tag="$1"

# Checkout the tag
git checkout $new_version_tag

# Create a new branch based on the tag
new_branch_name="edition/$new_version_tag"
git switch -c $new_branch_name

# Find the highest edition branch
highest_edition_branch=$(git branch -a | grep -E '^  remotes/origin/edition/[0-9]+\.[0-9]+\.[0-9]+$' | sed 's/.*edition\///' | sort -V | tail -n 1)

if [ -n "$highest_edition_branch" ]; then
    echo "Found highest edition branch: $highest_edition_branch"

    edition_commit_list=$(git rev-list $(git merge-base $highest_edition_branch origin/edition/$highest_edition_branch)..origin/edition/$highest_edition_branch --reverse)

    # Apply all commits from the last edition branch
    for commit in $edition_commit_list
    do
        git cherry-pick $commit
    done

    # Check for conflicts
    conflict_check=$(git status | grep "Unmerged paths")

    if [ -n "$conflict_check" ]; then
        echo "Conflicts detected. Please resolve conflicts and commit the changes."
        exit 1
    else
        echo "Commits applied successfully. You can now continue with your tasks."
    fi
else
    echo "Error: No existing edition branches found."
    exit 1
fi
